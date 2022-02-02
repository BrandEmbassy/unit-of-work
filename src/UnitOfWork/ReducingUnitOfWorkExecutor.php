<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

final class ReducingUnitOfWorkExecutor implements UnitOfWorkExecutor
{
    private OperationConsolidator $consolidator;

    private UnitOfWorkExecutor $unitOfWorkExecutor;

    private OperationsByPrioritySorter $operationsPrioritySorter;


    public function __construct(
        UnitOfWorkExecutor $unitOfWorkExecutor,
        OperationConsolidator $consolidator,
        OperationsByPrioritySorter $operationsPrioritySorter
    ) {
        $this->consolidator = $consolidator;
        $this->unitOfWorkExecutor = $unitOfWorkExecutor;
        $this->operationsPrioritySorter = $operationsPrioritySorter;
    }


    public function execute(UnitOfWork $unitOfWork): void
    {
        $originalOperations = $unitOfWork->getOperations();
        $sortedOperations = $this->operationsPrioritySorter->sort($originalOperations);
        $consolidatedOperations = $this->consolidator->consolidate($sortedOperations);
        $reducedUnitOfWork = UnitOfWork::fromOperations($consolidatedOperations);
        $this->unitOfWorkExecutor->execute($reducedUnitOfWork);
    }
}
