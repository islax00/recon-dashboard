<?php

namespace Modules\Fingerprint\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reconnaissance\Models\Scan;
use Modules\Subdomain\Models\Subdomain;

#[Fillable(['scan_id', 'subdomain_id', 'name', 'version', 'category'])]
class Technology extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function subdomain(): BelongsTo
    {
        return $this->belongsTo(Subdomain::class);
    }
}
