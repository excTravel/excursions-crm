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
        Schema::table('excursions', function (Illuminate\Database\Schema\Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('excursions', 'slug')) {
                $table->string('slug')->unique()->after('title');
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            //
        });
    }
};
