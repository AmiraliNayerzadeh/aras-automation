<?php

namespace App\Enums;

enum ApprovalStepRole: string
{
    case Manager = 'manager';
    case Hr = 'hr';
    case Ceo = 'ceo';
}
