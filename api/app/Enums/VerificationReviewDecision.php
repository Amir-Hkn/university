<?php

namespace App\Enums;

enum VerificationReviewDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
