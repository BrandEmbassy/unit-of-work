<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function array_values;
use function count;

/**
 * TODO: test this class
 *
 * @final
 */
class OperationConsolidator
{
    private LoggerInterface $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function consolidate(array $operations): array
    {
        if ('FT dry run enabled') {
            if ('FT enabled') {
                return $this->consolidateTheNewWay($operations);
            } else {
                // So that the merging process is just logged without any changes to actual data returned from Consolidator.
                $this->consolidateTheNewWay($operations);
            }
        }

        return $this->consolidateTheOldWay($operations);
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
            $mergeOperationIndex = $index;
            if (!$operation instanceof MergeableOperation) {
                $consolidatedOperations[$mergeOperationIndex] = $operation;
                continue;
            }

            $mergedOperation = $operation;

            for ($i = $index + 1; $i < $operationsCount; $i++) {
                $nextOperation = $operations[$i];
                if ($nextOperation->isChainBreakFor($mergedOperation)) {
                    $consolidatedOperations[$mergeOperationIndex] = $mergedOperation;
                    continue 2;
                }

                if (!$nextOperation instanceof MergeableOperation) {
                    continue;
                }

                if ($operation->canBeMergedWith($nextOperation)) {
                    $mergedOperation = $operation->mergeWith($nextOperation);
                    $mergeOperationIndex = $i;
                }
            }

            $consolidatedOperations[$mergeOperationIndex] = $mergedOperation;
        }

        ksort($consolidatedOperations);

        return array_values($consolidatedOperations);
    }


    /**
     * This is the original way of merging.
     *
     * @param Operation[] $operations
     *
     * @return Operation[]
     */
    private function consolidateTheOldWay(array $operations): array
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

            if ($previous->canBeMergedWith($current)) {
                $merged[] = $previous->mergeWith($current);
            } else {
                $merged[] = $previous;
                $merged[] = $current;
            }
        }

        return $merged;
    }
}
