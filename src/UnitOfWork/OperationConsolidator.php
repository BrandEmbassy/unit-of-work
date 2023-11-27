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
                return $this->consolidateTheNewWay($operations);
            }

            // So that the merging process is just logged without any changes to actual data returned from Consolidator.
            $this->consolidateTheNewWay($operations);
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
    private function consolidateTheNewWay(array $operations): array
    {
        if ($operations === []) {
            return [];
        }

        $operationsCount = count($operations);
        $consolidatedOperations = [];

        foreach ($operations as $index => $operation) {
            if ($operation === null) {
                continue;
            }

            $mergeOperationIndex = $index;
            if (!$operation instanceof MergeableOperation) {
                $consolidatedOperations[$mergeOperationIndex] = $operation;
                continue;
            }

            $mergedOperation = $operation;

            for ($i = $index + 1; $i < $operationsCount; $i++) {
                $nextOperation = $operations[$i];
                if (!$nextOperation instanceof MergeableOperation) {
                    continue;
                }

                if ($mergedOperation->canBeMergedWith($nextOperation)) {
                    $mergedOperation = $mergedOperation->mergeWith($nextOperation);
                    $operations[$index] = null;
                    $operations[$i] = null;
                    $mergeOperationIndex = $i;
                }
            }

            $consolidatedOperations[$mergeOperationIndex] = $mergedOperation;
        }

        ksort($consolidatedOperations);

        return array_values($consolidatedOperations);
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
