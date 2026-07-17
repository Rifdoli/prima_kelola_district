<?php

namespace App\DTOs\Evidence;

final readonly class UploadedEvidence
{
    public function __construct(
        public string $path,
        public string $url,
    ) {}
}
