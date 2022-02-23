<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function assert;

/**
 * @final
 */
class MergeableOperation implements Operation
{
    /**
     * @var int
     */
    public $number;


    public function __construct(int $number)
    {
        $this->number = $number;
    }


    public function canBeMergedWith(Operation $nextOperation): bool
    {
        return $nextOperation instanceof self;
    }


    public function mergeWith(Operation $nextOperation): Operation
    {
        assert($nextOperation instanceof self);

        return new self($this->number + $nextOperation->number);
    }
}
