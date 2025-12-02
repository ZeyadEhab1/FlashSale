<?php

namespace App\Jobs;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\note;

class ExpireHoldsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        $now = now();
        $expiredHolds = Hold::where('status', 'active')
            ->where('expires_at', '<=', $now)
            ->get();
        foreach ($expiredHolds as $hold){
            DB::transaction(function () use ($hold) {
                $fresh = Hold::where('id', $hold->id)->lockForUpdate()->first();
                if ($fresh && $fresh->status === 'active' && $fresh->expires_at->isPast()) {
                    $fresh->status = HoldStatusEnum::EXPIRED;
                    $fresh->save();
                }
            });
        }
    }
}
