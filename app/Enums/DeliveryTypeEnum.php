<?php

namespace App\Enums;

enum DeliveryTypeEnum: string
{
    case OWN_ADDRESS = 'own_address';
    case COMPANY = 'company';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
