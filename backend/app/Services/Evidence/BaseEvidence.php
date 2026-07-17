<?php

namespace App\Services\Evidence;

use App\DTOs\Evidence\UploadedEvidence;
use App\Jobs\CleanupUnusedEvidenceJob;
use App\Exceptions\Evidence\EvidenceDomainException;
use DateInterval;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class BaseEvidence
{
    /**
     * Storage disk.
     */
    protected function disk(): string
    {
        return 'public';
    }

    /**
     * Relative directory path from disk root.
     *
     * Example: assessment
     */

    abstract protected function baseDirPath(): string;

    /**
     * Delay before unused evidence cleanup.
     */
    abstract protected function cleanupDelay(): DateInterval|int;

    /**
     * Evidence cleanup action that will be called in job.
     */
    abstract public function cleanup(string $path): void;

    /**
     * Upload directory relative path from disk root.
     *
     * Example: assessment/2026/Q1
     */
    protected function uploadDirPath(): string
    {
        return $this->baseDirPath();
    }

    /**
     * Upload evidence file.
     */
    public function upload(UploadedFile $file): UploadedEvidence
    {
        $path = $this->storage()->putFileAs(
            trim($this->uploadDirPath(), '/'),
            $file,
            $this->basenameOf($file),
        );

        CleanupUnusedEvidenceJob::dispatch(
            static::class,
            $path,
        )->delay($this->cleanupDelay());

        return new UploadedEvidence(
            path: $path,
            url: $this->urlOf($path),
        );
    }

    /**
     * Delete evidence file.
     */
    public function delete(string $path): void
    {
        $path = ltrim($path, '/');
        $domain = trim($this->baseDirPath(), '/').'/';
        if ($path !== $domain && !str_starts_with($path, $domain)) {
            throw new EvidenceDomainException("evidence path:'$path' is not service's domain");
        }

        $this->storage()->delete($path);
    }

    /**
     * Get public URL.
     */
    public function urlOf(string $path): string
    {
        return $this->storage()->url($path);
    }

    /**
     * Generate evidence basename with extension.
     *
     * Example:
     * 2026_07_17_01K0V5B8K8MY7N0TQ8PKV8TQJM.pdf
     */
    protected function basenameOf(UploadedFile $file): string
    {
        return sprintf(
            '%s_%s.%s',
            now()->format('Y_m_d'),
            Str::ulid(),
            $file->extension(),
        );
    }

    /**
     * Filesystem adapter.
     */
    protected function storage(): FilesystemAdapter
    {
        return Storage::disk($this->disk());
    }
}