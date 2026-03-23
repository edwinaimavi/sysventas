<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guarantor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;



class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.users.index')->only('index', 'list');
        $this->middleware('can:admin.users.store')->only('store');
        $this->middleware('can:admin.users.update')->only('update');
        $this->middleware('can:admin.users.destroy')->only('destroy');
    }
    public function index()
    {
        $roles = Role::all();

        return view('admin.users.index', compact('roles'));
    }

    public function list()
    {
        $users = User::where('status', '!=', -1)->orderBy('id', 'desc')->get();

        ////si te sale un erro ejecunta en la rais del proyecto esto   composer require yajra/laravel-datatables-oracle 
        return DataTables::of($users)
            ->addIndexColumn()
            ->editColumn('status', function ($user) {
                return $user->status == 1
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';
            })
            ->addColumn('acciones', function ($user) {
                $statusOriginal = $user->status;
                $rutaFoto = Storage::url($user->photo);
                $rol = $user->roles->first()?->id ?? '';
                return view('admin.users.partials.acciones', compact('user', 'statusOriginal', 'rutaFoto', 'rol'))->render();
            })
            ->rawColumns(['status', 'acciones'])
            ->make(true);
    }
    public function create()
    {
        //
    }


    private function normalizeUserData(array $data): array
    {
        // Nombre y Apellidos: Primera letra mayúscula
        if (isset($data['name'])) {
            $data['name'] = ucwords(strtolower(trim($data['name'])));
        }

        if (isset($data['lastname'])) {
            $data['lastname'] = ucwords(strtolower(trim($data['lastname'])));
        }

        // Email: todo minúscula
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Dirección: primera letra mayúscula
        if (isset($data['address'])) {
            $data['address'] = ucfirst(strtolower(trim($data['address'])));
        }

        // DNI solo números
        if (isset($data['dni'])) {
            $data['dni'] = preg_replace('/\D/', '', $data['dni']);
        }

        // Teléfono solo números
        if (isset($data['phone'])) {
            $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        }

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'dni' => 'required|min:8|max:8|unique:users,dni',
            'name' => 'required|min:3|max:50',
            'lastname' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:20',
            'password_confirmation' => 'required|same:password',
            'phone' => 'nullable|min:9|max:15',
            'address' => 'nullable|min:3|max:150',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'status' => 'required',
        ], [

            // DNI
            'dni.required' => 'El DNI es obligatorio.',
            'dni.min' => 'El DNI debe tener 8 dígitos.',
            'dni.max' => 'El DNI debe tener 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',

            // Nombre
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener mínimo 3 caracteres.',

            // Apellidos
            'lastname.required' => 'Los apellidos son obligatorios.',

            // Email
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Este correo ya está registrado.',

            // Password
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
            'password_confirmation.same' => 'Las contraseñas no coinciden.',

            // Imagen
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser JPG o PNG.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',

            // Estado
            'status.required' => 'El estado es obligatorio.',
        ]);

        $data = $this->normalizeUserData($data);

        if ($request->hasFile('image')) {
            $data['photo'] = $request->file('image')->store('users');
        }
        $user = User::create($data);

        $user->roles()->sync([$request->input('role')]);


        return response()->json(['message' => 'Vehículo registrado correctamente']);
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
    public function update(Request $request, User $user)
    {
        //


        $data = $request->validate([
            'dni' => 'required|min:8|max:8|unique:users,dni,' . $user->id,
            'name' => 'required|min:3|max:50',
            'lastname' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|min:9|max:15',
            'address' => 'nullable|min:3|max:150',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'status' => 'required',
        ], [

            // DNI
            'dni.required' => 'El DNI es obligatorio.',
            'dni.min' => 'El DNI debe tener 8 dígitos.',
            'dni.max' => 'El DNI debe tener 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',

            // Nombre
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener mínimo 3 caracteres.',

            // Apellidos
            'lastname.required' => 'Los apellidos son obligatorios.',

            // Email
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'Este correo ya está registrado.',


            // Imagen
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser JPG o PNG.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',

            // Estado
            'status.required' => 'El estado es obligatorio.',
        ]);

        $data = $this->normalizeUserData($data);

        //VALIDAMOS LA CONTRASEÑA
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|min:6|max:20',
                'password_confirmation' => 'required|same:password',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            if ($user->photo) {
                Storage::delete($user->photo);
            }
            $data['photo'] = $request->file('image')->store('users');
        }

        $user->update($data);
        $role = Role::findById($request->input('role'));
        $user->syncRoles([$role->name]);

        return response()->json(['message' => 'Usuario actualizado correctamente']);
    }

    /**
     * Remove the specified resource from storage.
     *//*  */
    public function destroy(User $user)
    {
        $user->update(['status' => -1]);

        return response()->json([
            'message' => 'El usuario ha sido eliminado correctamente'
        ]);
    }
}
