<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class OperationsByPrioritySorterTest extends TestCase
{
    public function testOperationsAreSortedByPriority(): void
    {
        $operation1 = new NotMergeableOperationHavingPriority(200);
        $operation2 = new NotMergeableOperation();
        $operation3 = new NotMergeableOperationHavingPriority(550);
        $operation4 = new NotMergeableOperation();
        $operation5 = new NotMergeableOperationHavingPriority(450);
        $operationsToSort = [
            $operation1,
            $operation2,
            $operation3,
            $operation4,
            $operation5,
        ];

        $sorter = new OperationsByPrioritySorter();

        $sortedOperations = $sorter->sort($operationsToSort);

        $expectedOperations = [
            $operation1,
            $operation5,
            $operation2,
            $operation4,
            $operation3,
        ];
        Assert::assertSame($expectedOperations, $sortedOperations);
    }
}
