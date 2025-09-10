<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting; // Settingモデルを読み込むために追加
use Illuminate\Support\Facades\Hash; // Hashクラスを読み込むために追加
use Illuminate\Validation\ValidationException; // ValidationExceptionクラスを読み込むために追加

class AdminLoginController extends Controller
{
    /**
     * 管理者ログインフォームを表示する
     */
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // settingsテーブルの最初のレコードを取得
        $settings = Setting::first();

        if ($settings && $request->email === $settings->admin_email && Hash::check($request->password, $settings->admin_password)) {
            // 認証成功
            $request->session()->regenerate();
            $request->session()->put('is_admin_logged_in', true);
            return redirect()->intended(route('admin.index'));
        }

        // 認証失敗
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * 管理者ログアウト処理
     */
    public function logout(Request $request)
    {
        $request->session()->forget('is_admin_logged_in');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}