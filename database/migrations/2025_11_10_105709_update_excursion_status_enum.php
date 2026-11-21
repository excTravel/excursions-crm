<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Обновляем ENUM-поле вручную
        DB::statement("ALTER TABLE excursions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'archived') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Возвращаем назад (без archived)
        DB::statement("ALTER TABLE excursions MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
