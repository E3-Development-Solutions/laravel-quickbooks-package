<?php

namespace E3DevelopmentSolutions\QuickBooks\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class QuickBooksAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->isConnectedToQuickBooks()) {
            return Redirect::route('quickbooks.connect')
                ->with('error', 'You must connect to QuickBooks before accessing this resource.');
        }

        return $next($request);
    }
}
