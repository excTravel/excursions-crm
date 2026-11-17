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
        // The initial users migration already includes this column. Keep this
        // migration safe for databases that were created from that schema.
        if (! Schema::hasColumn('users', 'is_blocked')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_blocked')->default(false)->after('role');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not drop the column: it is also part of the initial users schema.
    }
};
