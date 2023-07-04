<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\ReciboHistorial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [];
        $status = 200;
        $clienteEstado = 1; // Activo
        // User::whereHas("roles", function($q){ $q->where("name", "admin"); })->get()

        // dd($clienteEstado);
        $usuarios =  User::all();

        // $cliente =  Cliente::find($id);
        if(count($usuarios) > 0){
            foreach ($usuarios as $usuario) {
                $usuario->factura;
                if($usuario->recibo != null ){
                    $usuario->ultimo_recibo = ReciboHistorial::where([
                        ["recibo_id", $usuario->recibo->id],
                    ]
                    )->orderBy('created_at', 'desc')->first();
                }
                $usuario->meta;

                $role_id = DB::table('model_has_roles')->where('model_id', $usuario->id)->first();
                $usuario->role_id = $role_id->role_id;
            }

            $response = $usuarios;
            $status = 200;

        }else{
            $response[] = "El usuario no existe o fue eliminado.";
        }

        return response()->json($response, $status);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];
        $status = 400;

        $validation = Validator::make($request->all() ,[
            'name' => 'required|string|max:255',
            'password' => 'required|string|confirmed',
            'email' => 'required|string|email|unique:users,email',
            'apellido' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'estado' => 'required|numeric|max:1',
            'role' => 'required|numeric',
        ]);

        if($validation->fails()) {
            $response[] = $validation->errors();
        }else {
            $user = User::create([
                'name' => $request['name'],
                'password' => bcrypt($request['password']),
                'email' => $request['email'],
                'apellido' => $request['apellido'],
                'cargo' => $request['cargo'],
                'estado' => $request['estado']
            ]);
            $role = Role::find($request['role']);
            // dd($user);
            $user->assignRole($role->name);
            $status = 201;
            $user->createToken('tokens')->plainTextToken;
            // $response[] = ['token' => $user->createToken('tokens')->plainTextToken];

            return response()->json([
                'id' => $user->id,
            ], 201);
        }




        return response()->json($response, $status);
        // return $this->success([
        //     'to
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $response = [];
        $status = 400;
        // $clienteEstado = 1; // Activo
        // User::whereHas("roles", function($q){ $q->where("name", "admin"); })->get()

        if(is_numeric($id)){

            // if($request->input("estado") !== null) $clienteEstado = $request->input("estado");

            // dd($clienteEstado);
            $usuario =  User::where([
                ['id', '=', $id],
                // ['estado', '=', $clienteEstado],
            ])->first();



            // $cliente =  Cliente::find($id);
            if($usuario){
                $role_id = DB::table('model_has_roles')->where('model_id', $usuario->id)->first();

                $usuario->clientes;
                $usuario->factura;
                $usuario->role_id = $role_id->role_id;
                $usuario->recibo;
                $usuario->recibo;

                $response = $usuario;
                $status = 200;

            }else{
                $response[] = "El usuario no existe o fue eliminado.";
            }

        }else{
            $response[] = "El usuario de Id debe ser numerico.";
        }

        return response()->json($response, $status);
    }

    public function edit(UsuarioController $usuarioController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $response = [];
        $status = 400;

        if(is_numeric($id)){
            $usuario =  User::find($id);

            if($usuario){
                $validation = Validator::make($request->all() ,[
                    'name' => 'required|string|max:255',
                    // 'password' => 'required|string|min:6|confirmed',
                    'email' => 'required|string|email|unique:users,email,'.$id,
                    'apellido' => 'required|string|max:255',
                    'cargo' => 'required|string|max:255',
                    'estado' => 'required|numeric|max:1',
                    'role' => 'required|numeric',
                ]);

                if($validation->fails()) {
                    $response[] = $validation->errors();
                } else {


                    $usuarioUpdate = $usuario->update([
                        'name' => $request['name'],
                        // 'password' => bcrypt($request['password']),
                        'email' => $request['email'],
                        'apellido' => $request['apellido'],
                        'cargo' => $request['cargo'],
                        'estado' => $request['estado']
                    ]);

                    DB::table('model_has_roles')->where('model_id', $usuario->id)->delete();
                    $role = Role::find($request['role']);

                    $usuario->assignRole($role->name);

                    if($usuarioUpdate){
                        $response[] = 'Usuario modificado con exito.';
                        $status = 200;

                    }else{
                        $response[] = 'Error al modificar los datos.';
                    }

                }

            }else{
                $response[] = "El Usuario no existe.";
            }

        }else{
            $response[] = "El Valor de Id debe ser numerico.";
        }

        return response()->json($response, $status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword( $id,Request $request)
    {
        $response = [];
        $status = 400;

        if(is_numeric($id)){
            $usuario =  User::find($id);
            // dd($usuario);
            if($usuario){
                $validation = Validator::make($request->all() ,[
                    'password' => 'required|string|confirmed',
                ]);

                if($validation->fails()) {
                    $response[] = $validation->errors();
                } else {

                    $usuarioUpdate = $usuario->update([
                        'password' => bcrypt($request['password']),
                    ]);

                    if($usuarioUpdate){
                        $response[] = 'Clave modificada con exito';
                        $status = 200;

                    }else{
                        $response[] = 'Error al modificar la clave';
                    }

                }

            }else{
                $response[] = "El Usuario no existe.";
            }

        }else{
            $response[] = "El Valor de Id debe ser numerico.";
        }

        return response()->json($response, $status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = [];
        $status = 400;

        if(is_numeric($id)){
            $cliente =  User::find($id);

            if($cliente){
                $clienteDelete = $cliente->update([
                    'estado' => 0,
                ]);

                if($clienteDelete){
                    $response[] = 'El usuario fue eliminado con exito.';
                    $status = 200;

                }else{
                    $response[] = 'Error al eliminar el usuario.';
                }

            }else{
                $response[] = "El usuario no existe.";
            }

        }else{
            $response[] = "El Valor de Id debe ser numerico.";
        }

        return response()->json($response, $status);
    }
}
