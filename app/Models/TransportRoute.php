<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class TransportRoute extends Model
{
    protected static string $table       = 'transport_routes';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
