<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function assert;

/**
 * @final
 */
class DefaultMergeableOperation implements MergeableOperation
{
    public function __construct(
        public readonly string $text
    ) {
    }


    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return $nextOperation instanceof self;
    }


    public function mergeWith(Operation $nextOperation): MergeableOperation
    {
        assert($nextOperation instanceof self);

        return new self($this->text . '+' . $nextOperation->text);
    }
}
