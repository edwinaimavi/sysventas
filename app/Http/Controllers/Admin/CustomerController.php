<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     return view('admin.customers.index');
    }

    
    public function list()
    {
        $customers = Customer::where('status', '!=', -1)->orderBy('id', 'desc')->get();
        return DataTables::of($customers)
            ->addIndexColumn()
            ->editColumn('status',function($customer){
                return $customer->status ==1
                ? '<span class="badge badge-success">Activo</span>'
                : '<span class="badge badge-danger">Inactivo</span>';
            })
            ->addColumn('acciones', function ($customer){
                $statusOriginal = $customer->status;
                return view('admin.customers.partials.acciones', compact('customer', 'statusOriginal'))->render();
            })
            ->rawColumns(['status','acciones'])
            ->make(true);
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
