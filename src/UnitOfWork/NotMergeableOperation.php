<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use LogicException;

/**
 * @final
 */
class NotMergeableOperation implements Operation
{
    public function canBeMergedFrom(Operation $operation): bool
    {
        return false;
    }


    public function mergeFrom(Operation $operation): Operation
    {
        throw new LogicException('Not mergeable!');
    }


    public function canBeMergedTo(Operation $operation): bool
    {
        return false;
    }


    public function mergeTo(Operation $operation): Operation
    {
        throw new LogicException('Not mergeable!');
    }


    public function isChainBreakFor(Operation $operation): bool
    {
        return true;
    }
}
