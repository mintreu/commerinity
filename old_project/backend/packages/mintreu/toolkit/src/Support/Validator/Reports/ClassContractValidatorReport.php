<?php

namespace Mintreu\Toolkit\Support\Validator\Reports;

class ClassContractValidatorReport
{

    public function __construct(
        public bool $valid,
        public string $className,
        public string $interfaceName,
        public array $errors
    ) {}

    public function hasErrors(): bool
    {
        return !$this->valid;
    }

    public function getErrorMessages(): array
    {
        return $this->errors;
    }

}
