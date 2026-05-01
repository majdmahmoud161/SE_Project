<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    // هذا السطر هو مفتاح الحل: بيسمح للعناصر بالنزول للداتا بيز
    protected $fillable = ['user_message', 'bot_reply'];
}