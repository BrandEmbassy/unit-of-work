<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use AssertionError;

/**
 * @final
 */
class AlienOperation implements Operation
{
    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return false;
    }


    public function mergeWith(Operation $nextOperation): Operation
    {
        throw new AssertionError();
    }
}
