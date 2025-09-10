<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // settingsテーブルの最初のレコード（ID=1など）を更新、なければ作成する
        Setting::updateOrCreate(
            ['id' => 1], // 常に同じレコードを更新するようにする
            [
                'system_prompt' => 'あなたは優秀なアシスタントです。',
                'ai_model' => 'gpt-5-2025-08-07', // モデル名を適宜変更してください
                'openai_api_key' => 'YOUR_API_KEY', // ご自身のAPIキーを設定してください
                'bot_name' => 'AIアシスタント',
                'admin_email' => 'admin@example.com',
                'admin_password' => Hash::make('password'), // 初期パスワードは 'password'
            ]
        );
    }
}