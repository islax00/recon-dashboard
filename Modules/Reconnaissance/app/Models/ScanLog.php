<?php

namespace Modules\Reconnaissance\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['scan_id', 'tool', 'level', 'message'])]
class ScanLog extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
