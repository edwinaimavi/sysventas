<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchSessionController extends Controller
{
    //

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ], [
            'branch_id.required' => 'Debes seleccionar una sucursal.',
            'branch_id.exists'   => 'La sucursal seleccionada no es válida.',
        ]);

        $branch = Branch::findOrFail($data['branch_id']);

        session([
            'branch_id'   => $branch->id,
            'branch_name' => $branch->name,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sucursal seleccionada correctamente.',
        ]);
    }
}
