<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function array_pop;
use function array_reverse;
use function array_values;
use function count;
use function ksort;

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

        $mergeOperationIndex = 0;
        $consolidatedOperations = [];

        $this->merge($consolidatedOperations, $operations, $operationsCount, $mergeOperationIndex);

        ksort($consolidatedOperations);

        return array_values($consolidatedOperations);
    }


    /**
     * @param Operation[] $consolidatedOperations
     * @param array<int, Operation|null> $operations
     */
    private function merge(
        array &$consolidatedOperations,
        array &$operations,
        int $operationsCount,
        int $currentOperationIndex
    ): void {
        $nextOperationIndex = $currentOperationIndex + 1;

        if ($nextOperationIndex === $operationsCount + 1) {
            return;
        }

        $currentOperation = $operations[$currentOperationIndex];

        if ($currentOperation === null) {
            $this->merge($consolidatedOperations, $operations, $operationsCount, $nextOperationIndex);

            return;
        }

        if (!$currentOperation instanceof MergeableOperation) {
            $operations[$currentOperationIndex] = null;
            $consolidatedOperations[$currentOperationIndex] = $currentOperation;

            $this->merge($consolidatedOperations, $operations, $operationsCount, $nextOperationIndex);

            return;
        }

        $mergedOperation = $currentOperation;
        $operations[$currentOperationIndex] = null;
        $lastMergedOperationIndex = $currentOperationIndex;

        for ($i = $nextOperationIndex; $i < $operationsCount; $i++) {
            $nextOperation = $operations[$i];
            if (!$nextOperation instanceof MergeableOperation) {
                continue;
            }

            if ($mergedOperation->canBeMergedWith($nextOperation)) {
                $mergedOperation = $mergedOperation->mergeWith($nextOperation);
                $operations[$i] = null;
                $lastMergedOperationIndex = $i;
            }
        }

        $consolidatedOperations[$lastMergedOperationIndex] = $mergedOperation;

        $this->merge($consolidatedOperations, $operations, $operationsCount, $nextOperationIndex);
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
