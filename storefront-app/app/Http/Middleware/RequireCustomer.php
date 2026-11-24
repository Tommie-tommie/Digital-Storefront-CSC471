<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCustomer
{
    # same idea as RequireAdmin but for the shopper side (cart/checkout/orders)
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('user_type') !== 'CUSTOMER') {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
