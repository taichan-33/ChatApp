<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // 作成したマイグレーションファイルの中身
public function up(): void
{
    Schema::table('settings', function (Blueprint $table) {
        // 既存のカラムの後に追加する
        $table->string('openai_api_key')->nullable()->after('ai_model');
        $table->string('bot_name')->default('AIアシスタント')->after('openai_api_key');
        $table->string('bot_icon_path')->nullable()->after('bot_name');
        $table->string('admin_email')->nullable()->after('bot_icon_path');
        $table->string('admin_password')->nullable()->after('admin_email');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
