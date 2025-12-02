<?php

namespace App\Enums;

enum HoldStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case USED = 'used';
    case CANCELLED = 'cancelled';
}

