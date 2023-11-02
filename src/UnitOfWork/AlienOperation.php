<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use AssertionError;

/**
 * @final
 */
class AlienOperation implements Operation
{
    public function canBeMergedFrom(Operation $operation): bool
    {
        return false;
    }


    public function mergeFrom(Operation $operation): Operation
    {
        throw new AssertionError();
    }


    public function canBeMergedTo(Operation $operation): bool
    {
        return false;
    }


    public function mergeTo(Operation $operation): Operation
    {
        throw new AssertionError();
    }


    public function isChainBreakFor(Operation $operation): bool
    {
        return true;
    }
}
