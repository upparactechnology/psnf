<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class ExamResult extends Model
{
    protected static string $table       = 'exam_results';
    protected static bool   $tenantScope = true;
    protected static bool   $softDelete  = false;
}
