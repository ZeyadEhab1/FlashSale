<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentWebhook extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_reference',
        'order_id',
        'payload',
        'processed',
        'processed_at',
        'uuid',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed'    => 'boolean',
        'processed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string)Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


    public function isAlreadyProcessed(): bool
    {
        return $this->processed === true;
    }
}
