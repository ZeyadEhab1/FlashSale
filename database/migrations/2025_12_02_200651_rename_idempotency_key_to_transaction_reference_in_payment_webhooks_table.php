<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('payment_webhooks', 'idempotency_key')) {
            DB::statement('ALTER TABLE payment_webhooks CHANGE idempotency_key transaction_reference VARCHAR(255)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('payment_webhooks', 'transaction_reference')) {
            DB::statement('ALTER TABLE payment_webhooks CHANGE transaction_reference idempotency_key VARCHAR(255)');
        }
    }
};
