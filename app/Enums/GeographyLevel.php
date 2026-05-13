<?php

namespace App\Enums;

enum GeographyLevel: string
{
    case NATIONAL = 'national';
    case REGION = 'region';
    case DISTRICT = 'district';
    case SCHOOL = 'school';
    case NONE = 'none';
}
