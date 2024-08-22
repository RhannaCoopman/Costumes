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
        Schema::create('annotations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->decimal('xPosition', 5, 2);
            $table->decimal('yPosition', 5, 2);
            $table->string('user_description')->nullable();
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('store')->nullable();
            $table->string('url')->nullable();
            $table->string('article_number')->nullable();

            $table->foreignId('image_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annotations', function (Blueprint $table) {
            $table->dropForeign(['image_id']);
        });

        Schema::dropIfExists('annotations');
    }
};
