<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User; // ★ Userモデルを読み込むために追加
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * 管理画面のトップページを表示します。
     * データベースから設定と全ユーザー情報を読み込みます。
     */
    public function index()
    {
        // settingsテーブルの最初のレコードを取得します
        $settings = Setting::first();
        
        // ★ ユーザー情報を1ページあたり10件で取得します
        $users = User::latest()->paginate(10); 

        // 取得した設定とユーザー情報をビューに渡します
        return view('admin.index', compact('settings', 'users'));
    }

    /**
     * 管理画面から送信された設定を更新します。
     */
    public function updateSettings(Request $request)
    {
        // 送信されたデータのバリデーション（検証）
        $request->validate([
            'openai_api_key' => 'nullable|string',
            'bot_name' => 'required|string|max:255',
            'bot_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'system_prompt' => 'required|string',
            'ai_model' => 'required|string|max:255',
        ]);

        // settingsテーブルの最初のレコードを取得（なければ新規作成）
        $settings = Setting::firstOrCreate(['id' => 1]);

        // APIキーが入力されていれば更新（入力がなければ現在の値を維持）
        if ($request->filled('openai_api_key')) {
            $settings->openai_api_key = $request->input('openai_api_key');
        }

        // その他のテキスト情報を更新
        $settings->fill($request->only([
            'bot_name',
            'system_prompt',
            'ai_model'
        ]));
        
        // Botアイコンがアップロードされた場合の処理
        if ($request->hasFile('bot_icon')) {
            // 以前のアイコンがあれば削除
            if ($settings->bot_icon_path) {
                Storage::disk('public')->delete($settings->bot_icon_path);
            }
            // 新しいアイコンを保存し、パスをデータベースに記録
            $path = $request->file('bot_icon')->store('bot_icons', 'public');
            $settings->bot_icon_path = $path;
        }

        // データベースに保存
        $settings->save();

        // 保存完了後、メッセージと共に管理画面トップにリダイレクト
        return redirect()->route('admin.index')->with('success', '設定を更新しました。');
    }
}