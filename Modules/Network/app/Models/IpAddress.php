<?php

namespace Modules\Network\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Reconnaissance\Models\Scan;
use Modules\Subdomain\Models\Subdomain;

#[Fillable(['scan_id', 'subdomain_id', 'ip', 'asn', 'org', 'country', 'city'])]
class IpAddress extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function subdomain(): BelongsTo
    {
        return $this->belongsTo(Subdomain::class);
    }

    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }
}
