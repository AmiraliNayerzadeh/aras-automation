<?php

namespace App\Enums;

enum EmploymentType: string
{
    case Official = 'official';
    case Contract = 'contract';
    case Probation = 'probation';
    case PartTime = 'part_time';
}
