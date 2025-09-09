<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting; // Settingモデルをインポート

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'system_prompt' => 'あなたは優秀なアシスタントです。',
            'ai_model' => 'gpt-5-2025-08-07',
        ]);
    }
}