<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function registerProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|max:255',
            'clerk_user_id' => 'required',
            'company_id' => 'required',
        ]);

        $user = User::create([
            'clerk_user_id' => $validated['clerk_user_id'],
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'company_id'    => $validated['company_id'],
            'password'      => bcrypt(str()->random(40)),
        ]);
        
        return response()->json([
            'message' => 'Usuário sincronizado com sucesso',
            'user'    => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

}
