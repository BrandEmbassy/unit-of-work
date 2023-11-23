<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class OperationConsolidationMode
{
    public function __construct(
        private readonly bool $isLoggingEnabled,
        private readonly bool $isDryRunUnlimitedConsolidation,
        private readonly bool $isUnlimitedConsolidation
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
