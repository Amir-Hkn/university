<?php

namespace App\Enums;

enum ProviderMatchOutcome: string
{
    case Matched = 'matched';
    case Mismatched = 'mismatched';
    case Unavailable = 'unavailable';
    case Error = 'error';
}
