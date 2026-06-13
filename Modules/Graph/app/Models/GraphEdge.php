<?php

namespace Modules\Graph\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reconnaissance\Models\Scan;

#[Fillable(['scan_id', 'source_node_id', 'target_node_id', 'relation'])]
class GraphEdge extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }
}
