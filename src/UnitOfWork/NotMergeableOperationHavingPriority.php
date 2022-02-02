<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use LogicException;

final class NotMergeableOperationHavingPriority implements Operation, HavingPriority
{
    private int $priority;


    public function __construct(int $priority)
    {
        $this->priority = $priority;
    }


    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return false;
    }


    public function mergeWith(Operation $nextOperation): Operation
    {
        throw new LogicException('Not mergeable!');
    }


    public function getPriority(): int
    {
        return $this->priority;
    }
}
