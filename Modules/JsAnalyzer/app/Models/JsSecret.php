<?php

namespace Modules\JsAnalyzer\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Reconnaissance\Models\Scan;

#[Fillable(['scan_id', 'js_file_id', 'type', 'value', 'severity', 'line_number', 'confidence'])]
class JsSecret extends Model
{
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function jsFile(): BelongsTo
    {
        return $this->belongsTo(JsFile::class);
    }
}
