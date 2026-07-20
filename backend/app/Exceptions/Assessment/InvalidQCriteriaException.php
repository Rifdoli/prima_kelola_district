<?php

namespace App\Exceptions\Assessment;

use Exception;

class InvalidQCriteriaException extends Exception
{
    protected ?int $criteriaId;

    public function setCriteriaId(?int $criteriaId): static
    {
        $this->criteriaId = $criteriaId;
        return $this;
    }

    public function getCriteriaId(): ?int
    {
        return $this->criteriaId;
    }
}