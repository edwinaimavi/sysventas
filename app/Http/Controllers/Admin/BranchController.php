<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

use App\Models\Branch;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.branches.index', compact('users'));
    }

    //FUNCION PARA LISTAR LAS SUCURSALES EN LA DATATABLE
    public function list()
    {

        $branches = Branch::where('is_active', '!=', -1)->orderBy('id', 'desc')->get();
        return DataTables::of($branches)
            ->addIndexColumn()
            ->editColumn('is_active', function ($branch) {
                return $branch->is_active == 1
                    ? '<span class="badge bg-success text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> Activo
                    </span>'
                    : '<span class="badge bg-danger text-light rounded-pill px-3 py-2 fs-6 shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> Inactivo
                    </span>';
            })
            ->addColumn('acciones', function ($branch) {
                $statusOriginal = $branch->is_active;
                return view('admin.branches.partials.acciones', compact('branch', 'statusOriginal'))->render();
            })
            ->rawColumns(['is_active', 'acciones'])
            ->make(true);
    }

    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'code'            => ['nullable', 'string', 'max:50'],
            'address'         => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:30'],
            'email'           => ['required', 'email', 'max:255', 'unique:branches,email'],
            'manager_user_id' => ['nullable', 'exists:users,id'],
            'is_active'       => ['required', 'in:0,1'],
        ], [
            'name.required'    => 'Falta llenar el nombre de la sucursal.',
            'address.required' => 'Falta llenar la dirección.',
            'phone.required'   => 'Falta llenar el teléfono.',
            'email.required'   => 'Falta llenar el email.',
            'email.email'      => 'El email no tiene un formato válido.',
            'email.unique'     => 'Ese email ya está registrado en otra sucursal.',
            'is_active.required' => 'Debes seleccionar el estado (Activo/Inactivo).',
        ]);
        // ✅ Guardar en la base de datos
        $branch = Branch::create([
            'code'     => $data['code'] ?? null,
            'name'     => $data['name'],
            'address'  => $data['address'],
            'phone'    => $data['phone'] ?? null,
            'email'    => $data['email'],
            'manager_user_id' => $data['manager_user_id'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        // ✅ Respuesta JSON para AJAX
        return response()->json([
            'message' => 'Sucursal registrada correctamente',
            'branch'  => $branch, // 👈 importante para el home
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'code'   => 'nullable|string|max:10|unique:branches,code,' . $branch->id, // No obligatorio
            'name'   => 'required|string|min:3|max:100', // Nombre de la sucursal
            'address' => 'required|string|min:5|max:150', // Dirección
            'phone'  => 'nullable|string|min:6|max:20', // Teléfono (opcional)
            'email'  => 'required|email|unique:branches,code,' . $branch->id, // Email único
            'manager_user_id'  => 'nullable',
            'integer',
            'exists:users,id', // Email único
            'is_active' => 'required|in:0,1', // Estado (activo o inactivo)
        ]);

        $branch->update($data);
        return response()->json(['message' => 'Suscursal actualizado correctamente']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch  $branch)
    {
        $branch->update(['is_active' => -1]);
        return response()->json(['message' => 'Sucursal eliminada correctamente']);
    }
}
