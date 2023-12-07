<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class TestOnlyChainBreakingOperation implements Operation
{
    public function isChainBreakFor(Operation $operation): bool
    {
        return $operation instanceof TestOnlyMergeableOperation;
    }
}
