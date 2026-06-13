<?php

namespace Modules\Subdomain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reconnaissance\Models\Scan;

#[Fillable(['scan_id', 'hostname', 'ip_address', 'status_code', 'title', 'is_alive'])]
class Subdomain extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
