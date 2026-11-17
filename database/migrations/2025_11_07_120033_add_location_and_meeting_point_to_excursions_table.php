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
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('meeting_point_lat', 10, 7)->nullable();
            $table->decimal('meeting_point_lng', 10, 7)->nullable();
            $table->string('meeting_point_address')->nullable();
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
