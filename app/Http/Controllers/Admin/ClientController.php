<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:admin.clientes.index')->only('index', 'list');
        $this->middleware('can:admin.clientes.store')->only('store');
        $this->middleware('can:admin.clientes.update')->only('update');
        $this->middleware('can:admin.clientes.destroy')->only('destroy');
    }
    public function index()
    {
        return view('admin.clients.index');
    }

    public function list()
    {
        $branchId = session('branch_id'); // 👈 sucursal elegida

        $query = Client::with(['branch', 'user']) // 👈 importante
            ->where('status', '!=', -1);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $customers = $query->orderBy('id', 'desc')->get();

        return DataTables::of($customers)
            ->addIndexColumn()
            ->editColumn('status', function ($client) {
                return $client->status == 1
                    ? '<span class="badge bg-success text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> Activo
                   </span>'
                    : '<span class="badge bg-danger text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                    <i class="bi bi-x-circle-fill me-1"></i> Inactivo
                   </span>';
            })
            ->addColumn('acciones', function ($client) {
                $statusOriginal = $client->status;
                return view('admin.clients.partials.acciones', compact('client', 'statusOriginal'))->render();
            })
            ->rawColumns(['status', 'acciones'])
            ->make(true);
    }


    public function consultarDniRuc($dniruc)
    {
        $numero = (string) $dniruc;

        // Solo números
        if (!preg_match('/^\d+$/', $numero)) {
            return response()->json([
                'status'  => false,
                'message' => 'El número de documento debe contener solo dígitos.'
            ], 422);
        }

        // Solo aceptamos 8 (DNI) o 11 (RUC)
        if (strlen($numero) !== 8 && strlen($numero) !== 11) {
            return response()->json([
                'status'  => false,
                'message' => 'El número debe tener 8 dígitos (DNI) o 11 dígitos (RUC).'
            ], 422);
        }

        $token = 'apis-token-7645.70qIyk7rGHUBVYCLNlcITcM1fo-mBqvp';

        // ========= DNI (8 dígitos) =========
        if (strlen($numero) === 8) {

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $numero,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Referer: https://apis.net.pe/consulta-dni-api',
                    'Authorization: ' . 'Bearer ' . $token,
                ],
            ]);

            $response = curl_exec($curl);

            if ($response === false) {
                $error = curl_error($curl);
                curl_close($curl);
                return response()->json([
                    'status'  => false,
                    'message' => 'Error al conectar con el servicio de DNI.',
                    'error'   => $error,
                ], 500);
            }

            curl_close($curl);

            $persona = json_decode($response);

            if (!$persona || isset($persona->error)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'DNI no encontrado.',
                    'data'    => $persona
                ], 404);
            }

            return response()->json([
                'status' => true,
                'type'   => 'DNI',
                'data'   => $persona,
            ]);
        }

        // ========= RUC (11 dígitos) =========
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.apis.net.pe/v2/sunat/ruc?numero=' . $numero,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Referer: http://apis.net.pe/api-ruc',
                'Authorization: ' . 'Bearer ' . $token,
            ],
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json([
                'status'  => false,
                'message' => 'Error al conectar con el servicio de RUC.',
                'error'   => $error,
            ], 500);
        }

        curl_close($curl);

        $empresa = json_decode($response);

        if (!$empresa || isset($empresa->error)) {
            return response()->json([
                'status'  => false,
                'message' => 'RUC no encontrado.',
                'data'    => $empresa
            ], 404);
        }

        return response()->json([
            'status' => true,
            'type'   => 'RUC',
            'data'   => $empresa,
        ]);
    }



    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        // 👇 sucursal de la sesión (obligatoria)
        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay una sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            // ❌ ya no pedimos branch_id ni user_id del request

            'document_type'   => 'required|in:DNI,RUC,CE',

            'document_number' => [
                'required',
                'regex:/^[0-9]{8,11}$/',
                Rule::unique('clients', 'document_number')
            ],

            'first_name'      => 'nullable|string|min:2|max:50',
            'last_name'       => 'nullable|string|min:2|max:50',

            'birth_date'      => 'nullable|date|before:today',

            'gender'         => 'nullable|in:M,F,O',
            'marital_status' => 'nullable|in:soltero,casado,divorciado,viudo',
            'occupation'     => 'nullable|string|max:100',

            'email' => [
                'nullable',
                'email',
                Rule::unique('clients', 'email')
            ],

            'phone' => [
                'nullable',
                'regex:/^[0-9]{9,15}$/',
            ],

            'credit_score'    => 'nullable|numeric|between:0,100',
            'status'          => 'required|in:0,1',

            'image'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 🔹 Asignamos sucursal y usuario desde backend
        $data['branch_id'] = $branchId;
        $data['user_id']   = Auth::id(); // quien lo registró

        // Reglas dependientes
        if ($data['document_type'] === 'RUC') {
            $v = Validator::make($data, [
                'company_name' => 'required|string|max:150',
                'ruc'          => 'required|string|max:15',
            ]);

            if ($v->fails()) {
                return response()->json(['status' => 'error', 'errors' => $v->errors()], 422);
            }

            $data['full_name'] = trim($data['company_name']);
        } else {
            if (empty($data['last_name'])) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['last_name' => ['El apellido es obligatorio para persona natural.']]
                ], 422);
            }

            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . $data['last_name']);
        }

        $data['status'] = (int)($data['status'] ?? 1);

        if (Auth::check()) {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
        }

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $data['photo'] = $request->file('image')->store('clients');
            }

            $client = Client::create($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cliente creado correctamente.',
                'data'    => $client
            ], 201);
        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error("Error creando cliente: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al crear el cliente.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * EDIT
     */
    public function edit($id)
    {
        $client = Client::find($id);

        if (! $client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (! $client) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cliente no encontrado.'
            ], 404);
        }

        $branchId = session('branch_id');

        if (! $branchId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No hay una sucursal seleccionada en la sesión.',
            ], 422);
        }

        $data = $request->validate([
            // ❌ quitamos branch_id y user_id

            'document_type'   => 'required|in:DNI,RUC,CE',

            'document_number' => [
                'required',
                'regex:/^[0-9]{8,11}$/',
                \Illuminate\Validation\Rule::unique('clients', 'document_number')->ignore($client->id)
            ],

            'first_name'      => 'nullable|string|min:2|max:50',
            'last_name'       => 'nullable|string|min:2|max:50',
            'birth_date'      => 'nullable|date|before:today',

            'gender'         => 'nullable|in:M,F,O',
            'marital_status' => 'nullable|in:soltero,casado,divorciado,viudo',
            'occupation'     => 'nullable|string|max:100',

            'email' => [
                'nullable',
                'email',
                \Illuminate\Validation\Rule::unique('clients', 'email')->ignore($client->id)
            ],

            'phone' => [
                'nullable',
                'regex:/^[0-9]{9,15}$/',
            ],

            'credit_score'    => 'nullable|numeric|between:0,100',
            'status'          => 'required|in:0,1',

            'image'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.regex'    => 'El número de documento debe contener solo dígitos (8 a 11).',
            'document_number.unique'   => 'El número de documento ya está registrado en otro cliente.',

            'email.email'  => 'Ingresa un correo válido.',
            'email.unique' => 'El correo ya está en uso por otro cliente.',

            'phone.regex'  => 'El teléfono debe contener solo números (9 a 15 dígitos).',
        ]);

        // 🔹 forzamos a que siga en la sucursal actual de la sesión
        $data['branch_id'] = $branchId;

        // Reglas dependientes
        if (isset($data['document_type']) && $data['document_type'] === 'RUC') {
            $v = Validator::make($data, [
                'company_name' => 'required|string|max:150',
                'ruc'          => 'required|string|max:15',
            ]);
            if ($v->fails()) {
                return response()->json(['status' => 'error', 'errors' => $v->errors()], 422);
            }
            $data['full_name'] = trim($data['company_name']);
        } else {
            if (empty($data['last_name'])) {
                return response()->json([
                    'status' => 'error',
                    'errors' => ['last_name' => ['El apellido es obligatorio para persona natural.']]
                ], 422);
            }
            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        }

        if (Auth::check()) {
            $data['updated_by'] = Auth::id();
            $data['user_id']    = Auth::id(); // si quieres mantener user_id como "responsable actual"
        }

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                $newPath = $request->file('image')->store('clients');

                if ($client->photo) {
                    Storage::delete($client->photo);
                }

                $data['photo'] = $newPath;
            }

            $client->update($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cliente actualizado correctamente.',
                'data'    => $client->fresh()
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error actualizando cliente: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar el cliente.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /*  */

    //DELETE CLIENTE    

    public function destroy(Client  $client)
    {
        $client->update(['status' => -1]);
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}
