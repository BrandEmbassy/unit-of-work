<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class ReducingUnitOfWorkExecutor implements UnitOfWorkExecutor
{
    /**
     * @var OperationConsolidator
     */
    private $consolidator;

    /**
     * @var UnitOfWorkExecutor
     */
    private $unitOfWorkExecutor;


    public function __construct(UnitOfWorkExecutor $unitOfWorkExecutor, OperationConsolidator $consolidator)
    {
        $this->consolidator = $consolidator;
        $this->unitOfWorkExecutor = $unitOfWorkExecutor;
    }


    public function execute(
        UnitOfWork $unitOfWork,
        ?bool $shouldLogUnitOfWorkOperationConsolidation = null,
        ?bool $shouldUseNewConsolidationWithDryRun = null,
        ?bool $shouldUseNewConsolidation = null
    ): void {
        $operations = $this->consolidator->consolidate(
            $unitOfWork->getOperations(),
            $shouldLogUnitOfWorkOperationConsolidation ?? false,
            $shouldUseNewConsolidationWithDryRun ?? false,
            $shouldUseNewConsolidation ?? false,
        );
        $reducedUnitOfWork = UnitOfWork::fromOperations($operations);
        $this->unitOfWorkExecutor->execute($reducedUnitOfWork);
    }
}
