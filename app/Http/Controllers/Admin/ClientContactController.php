<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientContact;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    public function list(Request $request)
    {
        $clientId = $request->get('client_id');

        // Si no hay client_id, devolvemos simplemente una consulta vacía
        // para que DataTables reciba un JSON válido pero sin registros.
        $query = ClientContact::query();

        if ($clientId) {
            $query->where('client_id', $clientId);
        } else {
            // Fuerza 0 resultados (WHERE 1=0)
            $query->whereRaw('1 = 0');
        }

        return DataTables::of($query)
            ->addColumn('is_primary_badge', function ($c) {
                return $c->is_primary
                    ? '<span class="badge badge-success">Sí</span>'
                    : '<span class="badge badge-secondary">No</span>';
            })
            ->addColumn('acciones', function ($c) {
                return '
            <button class="btn btn-sm btn-info editContact" data-id="' . $c->id . '">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger deleteContact" data-id="' . $c->id . '">
                <i class="fas fa-trash"></i>
            </button>';
            })
            ->rawColumns(['is_primary_badge', 'acciones'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'contact_type'   => 'required|in:Domicilio,Trabajo,Referencia,Otro',
            'address'        => 'nullable|string|max:255',
            'district'       => 'nullable|string|max:100',
            'province'       => 'nullable|string|max:100',
            'department'     => 'nullable|string|max:100',
            'reference'      => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'alt_phone'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:120',
            'contact_name'   => 'nullable|string|max:120',
            'relationship'   => 'nullable|string|max:50',
            'is_primary'     => 'nullable|boolean',
        ]);

        $data['is_primary'] = !empty($data['is_primary']);

        $contact = ClientContact::create($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Contacto creado correctamente.',
            'data'    => $contact,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $contact = ClientContact::find($id);

        if (! $contact) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contacto no encontrado.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $contact,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $contact = ClientContact::find($id);

        if (! $contact) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Contacto no encontrado.',
            ], 404);
        }

        $data = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'contact_type'   => 'required|in:Domicilio,Trabajo,Referencia,Otro',
            'address'        => 'nullable|string|max:255',
            'district'       => 'nullable|string|max:100',
            'province'       => 'nullable|string|max:100',
            'department'     => 'nullable|string|max:100',
            'reference'      => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'alt_phone'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:120',
            'contact_name'   => 'nullable|string|max:120',
            'relationship'   => 'nullable|string|max:50',
            'is_primary'     => 'nullable|boolean',
        ]);

        $data['is_primary'] = !empty($data['is_primary']);

        $contact->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Contacto actualizado correctamente.',
            'data'    => $contact->fresh(),
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $contact = ClientContact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contacto no encontrado.'
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contacto eliminado correctamente.'
        ]);
    }
}
