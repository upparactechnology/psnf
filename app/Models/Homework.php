<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Homework extends Model
{
    protected static string $table       = 'homeworks';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
