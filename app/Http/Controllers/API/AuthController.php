<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Utilisateur::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->mdp)) {
            return response()->json([
                'message' => 'Identifiants incorrects'
            ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'username' => $user->username,
                'type_utilisateur' => $user->type_utilisateur,
                'idRegion' => $user->idRegion
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'nom' => $request->user()->nom,
                'prenom' => $request->user()->prenom,
                'username' => $request->user()->username,
                'type_utilisateur' => $request->user()->type_utilisateur,
                'idRegion' => $request->user()->idRegion
            ]
        ]);
    }
}
