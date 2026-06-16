<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Certificate extends Model
{
    protected static string $table       = 'certificates';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
