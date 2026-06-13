<?php

namespace Modules\Core\Contracts;

interface ReconParserInterface
{
    public function tool(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $output): array;
}
