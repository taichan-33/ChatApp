<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // HasManyをインポート

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['conversation_id', 'role', 'content'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // リレーション名を images から files に変更し、参照先モデルも変更
    public function files(): HasMany
    {
        return $this->hasMany(MessageFile::class);
    }
}