<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use LogicException;

final class NotMergeableOperation implements Operation
{
    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return false;
    }


    public function mergeWith(Operation $nextOperation): Operation
    {
        throw new LogicException('Not mergeable!');
    }
}
