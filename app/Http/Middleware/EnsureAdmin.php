<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the Admin Panel.');
        }

        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access: Admin privileges required.');
        }

        return $next($request);
    }
}
