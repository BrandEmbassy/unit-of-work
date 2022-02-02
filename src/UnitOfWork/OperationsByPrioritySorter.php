<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function usort;

final class OperationsByPrioritySorter
{
    /**
     * @param array<Operation> $operations
     *
     * @return array<Operation>
     */
    public function sort(array $operations): array
    {
        usort(
            $operations,
            static function (Operation $operation1, Operation $operation2): int {
                $priority1 = $operation1 instanceof HavingPriority
                    ? $operation1->getPriority()
                    : HavingPriority::DEFAULT_PRIORITY;
                $priority2 = $operation2 instanceof HavingPriority
                    ? $operation2->getPriority()
                    : HavingPriority::DEFAULT_PRIORITY;

                return $priority1 <=> $priority2;
            }
        );

        return $operations;
    }
}
