<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class FeeInvoice extends Model
{
    protected static string $table       = 'fee_invoices';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
