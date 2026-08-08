<?php

namespace App\Enums;

enum OrderType: string
{
    case Internal = 'internal';
    case External = 'external';
}
