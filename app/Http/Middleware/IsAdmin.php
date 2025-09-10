<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 管理者としてログインしたことを示すセッションキーがあるか確認する
        if ($request->session()->get('is_admin_logged_in')) {
            return $next($request);
        }

        // 管理者としてログインしていなければ、管理者ログインページにリダイレクト
        return redirect()->route('admin.login');
    }
}