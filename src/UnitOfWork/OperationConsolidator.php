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
     * TODO
     * hide logging behind FT?
     * discuss with infra regarding load
     */
//    public function __construct(LoggerInterface)
//    {
//    }


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
            if (!$operation->isMergeable()) {
                $consolidatedOperations[] = $operation;
                continue;
            }

            $mergedOperation = $operation;

            for ($i = $index + 1; $i < $operationsCount; $i++) {
                if ($operations[$i]->isChainBreakFor($mergedOperation)) {
                    $consolidatedOperations[] = $mergedOperation;
                    continue 2;
                }

                if ($operation->canBeMergedWith($operations[$i], new NullLogger())) {
                    $mergedOperation = $operation->mergeWith($operations[$i], new NullLogger());
                }
            }

            $consolidatedOperations[] = $mergedOperation;
        }



        return $consolidatedOperations;
    }
}
