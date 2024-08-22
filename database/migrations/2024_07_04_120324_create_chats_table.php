<?php

use App\Enums\chatTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->nullable(); // Group name, nullable for individual chats
            $table->enum('type', [chatTypeEnum::INDIVIDUAL, chatTypeEnum::GROUP])->default(chatTypeEnum::INDIVIDUAL);
            $table->timestamps();
        });

        // Pivot table for chat participants
        Schema::create('chat_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_user');
        Schema::dropIfExists('chats');
    }
}

