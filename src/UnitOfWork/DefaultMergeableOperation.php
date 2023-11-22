<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class DefaultMergeableOperation implements MergeableOperation
{
    public int $number;


    public function __construct(int $number)
    {
        $this->number = $number;
    }


    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return $nextOperation instanceof self;
    }


    public function mergeWith(Operation $nextOperation): MergeableOperation
    {
        assert($nextOperation instanceof self);

        return new self($this->number + $nextOperation->number);
    }
}
