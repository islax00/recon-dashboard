<?php

namespace Modules\Crawler\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reconnaissance\Models\Scan;
use Modules\Subdomain\Models\Subdomain;

#[Fillable(['scan_id', 'subdomain_id', 'url', 'method', 'status_code', 'content_type', 'content_length', 'parameters'])]
class Endpoint extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parameters' => 'array',
        ];
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function subdomain(): BelongsTo
    {
        return $this->belongsTo(Subdomain::class);
    }
}
