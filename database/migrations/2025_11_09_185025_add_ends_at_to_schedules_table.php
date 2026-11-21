<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('schedules', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->after('start_time');
            }
            if (! Schema::hasColumn('schedules', 'finished_at')) {
                $table->dateTime('finished_at')->nullable()->after('ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'finished_at')) {
                $table->dropColumn('finished_at');
            }
            if (Schema::hasColumn('schedules', 'ends_at')) {
                $table->dropColumn('ends_at');
            }
        });
    }
};
