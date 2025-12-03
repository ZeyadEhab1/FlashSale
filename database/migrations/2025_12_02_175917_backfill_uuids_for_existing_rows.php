<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        DB::table('products')->whereNull('uuid')->get()->each(function ($row) {
            DB::table('products')->where('id', $row->id)->update([
                'uuid' => Str::uuid()->toString(),
            ]);
        });

        DB::table('holds')->whereNull('uuid')->get()->each(function ($row) {
            DB::table('holds')->where('id', $row->id)->update([
                'uuid' => Str::uuid()->toString(),
            ]);
        });

        DB::table('orders')->whereNull('uuid')->get()->each(function ($row) {
            DB::table('orders')->where('id', $row->id)->update([
                'uuid' => Str::uuid()->toString(),
            ]);
        });

        DB::table('payment_webhooks')->whereNull('uuid')->get()->each(function ($row) {
            DB::table('payment_webhooks')->where('id', $row->id)->update([
                'uuid' => Str::uuid()->toString(),
            ]);
        });
    }


    public function down(): void
    {
    }
};
