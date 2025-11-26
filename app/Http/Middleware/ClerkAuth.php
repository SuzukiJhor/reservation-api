<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
class ClerkAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            [$clerkUserId, $user] = $this->authenticate($request);
            if ($user) {
                Auth::login($user);
                $request->setUserResolver(fn() => $user);
            }
            $request->merge(['clerk_user_id' => $clerkUserId]);
            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized',
                'error'   => $e->getMessage(),
            ], 401);
        }
    }

    private function authenticate(Request $request)
    {
        $token = $this->extractToken($request);
        $jwks = $this->getJwks();
        $payload = $this->decodeToken($token, $jwks);
        $checkSyncUser = $this->checkSyncUser($payload);

        return [$payload->sub, $checkSyncUser];
    }

    private function extractToken(Request $request)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            throw new \Exception('Missing or invalid Authorization header');
        }
        return substr($auth, 7);
    }

    private function getJwks()
    {
        return Cache::remember('clerk_jwks', 3600, function () {
            $frontendApi = env('CLERK_FRONTEND_API');
            $jwksUrl = "https://{$frontendApi}/.well-known/jwks.json";
            $response = Http::get($jwksUrl);
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch JWKS');
            }
            return $response->json();
        });
    }

    private function decodeToken($token, $jwks)
    {
        try {
            return JWT::decode(
                $token,
                JWK::parseKeySet($jwks)
            );
        } catch (\Exception $e) {
            throw new \Exception('Invalid token: ' . $e->getMessage());
        }
    }


    private function checkSyncUser($payload)
    {
        if (empty($payload->sub)) {
            throw new \Exception('Token missing sub (user ID)');
        }
        $user = User::where('clerk_user_id', $payload->sub)->first();
        return $user ?? null;
    }
}
