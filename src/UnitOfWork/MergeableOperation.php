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


    public function canBeMergedFrom(Operation $operation): bool
    {
        return $operation instanceof self;
    }


    public function mergeFrom(Operation $operation): Operation
    {
        assert($operation instanceof self);

        return new self($this->number + $operation->number);
    }


    public function canBeMergedTo(Operation $operation): bool
    {
        return $operation instanceof self;
    }


    public function mergeTo(Operation $operation): Operation
    {
        assert($operation instanceof self);

        return new self($this->number + $operation->number);
    }


    public function isChainBreakFor(Operation $operation): bool
    {
        return true;
    }
}
