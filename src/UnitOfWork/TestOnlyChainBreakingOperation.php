<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class TestOnlyChainBreakingOperation extends AbstractOperation
{
    public function isChainBreakFor(Operation $operation): bool
    {
        return $operation instanceof TestOnlyMergeableOperation;
    }
}
