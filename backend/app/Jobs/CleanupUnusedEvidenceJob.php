<?php

namespace App\Jobs;

use App\Services\Evidence\BaseEvidence;
use LogicException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CleanupUnusedEvidenceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $service,
        public readonly string $path,
    ) {}

    public function backoff(): int
    {
        return 10;
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(static::class))->releaseAfter($this->backoff()),
        ];
    }

    public function handle(): void
    {
        $service = app($this->service);

        if (! $service instanceof BaseEvidence) {
            throw new LogicException(sprintf(
                'Service [%s] must extend %s.',
                $this->service,
                BaseEvidence::class,
            ));
        }

        $service->cleanup($this->path);
    }
}
