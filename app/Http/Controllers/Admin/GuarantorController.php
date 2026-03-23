<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guarantor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GuarantorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.guarantors.index');
    }



    public function list()
    {
        // Si luego quieres excluir "eliminados lógicamente", aquí puedes filtrar por status
        $guarantors = Guarantor::where('status', '!=', -1)->orderBy('id', 'desc')->get();

        return DataTables::of($guarantors)
            ->addIndexColumn()
            ->editColumn('status', function ($guarantor) {
                return $guarantor->status == 1
                    ? '<span class="badge bg-success text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Activo
                   </span>'
                    : '<span class="badge bg-danger text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                        <i class="bi bi-x-circle-fill me-1"></i> Inactivo
                   </span>';
            })
            ->addColumn('acciones', function ($guarantor) {
                $statusOriginal = $guarantor->status;
                return view('admin.guarantors.partials.acciones', compact('guarantor', 'statusOriginal'))->render();
            })
            ->rawColumns(['status', 'acciones'])
            ->make(true);
    }

    public function consultarDniRuc($dniruc)
    {
        // 👉 reutilizamos EXACTAMENTE el método del ClientController
        return app(ClientController::class)->consultarDniRuc($dniruc);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    private function normalizeUserData(array $data): array
    {
        // Nombre y Apellidos: Primera letra mayúscula
        if (isset($data['first_name'])) {
            $data['first_name'] = ucwords(strtolower(trim($data['first_name'])));
        }

        if (isset($data['last_name'])) {
            $data['last_name'] = ucwords(strtolower(trim($data['last_name'])));
        }

        if (isset($data['company_name'])) {
            $data['company_name'] = ucwords(strtolower(trim($data['company_name'])));
        }

        // Email: todo minúscula
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Dirección: primera letra mayúscula
        if (isset($data['relationship'])) {
            $data['relationship'] = ucfirst(strtolower(trim($data['relationship'])));
        }
        if (isset($data['occupation'])) {
            $data['occupation'] = ucfirst(strtolower(trim($data['occupation'])));
        }

        if (isset($data['address'])) {
            $data['address'] = ucfirst(strtolower(trim($data['address'])));
        }

        // DNI solo números
        if (isset($data['document_number'])) {
            $data['document_number'] = preg_replace('/\D/', '', $data['document_number']);
        }

        if (isset($data['ruc'])) {
            $data['ruc'] = preg_replace('/\D/', '', $data['ruc']);
        }

        // Teléfono solo números
        if (isset($data['phone'])) {
            $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        }
        if (isset($data['alt_phone'])) {
            $data['alt_phone'] = preg_replace('/\D/', '', $data['alt_phone']);
        }

        return $data;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'       => 'nullable|exists:clients,id',
            'is_external'     => 'nullable|boolean',

            'document_type'   => 'required|in:DNI,RUC,CE',
            'document_number' => [
                'required',
                'regex:/^[0-9]{8,15}$/',
                Rule::unique('guarantors', 'document_number'),
            ],

            'first_name'      => 'required_if:document_type,DNI,CE|nullable|string|max:80',
            'last_name'       => 'required_if:document_type,DNI,CE|nullable|string|max:80',

            'company_name'    => 'required_if:document_type,RUC|nullable|string|max:150',
            'ruc'             => 'required_if:document_type,RUC|nullable|string|max:15',

            'phone'           => 'nullable|string|max:20',
            'alt_phone'       => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:120',
            'address'         => 'nullable|string|max:255',

            'relationship'    => 'nullable|string|max:80',
            'occupation'      => 'nullable|string|max:100',

            'status'          => 'required|in:0,1',

            'photo'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'document_type.required'   => 'Selecciona el tipo de documento.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.regex'    => 'El número debe tener entre 8 y 15 dígitos.',
            'document_number.unique'   => 'Este documento ya está registrado.',

            'first_name.required_if'   => 'Los nombres son obligatorios para persona natural.',
            'last_name.required_if'    => 'Los apellidos son obligatorios para persona natural.',

            'company_name.required_if' => 'La razón social es obligatoria para empresa.',
            'ruc.required_if'          => 'El RUC es obligatorio para empresa.',

            'email.email'              => 'Ingresa un correo válido.',
        ]);

        $data = $this->normalizeUserData($data);

        // Normalizar is_external
        $data['is_external'] = !empty($data['is_external']);

        // Construir full_name según sea persona o empresa
        if (!empty($data['document_type']) && $data['document_type'] === 'RUC') {
            // Para RUC exigimos company_name y ruc
            $v = Validator::make($data, [
                'company_name' => 'required|string|max:150',
                'ruc'          => 'required|string|max:15',
            ]);

            if ($v->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $v->errors(),
                ], 422);
            }

            $data['full_name'] = trim($data['company_name']);
        } else {
            $first = $data['first_name'] ?? '';
            $last  = $data['last_name'] ?? '';

            if (trim($first . ' ' . $last) === '') {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'first_name' => ['Debes registrar nombres y/o apellidos del garante.'],
                    ],
                ], 422);
            }

            $data['full_name'] = trim($first . ' ' . $last);
        }

        $data['status'] = (int)($data['status'] ?? 1);

        if (Auth::check()) {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
        }

        try {
            DB::beginTransaction();

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('guarantors');
            }

            $guarantor = Guarantor::create($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Garante creado correctamente.',
                'data'    => $guarantor,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error creando garante: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al crear el garante.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $guarantor = Guarantor::find($id);

        if (!$guarantor) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Garante no encontrado.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $guarantor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $guarantor = Guarantor::find($id);

        if (!$guarantor) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Garante no encontrado.',
            ], 404);
        }

        $data = $request->validate([
            'client_id'       => 'nullable|exists:clients,id',
            'is_external'     => 'nullable|boolean',

            'document_type'   => 'nullable|in:DNI,RUC,CE',
            'document_number' => [
                'nullable',
                'regex:/^[0-9]{8,15}$/',
                Rule::unique('guarantors', 'document_number')->ignore($guarantor->id),
            ],

            'first_name'      => 'nullable|string|max:80',
            'last_name'       => 'nullable|string|max:80',

            'company_name'    => 'nullable|string|max:150',
            'ruc'             => 'nullable|string|max:15',

            'phone'           => 'nullable|string|max:20',
            'alt_phone'       => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:120',
            'address'         => 'nullable|string|max:255',

            'relationship'    => 'nullable|string|max:80',
            'occupation'      => 'nullable|string|max:100',

            'status'          => 'required|in:0,1',

            'photo'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'document_number.regex'  => 'El número de documento debe tener entre 8 y 15 dígitos numéricos.',
            'document_number.unique' => 'El número de documento ya está registrado para otro garante.',

            'email.email'            => 'Ingresa un correo válido.',
        ]);
        $data = $this->normalizeUserData($data);

        $data['is_external'] = !empty($data['is_external']);

        // Construir full_name igual que en store
        if (!empty($data['document_type']) && $data['document_type'] === 'RUC') {
            $v = Validator::make($data, [
                'company_name' => 'required|string|max:150',
                'ruc'          => 'required|string|max:15',
            ]);

            if ($v->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $v->errors(),
                ], 422);
            }

            $data['full_name'] = trim($data['company_name']);
        } else {
            $first = $data['first_name'] ?? '';
            $last  = $data['last_name'] ?? '';

            if (trim($first . ' ' . $last) === '') {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'first_name' => ['Debes registrar nombres y/o apellidos del garante.'],
                    ],
                ], 422);
            }

            $data['full_name'] = trim($first . ' ' . $last);
        }

        if (Auth::check()) {
            $data['updated_by'] = Auth::id();
        }

        try {
            DB::beginTransaction();
            sleep(5);

            if ($request->hasFile('photo')) {
                $newPath = $request->file('photo')->store('guarantors');

                if ($guarantor->photo) {
                    Storage::delete($guarantor->photo);
                }

                $data['photo'] = $newPath;
            }

            $guarantor->update($data);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Garante actualizado correctamente.',
                'data'    => $guarantor->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error actualizando garante: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar el garante.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guarantor $guarantor)
    {
        // Igual que clientes: status = -1 para "eliminado"
        $guarantor->update(['status' => -1]);

        return response()->json([
            'message' => 'Garante eliminado correctamente.',
        ]);
    }
}
