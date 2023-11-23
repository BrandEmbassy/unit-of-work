<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class OperationConsolidationMode
{
    public function __construct(
        private readonly bool $isLoggingEnabled = false,
        private readonly bool $isDryRunUnlimitedConsolidation = false,
        private readonly bool $isUnlimitedConsolidation = false
    ) {
    }


    public function isLoggingEnabled(): bool
    {
        return $this->isLoggingEnabled;
    }


    public function isDryRunUnlimitedConsolidation(): bool
    {
        return $this->isDryRunUnlimitedConsolidation;
    }


    public function isUnlimitedConsolidation(): bool
    {
        return $this->isUnlimitedConsolidation;
    }
}
