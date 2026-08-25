<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Akses khusus Admin.');
            }

            return redirect()->route('pos')->with('error', 'Akses ditolak. Halaman ini khusus untuk Admin.');
        }

        return $next($request);
    }
}
