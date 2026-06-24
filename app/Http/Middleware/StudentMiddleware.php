<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isStudent()) {
            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            }
            if ($user->isCoach()) {
                return redirect('/coach/dashboard');
            }
            return redirect('/');
        }

        return $next($request);
    }
}
