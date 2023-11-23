<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class OperationConsolidationMode
{
    public function __construct(
        private readonly bool $shouldLogUnitOfWorkOperationConsolidation,
        bool $shouldUseNewConsolidationWithDryRun,
        bool $shouldUseNewConsolidation
    ) {
    }
}
