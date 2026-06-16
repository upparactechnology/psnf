<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class FeePayment extends Model
{
    protected static string $table       = 'fee_payments';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
    protected static bool   $timestamps  = false;
}
