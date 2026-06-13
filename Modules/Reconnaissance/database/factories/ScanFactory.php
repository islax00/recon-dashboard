<?php

namespace Modules\Reconnaissance\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Enums\ScanStatus;
use Modules\Reconnaissance\Models\Scan;

/**
 * @extends Factory<Scan>
 */
class ScanFactory extends Factory
{
    protected $model = Scan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'domain' => fake()->domainName(),
            'status' => ScanStatus::Pending,
            'options' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
