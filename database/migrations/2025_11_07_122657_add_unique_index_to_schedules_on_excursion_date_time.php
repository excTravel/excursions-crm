<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // имя индекса
        $indexName = 'schedules_excursion_date_time_deleted_unique';

        // проверяем, есть ли уже такой индекс
        $exists = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'schedules'
              AND index_name = ?
        ", [$indexName]);

        if (! $exists || (int) $exists->cnt === 0) {
            Schema::table('schedules', function (Blueprint $table) use ($indexName) {
                // уникальный индекс по 4 колонкам
                $table->unique(['excursion_id', 'date', 'start_time', 'deleted_at'], $indexName);
            });
        }
    }

    public function down(): void
    {
        $indexName = 'schedules_excursion_date_time_deleted_unique';

        $exists = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'schedules'
              AND index_name = ?
        ", [$indexName]);

        if ($exists && (int) $exists->cnt > 0) {
            Schema::table('schedules', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }
};
