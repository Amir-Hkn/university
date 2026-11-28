<?php

namespace App\Enums;

enum VerificationDocumentKind: string
{
    case NationalIdImage = 'national_id_image';
    case LatestDiplomaImage = 'latest_diploma_image';
}
