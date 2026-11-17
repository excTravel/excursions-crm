<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excursion_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');                    // Название услуги: "Фотосессия"
            $table->text('description')->nullable();   // Описание (опционально)
            $table->decimal('price', 10, 2);           // Цена, например 500.00

            $table->boolean('per_person')->default(false); // true = за человека, false = за бронирование
            $table->boolean('is_active')->default(true);   // Можно выключать услугу

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_services');
    }
};
