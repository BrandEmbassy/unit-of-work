<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface UnitOfWorkExecutor
{
    /**
     * @throws UnableToProcessOperationException
     */
    public function execute(
        UnitOfWork $unitOfWork,
        OperationConsolidationMode $operationConsolidationMode
    ): void;
}
