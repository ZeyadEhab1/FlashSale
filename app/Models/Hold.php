<?php

namespace App\Models;

use App\Enums\HoldStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Hold extends Model
{
    protected $fillable = [
        'product_id',
        'qty',
        'status',
        'expires_at',
        'uuid',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'status' => HoldStatusEnum::class,
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
