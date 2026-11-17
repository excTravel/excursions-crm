<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_status', 20)
                ->default('unpaid')
                ->after('status'); // unpaid | paid | failed

            $table->string('payment_method', 50)
                ->nullable()
                ->after('payment_status');

            $table->string('payment_last4', 4)
                ->nullable()
                ->after('payment_method');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('payment_last4');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'payment_last4',
                'paid_at',
            ]);
        });
    }
};
