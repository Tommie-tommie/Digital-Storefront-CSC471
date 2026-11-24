<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdmin
{
    # no Laravel Auth, login just puts user_id + user_type in the session
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('user_type') !== 'ADMIN') {
            return redirect()->route('admin.login');
        }
        return $next($request);
    }
}
