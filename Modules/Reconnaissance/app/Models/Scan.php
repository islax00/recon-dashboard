<?php

namespace Modules\Reconnaissance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Core\DTOs\ScanDto;
use Modules\Core\Enums\ScanStatus;
use Modules\Reconnaissance\Database\Factories\ScanFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property string $domain
 * @property ScanStatus $status
 * @property array<string, mixed>|null $options
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 */
#[Fillable(['user_id', 'domain', 'status', 'options', 'started_at', 'completed_at'])]
class Scan extends Model
{
    /** @use HasFactory<ScanFactory> */
    use HasFactory;

    protected static function newFactory(): ScanFactory
    {
        return ScanFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ScanStatus::class,
            'options' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function toDto(): ScanDto
    {
        return ScanDto::fromArray([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'domain' => $this->domain,
            'status' => $this->status,
            'options' => $this->options,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
        ]);
    }
}
