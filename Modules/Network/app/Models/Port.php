<?php

namespace Modules\Network\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ip_address_id', 'port', 'protocol', 'service', 'banner', 'is_open'])]
class Port extends Model
{
    public function ipAddress(): BelongsTo
    {
        return $this->belongsTo(IpAddress::class);
    }
}
