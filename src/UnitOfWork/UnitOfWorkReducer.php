<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function count;

/**
 * @final
 */
class UnitOfWorkReducer
{
    public function reduceFromBeginning(UnitOfWork $unitOfWorkToReduce, Operation $operationToReduceBy): UnitOfWork
    {
        $operations = $unitOfWorkToReduce->getOperations();

        $operationsAfterReduction = [];
        $index = 0;

        while (count($operations) > $index && $operations[$index]->canBeMergedWith($operationToReduceBy)) {
            ++$index;
        }

        while (count($operations) > $index) {
            $operationsAfterReduction[] = $operations[$index];
            ++$index;
        }

        return UnitOfWork::fromOperations($operationsAfterReduction);
    }
}
