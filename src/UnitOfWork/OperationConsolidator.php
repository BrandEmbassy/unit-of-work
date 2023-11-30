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
    public function __construct(
        private readonly OperationConsolidatorResultLogger $operationConsolidatorResultLogger
    ) {
    }


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
            $isLoggingEnabled = $operationConsolidationMode->isLoggingEnabled();
            if ($operationConsolidationMode->isUnlimitedConsolidation()) {
                return $this->consolidateNew($operations, $isLoggingEnabled);
            }

            $this->consolidateNew($operations, $isLoggingEnabled);
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
    private function consolidateNew(array $operations, bool $isLoggingEnabled): array
    {
        if ($operations === []) {
            return [];
        }

        $operationsCount = count($operations);

        if ($operationsCount === 1) {
            return $operations;
        }

        $operationConsolidationResult = $this->getConsolidatedOperations($operations, $operationsCount);

        if ($isLoggingEnabled) {
            $this->operationConsolidatorResultLogger->log($operationConsolidationResult);
        }

        return $operationConsolidationResult->getConsolidatedOperations();
    }


    /**
     * @param Operation[] $operations
     */
    private function getConsolidatedOperations(array $operations, int $operationsCount): OperationConsolidationResult
    {
        $initialOperations = $operations;
        $consolidatedOperationsState = [];

        $consolidatedOperations = [];

        for ($currentOperationIndex = 0; $currentOperationIndex < $operationsCount; $currentOperationIndex++) {
            $currentOperation = $operations[$currentOperationIndex];

            if ($currentOperation === null) {
                continue;
            }

            if (!$currentOperation instanceof MergeableOperation) {
                $consolidatedOperations[$currentOperationIndex] = $currentOperation;
                $consolidatedOperationsState[$currentOperationIndex] = [$currentOperationIndex];
                continue;
            }

            $mergedOperation = $currentOperation;
            $lastMergedOperationIndex = $currentOperationIndex;

            $consolidatedOperationsStateItem = [$currentOperationIndex];

            for ($nextOperationIndex = $currentOperationIndex + 1; $nextOperationIndex < $operationsCount; $nextOperationIndex++) {
                $nextOperation = $operations[$nextOperationIndex];
                if (!$nextOperation instanceof MergeableOperation) {
                    continue;
                }

                if ($currentOperation->canBeMergedWith($nextOperation)) {
                    $mergedOperation = $mergedOperation->mergeWith($nextOperation);
                    $operations[$nextOperationIndex] = null;
                    $lastMergedOperationIndex = $nextOperationIndex;
                    $consolidatedOperationsStateItem[] = $nextOperationIndex;
                }
            }

            $consolidatedOperationsState[$lastMergedOperationIndex] = $consolidatedOperationsStateItem;
            $consolidatedOperations[$lastMergedOperationIndex] = $mergedOperation;
        }

        ksort($consolidatedOperations);
        ksort($consolidatedOperationsState);
        $consolidatedOperations = array_values($consolidatedOperations);

        return new OperationConsolidationResult(
            $initialOperations,
            $consolidatedOperations,
            $consolidatedOperationsState,
        );
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
