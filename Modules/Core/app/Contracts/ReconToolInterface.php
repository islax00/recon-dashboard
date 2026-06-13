<?php

namespace Modules\Core\Contracts;

use Modules\Core\DTOs\ReconResultDto;
use Modules\Core\DTOs\ScanDto;

interface ReconToolInterface
{
    public function name(): string;

    public function run(ScanDto $scan): ReconResultDto;
}
