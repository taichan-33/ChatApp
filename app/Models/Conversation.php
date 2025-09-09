<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // BelongsToをインポート


class Conversation extends Model
{
    use HasFactory;
    // fillableにuser_idを追加
    protected $fillable = ['user_id', 'title'];


    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Conversationは一人のUserに属する、という関係を定義
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}