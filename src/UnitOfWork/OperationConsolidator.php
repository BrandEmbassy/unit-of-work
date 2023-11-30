<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function array_pop;
use function array_reverse;
use function array_values;
use function count;

/**
 * @final
 */
class OperationConsolidator
{
    /**
     * @param Operation[] $operations
     *
     * @return Operation[]
     */
    public function consolidate(
        array $operations,
        OperationConsolidationMode $operationConsolidationMode
    ): array {
        if ($operationConsolidationMode->isDryRunUnlimitedConsolidation()) {
            if ($operationConsolidationMode->isUnlimitedConsolidation()) {
                return $this->consolidateNew($operations);
            }

            $this->consolidateNew($operations);
        }

        return $this->consolidateOld($operations);
    }


    /**
     * This is the new way of merging.
     *
     * @param Operation[] $operations
     *
     * @return Operation[]
     */
    private function consolidateNew(array $operations): array
    {
        if ($operations === []) {
            return [];
        }

        $operationsCount = count($operations);

        if ($operationsCount === 1) {
            return $operations;
        }

        $consolidatedOperations = $this->getConsolidatedOperations($operations, $operationsCount);

        ksort($consolidatedOperations);

        return array_values($consolidatedOperations);
    }


    /**
     * @param Operation[] $operations
     *
     * @return Operation[]
     */
    private function getConsolidatedOperations(array $operations, int $operationsCount): array
    {
        $consolidatedOperations = [];

        for ($currentOperationIndex = 0; $currentOperationIndex < $operationsCount; $currentOperationIndex++) {
            $currentOperation = $operations[$currentOperationIndex];

            if ($currentOperation === null) {
                continue;
            }

            if (!$currentOperation instanceof MergeableOperation) {
                $consolidatedOperations[$currentOperationIndex] = $currentOperation;
                continue;
            }

            $mergedOperation = $currentOperation;
            $lastMergedOperationIndex = $currentOperationIndex;

            for ($nextOperationIndex = $currentOperationIndex + 1; $nextOperationIndex < $operationsCount; $nextOperationIndex++) {
                $nextOperation = $operations[$nextOperationIndex];
                if (!$nextOperation instanceof MergeableOperation) {
                    continue;
                }

                if ($currentOperation->canBeMergedWith($nextOperation)) {
                    $mergedOperation = $mergedOperation->mergeWith($nextOperation);
                    $operations[$nextOperationIndex] = null;
                    $lastMergedOperationIndex = $nextOperationIndex;
                }
            }

            $consolidatedOperations[$lastMergedOperationIndex] = $mergedOperation;
        }

        return $consolidatedOperations;
    }


    /**
     * @param Operation[] $operations
     *
     * @return Operation[]
     */
    private function consolidateOld(array $operations): array
    {
        if ($operations === []) {
            return [];
        }

        /** @var Operation[] $operations */
        $operations = array_values(array_reverse($operations));
        /** @var Operation[] $merged */
        $merged = [array_pop($operations)];

        while (count($operations) > 0) {
            /** @var Operation $previous */
            $previous = array_pop($merged);
            /** @var Operation $current */
            $current = array_pop($operations);

            if ($current instanceof MergeableOperation
                && $previous instanceof MergeableOperation
                && $previous->canBeMergedWith($current)
            ) {
                $merged[] = $previous->mergeWith($current);
            } else {
                $merged[] = $previous;
                $merged[] = $current;
            }
        }

        return $merged;
    }
}
