<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_path',
        'original_name',
        'mime_type',
    ];
}