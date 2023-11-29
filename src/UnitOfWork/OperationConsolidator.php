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

        $initialOperationsState = [];
        foreach ($operations as $key => $item) {
            $initialOperationsState[] = sprintf('(%s) %s', $key, basename(str_replace('\\', '/', $item::class)));
        }
        $logMessage = sprintf('UoW Operations [%s] got merged into ', implode(', ', $initialOperationsState));

        $mergedOperationsLogList = [];

        $consolidatedOperations = [];
        $operations = array_reverse($operations, true);

        foreach ($operations as $operationIndex => $operation) {
            if (!$operation instanceof MergeableOperation) {
                $consolidatedOperations[] = $operation;
                $mergedOperationsLogList[$operationIndex] = [];

                continue;
            }

            $updatedConsolidatedOperations = [];
            $hasBeenMerged = false;

            foreach ($consolidatedOperations as $consolidatedOperationIndex => $consolidatedOperation) {
                if ($consolidatedOperation instanceof MergeableOperation
                    && $operation->canBeMergedWith($consolidatedOperation)
                ) {
                    $updatedConsolidatedOperations[] = $operation->mergeWith($consolidatedOperation);
                    $mergedOperationsLogList[$consolidatedOperationIndex][] = $operationIndex;
                    $hasBeenMerged = true;

                    continue;
                }

                $updatedConsolidatedOperations[] = $consolidatedOperation;
                $mergedOperationsLogList[$consolidatedOperationIndex] = [];
            }

            if (!$hasBeenMerged) {
                $updatedConsolidatedOperations[] = $operation;
                $mergedOperationsLogList[$operationIndex] = [];
            }

            $consolidatedOperations = $updatedConsolidatedOperations;
        }

        $consolidatedOperations = array_reverse($consolidatedOperations);

        $mergedOperationsLogMessage = [];
        ksort($mergedOperationsLogList);
        foreach ($mergedOperationsLogList as $initialOperationIndex => $mergedOperationIndexList) {
            $baseOperationClassName = basename(str_replace('\\', '/', $operations[$initialOperationIndex]::class));
            $mergedOperations = [$initialOperationIndex];
            foreach ($mergedOperationIndexList as $mergedOperationIndex) {
                $mergedOperations[] = $mergedOperationIndex;
            }
            $mergedOperationsLogMessage[] = sprintf('(%s) %s', implode(', ', array_reverse($mergedOperations)), $baseOperationClassName);
        }
        $logMessage .= sprintf('[%s]', implode(', ', $mergedOperationsLogMessage));

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
