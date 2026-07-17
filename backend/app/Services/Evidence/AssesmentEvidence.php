<?php

namespace App\Services\Evidence;

use DateInterval;
use LogicException;
use Illuminate\Support\Facades\DB;

class AssessmentEvidence extends BaseEvidence
{
    private array $uploadCtx = [];

    protected function baseDirPath(): string
    {
        return 'assessments';
    }

    protected function cleanupDelay(): DateInterval|int
    {
        /** 6 hours */
        return 60 * 60 * 6;
    }

    public function cleanup(string $path): void
    {
        $sql = 'SELECT EXISTS (SELECT 1 FROM assessment_answers WHERE evidence = ?)';
        $isExists = (bool) DB::scalar($sql, [$path]);
        if (!$isExists && $this->storage()->exists($path)) {
            $this->delete($path);
        }
    }

    protected function uploadDirPath(): string
    {
        $year = $this->getUploadYear();
        $quarter = $this->getUploadQuarter();

        if ($year === null) throw new LogicException('year config required to build upload path');
        if ($quarter === null) throw new LogicException('quarter config required to build upload path');
        return sprintf("%s/$year/Q$quarter", $this->baseDirPath());
    }

    public function setUploadYear(int $year): static
    {
        $this->uploadCtx['year'] = $year;
        return $this;
    }

    public function getUploadYear(): ?int
    {
        return $this->uploadCtx['year'] ?? null;
    }

    public function setUploadQuarter(int $quarter): static
    {
        $this->uploadCtx['quarter'] = $quarter;
        return $this;
    }

    public function getUploadQuarter(): ?int
    {
        return $this->uploadCtx['quarter'] ?? null;
    }
}
