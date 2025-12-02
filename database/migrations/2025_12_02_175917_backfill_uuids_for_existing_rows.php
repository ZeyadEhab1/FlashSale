<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill UUIDs for products
        DB::table('products')->whereNull('uuid')->update([
            'uuid' => DB::raw('UUID()')
        ]);

        // Backfill UUIDs for holds
        DB::table('holds')->whereNull('uuid')->update([
            'uuid' => DB::raw('UUID()')
        ]);

        // Backfill UUIDs for orders
        DB::table('orders')->whereNull('uuid')->update([
            'uuid' => DB::raw('UUID()')
        ]);

        // Backfill UUIDs for payment_webhooks
        DB::table('payment_webhooks')->whereNull('uuid')->update([
            'uuid' => DB::raw('UUID()')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - UUIDs can remain
    }
};
