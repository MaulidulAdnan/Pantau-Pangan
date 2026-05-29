<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 401);
            }
            if ($user->status === 'suspended') {
                return response()->json(['message' => 'Akun Anda telah di-suspend'], 403);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token telah kadaluarsa'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        return $next($request);
    }
}
