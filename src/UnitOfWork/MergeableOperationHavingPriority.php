<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

final class MergeableOperationHavingPriority implements Operation, HavingPriority
{
    private int $priority;

    public int $number;


    public function __construct(int $priority, int $number)
    {
        $this->priority = $priority;
        $this->number = $number;
    }


    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return $nextOperation instanceof self;
    }


    public function mergeWith(Operation $nextOperation): Operation
    {
        assert($nextOperation instanceof self);

        return new self($this->priority, $this->number + $nextOperation->number);
    }


    public function getPriority(): int
    {
        return $this->priority;
    }
}
