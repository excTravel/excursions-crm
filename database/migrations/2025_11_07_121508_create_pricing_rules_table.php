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
        Schema::create('pricing_rules', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('excursion_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('min_people')->default(1);
            $table->unsignedInteger('max_people')->nullable(); // NULL = без верхней границы
            $table->decimal('price', 10, 2);                   // цена интервала
            $table->enum('mode', ['per_person', 'per_group'])->default('per_person');
            $table->timestamps();

            // базовая уникальность по "точному" диапазону (NB: NULL в max_people допускает дубли!)
            $table->unique(['excursion_id', 'min_people', 'max_people'], 'pricing_rules_unique_range');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
