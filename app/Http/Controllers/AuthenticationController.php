<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationApp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticationController extends Controller
{
    //this method adds new users
    public function createAccount(Request $request)
    {
        $attr = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'email' => 'required|string|email|unique:users,email',
            'apellido' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'estado' => 'required|numeric|max:1',
        ]);

        $user = User::create([
            'name' => $attr['name'],
            'password' => bcrypt($attr['password']),
            'email' => $attr['email'],
            'apellido' => $attr['apellido'],
            'cargo' => $attr['cargo'],
            'estado' => $attr['estado']
        ]);
        
        $user->assignRole('admin');
        
        // return ['token' => $user->createToken('tokens')->plainTextToken];
        return response()->json(['token' => $user->createToken('tokens')->plainTextToken], 201);

        // return $this->success([
        //     'token' => $user->createToken('tokens')->plainTextToken
        // ]);
    }
    
    //use this method to signin users
    public function signin(Request $request)
    {
        $attr = $request->validate([
            'email' => 'required|string|email|',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($attr)) {
            // return $this->error('Credentials not match', 401);
            return response()->json(['mensaje' => "usuario o contraseña incorrectos"], 401);
        }
        
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            // Hinting here for $user will be specific to the User object
            // return $this->success([
            //     'token' => $user->createToken($request->device_name)->plainTextToken,
            // ]);

            $dataCierre = ConfigurationApp::first();

            if ($dataCierre->cierre == 1) {
                if (!$user->hasAnyRole(["administrador", "supervisor"])) return response()->json(['mensaje' => "La app tiene el cierre activo"], 401);
            } 

            $token = $user->createToken('tokens')->plainTextToken;
            $newUser = DB::table('users')
                ->select('users.id as userId', 'users.name as nombre', 'users.apellido as apellido', 'users.cargo as cargo', 'users.email as email', 'users.email_verified_at as email_verified_at', 'users.estado as user_estado', 'users.created_at as user_created_at', 'users.updated_at as user_updated_at','roles.id as roleId','roles.name as roleName')

                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $user->id)
                ->first();    
            
            
            // DB::table('model_has_roles')->where('model_id', $user->id)
            return response()->json([
                'token' => $token,
                'user' => $newUser,    
            ], 200);
            // return [
            //     'token' => $token,
            //     'user' => $newUser,    
            // ];
        } else {
            // return $this->error('Error', 401);
            // return ['error' => "problemas"];
            return response()->json(['mensaje' => "problemas auth"], 401);
        }
        

    }

    // this method signs out users by removing tokens
    public function signout(Request $request)
    {
        // dd($request->user()->currentAccessToken());
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            $user->tokens()->delete();
        } else {
            return ['error' => "problemas"];
        }

        return [
            'message' => 'Tokens Revoked'
        ];
    }
}
