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
        // ✅ Validar los campos
        $data = $request->validate([
            'code'   => 'nullable|string|max:10',
            'name'   => 'required|string|min:3|max:100',
            'address' => 'required|string|min:5|max:150',
            'phone'  => 'nullable|string|min:6|max:20',
            'email'  => 'required|email|unique:branches,email',
            'manager_user_id'  => 'nullable|integer|exists:users,id',
            'is_active' => 'required|in:0,1',
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
