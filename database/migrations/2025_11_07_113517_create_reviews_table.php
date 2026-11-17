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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');       // кто оставил отзыв
            $table->foreignId('excursion_id')->constrained()->onDelete('cascade');  // на какую экскурсию
            $table->tinyInteger('rating')->unsigned();                              // оценка от 1 до 5
            $table->text('comment')->nullable();                                    // текст отзыва
            $table->timestamps();

            // чтобы один пользователь не оставлял несколько отзывов на одну экскурсию
            $table->unique(['user_id', 'excursion_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
