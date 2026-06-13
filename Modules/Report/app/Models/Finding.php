<?php

namespace Modules\Report\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Reconnaissance\Models\Scan;

#[Fillable(['report_id', 'scan_id', 'title', 'description', 'severity', 'type', 'findable_type', 'findable_id'])]
class Finding extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function findable(): MorphTo
    {
        return $this->morphTo();
    }
}
