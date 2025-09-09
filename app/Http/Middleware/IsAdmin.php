<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // ▼▼▼ この行を追加しました！ ▼▼▼


class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
{
    if (!Auth::check() || !Auth::user()->is_admin) {
        abort(403, '管理者専用ページです。');
    }
    return $next($request);
}
}
