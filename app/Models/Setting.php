<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'system_prompt',
        'ai_model',
        'openai_api_key', // 追加
        'bot_name',       // 追加
        'bot_icon_path',  // 追加
        'admin_email',    // 追加
        'admin_password', // 追加
    ];
}