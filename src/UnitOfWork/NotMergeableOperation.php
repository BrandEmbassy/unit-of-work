<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class NotMergeableOperation implements Operation
{
    public function isChainBreakFor(Operation $operation): bool
    {
        return false;
    }
}
