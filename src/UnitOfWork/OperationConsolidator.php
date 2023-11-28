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

        $consolidatedOperations = [array_pop($operations)];

        if ($operations === []) {
            return $consolidatedOperations;
        }

        $operations = array_reverse($operations);

        foreach ($operations as $operation) {
            if (!($operation instanceof MergeableOperation)) {
                $consolidatedOperations[] = $operation;

                continue;
            }

            $updatedConsolidatedOperations = [];
            $hasBeenMerged = false;

            foreach ($consolidatedOperations as $consolidatedOperation) {
                if ($consolidatedOperation instanceof MergeableOperation && $consolidatedOperation->canBeMergedWith($operation)) {
                    $updatedConsolidatedOperations[] = $operation->mergeWith($consolidatedOperation);
                    $hasBeenMerged = true;

                    continue;
                }

                $updatedConsolidatedOperations[] = $consolidatedOperation;
            }

            if (!$hasBeenMerged) {
                $updatedConsolidatedOperations[] = $operation;
            }

            $consolidatedOperations = $updatedConsolidatedOperations;
        }

        return array_reverse($consolidatedOperations);
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
