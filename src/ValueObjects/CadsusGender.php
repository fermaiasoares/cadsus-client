<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\ValueObjects;

enum CadsusGender: string
{
    case MALE = 'M';
    case FEMALE = 'F';
    case UNDETERMINED = 'N';
}
