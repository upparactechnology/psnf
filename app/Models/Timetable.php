<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Timetable extends Model
{
    protected static string $table       = 'timetables';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
