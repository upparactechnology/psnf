<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Attendance extends Model
{
    protected static string $table       = 'attendance';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
