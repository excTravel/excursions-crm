<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            if (! Schema::hasColumn('excursions', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('status');
            }

            if (! Schema::hasColumn('excursions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('excursions', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            if (Schema::hasColumn('excursions', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('excursions', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('excursions', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
