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
    public function __construct(FeatureStateResolver $featureStateResolver, LoggerInterface $logger)
    {
    }


    /**
     * @param Operation[] $operations
     *
     * @return Operation[]
     */
    public function consolidate(array $operations): array
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
}
