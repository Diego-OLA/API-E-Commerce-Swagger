<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
       public function register(Request $request){
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email'  => 'required|email|unique:users',
            'password'  => 'required|min:8|confirmed',

        ]);

        $user = User::create([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), 
            'role'  => 'user',
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request){
     $validated = $request->validate([
        'email'=>'email|required',
        'password' => 'required'

     ]);

     $user = User::where('email',$request->email)->first();

     /*if(! $user || !Hash::check($request->password,$user->password)){
        return response()->json(['message'=>'Clave incorrecta'],401);
     }*/

     $credenciales = $request->only('email', 'password');

     if(! $token = auth('api')->attempt($credenciales)){
        return response()->json(['message'=>'No autorizado'],401);
     }

     return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_id' => auth('api')->factory()->getTTL()*60
     ]);

    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['message'=>'Sesión cerrada exitosamente']);
    }
}
