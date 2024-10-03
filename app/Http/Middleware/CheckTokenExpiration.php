<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()->currentAccessToken();

        // Vérifier si le token a expiré
        if ($token->created_at->diffInMinutes(Carbon::now()) > config('sanctum.expiration')) {
            $token->delete(); // Supprimer le token expiré
            return response()->json(['message' => 'Token expired.'], 401);
        }

        return $next($request);
    }
}
