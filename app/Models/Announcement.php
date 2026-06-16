<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Announcement extends Model
{
    protected static string $table       = 'announcements';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
