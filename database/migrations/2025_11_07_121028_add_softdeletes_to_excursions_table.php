<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('excursions', function (Illuminate\Database\Schema\Blueprint $table) {
            if (! Schema::hasColumn('excursions', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('excursions', function (Illuminate\Database\Schema\Blueprint $table) {
            if (Schema::hasColumn('excursions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
