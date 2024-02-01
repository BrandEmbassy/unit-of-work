<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function array_pop;
use function array_reverse;
use function array_values;

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
    public function consolidate(array $operations): array
    {
        if ($operations === []) {
            return [];
        }

        /** @var Operation[] $operations */
        $operations = array_values(array_reverse($operations));
        /** @var Operation[] $merged */
        $merged = [array_pop($operations)];

        while ($operations !== []) {
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
