<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ClerkAuth
{
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header('Authorization');

        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = substr($auth, 7);

        // Buscar JWKS com cache (1h)
        $jwks = Cache::remember('clerk_jwks', 3600, function () {
            $frontendApi = env('CLERK_FRONTEND_API'); 
            $jwksUrl = "https://{$frontendApi}/.well-known/jwks.json";

            $response = Http::get($jwksUrl);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        });

        if (!$jwks || empty($jwks['keys'])) {
            return response()->json(['message' => 'JWKS not found'], 500);
        }

        try {
            // Decodificar JWT usando JWKS do Clerk
            $decoded = JWT::decode(
                $token,
                JWK::parseKeySet($jwks) // CORRETO para firebase/php-jwt 6.x
            );

            // ID do usuário no Clerk
            $clerkId = $decoded->sub;
            if (!$clerkId) {
                throw new \Exception('Invalid token: missing sub');
            }

            $user = User::all();

            // Criar ou sincronizar usuário local
            $user = User::firstOrCreate(
                ['clerk_user_id' => $clerkId],
            );

            // if (!$user->is_active) {
            //     return response()->json(['message' => 'User inactive'], 403);
            // }

            // Injeta usuário autenticado no request
            $request->setUserResolver(fn() => $user);

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
