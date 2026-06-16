<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class CommunicationMessage extends Model
{
    protected static string $table       = 'communication_messages';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
    protected static bool   $timestamps  = false;
}
