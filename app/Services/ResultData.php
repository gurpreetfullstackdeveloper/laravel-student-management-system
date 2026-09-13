<?php

namespace App\Services;

final class ResultData
{
    public function __construct(
        public readonly float $total,
        public readonly float $maximum,
        public readonly float $percentage,
        public readonly string $grade,
        public readonly bool $passed,
    ) {
    }
}