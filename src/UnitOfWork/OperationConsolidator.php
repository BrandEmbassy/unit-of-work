<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function array_pop;
use function array_reverse;
use function array_values;
use function assert;
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
        assert(!$operationConsolidationMode->isLoggingEnabled());
        assert(!$operationConsolidationMode->isDryRunUnlimitedConsolidation());
        assert(!$operationConsolidationMode->isUnlimitedConsolidation());

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
