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

        // Abonelik yoksa veya süresi dolmuşsa özel sayfaya yönlendir
        if (!$subscription || !$subscription->is_active || ($subscription->end_date && $subscription->end_date->isPast())) {
            // Livewire AJAX isteği ise JSON hata dön
            if ($request->hasHeader('X-Livewire')) {
                return response()->json([
                    'redirect' => url('/subscription-expired'),
                ], 200);
            }
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
