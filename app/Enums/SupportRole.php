<?php

namespace App\Enums;

/**
 * Describes the capacity in which a supervisor supports a teacher.
 */
enum SupportRole: string
{
    case Coach = 'coach';
    case Inspector = 'inspector';
    case Mentor = 'mentor';
}
