<?php

namespace Modules\Report\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Reconnaissance\Models\Scan;

#[Fillable([
    'scan_id',
    'risk_score',
    'risk_level',
    'subdomains_count',
    'endpoints_count',
    'secrets_count',
    'vulnerabilities_count',
    'ai_summary',
])]
class Report extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }
}
