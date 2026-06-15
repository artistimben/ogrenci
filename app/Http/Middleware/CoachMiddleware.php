<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoachMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isCoach()) {
            abort(403, 'Unauthorized access');
        }

        $user = auth()->user();
        $subscription = $user->subscription;

        // Check if subscription exists, is active, and is not expired
        if (!$subscription || !$subscription->is_active || ($subscription->end_date && $subscription->end_date->isPast())) {
            abort(403, 'Aboneliğiniz sona ermiş veya aktif değil. Lütfen sistem yöneticinizle iletişime geçin.');
        }

        return $next($request);
    }
}
