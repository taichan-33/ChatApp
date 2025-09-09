<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // データが存在しない場合は、空のSettingモデルインスタンスを生成します。
        // これにより、ビューで$settingsがnullになるのを防ぎます。
        $settings = Setting::firstOrNew([]);
        
        $users = User::latest()->paginate(10);
        return view('admin.index', compact('settings', 'users'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'system_prompt' => 'required|string|max:2000',
            'ai_model' => 'required|string|max:255',
        ]);

        // ▼▼▼ 改善点 ▼▼▼
        // IDに依存せず、常にテーブルの最初のレコードを取得または新規作成します。
        $settings = Setting::firstOrNew([]);
        
        // 取得または作成したモデルに、リクエストされた値を設定します。
        $settings->fill($validated);
        
        // データベースに保存します。（INSERTまたはUPDATEが自動的に実行されます）
        $settings->save();

        return redirect()->route('admin.index')->with('success', '設定を更新しました。');
    }
}

