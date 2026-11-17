<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BlockedUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_blocked) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Ваш аккаунт заблокирован администратором.',
            ]);
        }

        return $next($request);
    }
}

