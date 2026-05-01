<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->id();
        $table->text('user_message'); // رسالة المستخدم
        $table->text('bot_reply');    // رد البوت
        $table->timestamps();         // تاريخ ووقت الرسالة
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
