<?php

namespace Modules\JsAnalyzer\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Crawler\Models\Endpoint;
use Modules\Reconnaissance\Models\Scan;

#[Fillable(['scan_id', 'endpoint_id', 'url', 'size', 'is_analyzed'])]
class JsFile extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(Endpoint::class);
    }

    public function secrets(): HasMany
    {
        return $this->hasMany(JsSecret::class);
    }
}
