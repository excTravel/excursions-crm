<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_extra_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('extra_service_id')
                ->constrained()
                ->onDelete('cascade');

            $table->unsignedInteger('quantity')->default(1);  // Кол-во (обычно 1, либо peopleCount)
            $table->decimal('unit_price', 10, 2);             // Цена за единицу на момент брони
            $table->decimal('total_price', 10, 2);            // unit_price * quantity

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extra_services');
    }
};
