<?php

namespace App\Enums;

enum AttachmentKind: string
{
    case Attachment = 'attachment';
    case Receipt = 'receipt';
    case ReportFile = 'report_file';
}
