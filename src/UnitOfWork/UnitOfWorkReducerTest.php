<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class UnitOfWorkReducerTest extends TestCase
{
    /**
     * @dataProvider operationsToReduceProvider
     *
     * @param Operation[] $expectedOperationsAfterReduction
     * @param Operation[] $operationsToReduce
     */
    public function testShouldReduceOperationsFromBeginning(
        array $expectedOperationsAfterReduction,
        array $operationsToReduce,
        Operation $operationToReduceBy
    ): void {
        $reducer = new UnitOfWorkReducer();
        $unitOfWorkToReduce = UnitOfWork::fromOperations($operationsToReduce);

        $unitOfWorkAfterReduction = $reducer->reduceFromBeginning($unitOfWorkToReduce, $operationToReduceBy);
        $actualOperationsAfterReduction = $unitOfWorkAfterReduction->getOperations();

        $expectedOperationsCount = count($expectedOperationsAfterReduction);
        Assert::assertCount($expectedOperationsCount, $actualOperationsAfterReduction);

        for ($i = 0; $i < $expectedOperationsCount; $i++) {
            Assert::assertSame($expectedOperationsAfterReduction[$i], $actualOperationsAfterReduction[$i]);
        }
    }


    /**
     * @return Operation[][]|Operation[][][]
     */
    public static function operationsToReduceProvider(): array
    {
        $mergeableOperation1 = new DefaultMergeableOperation(1);
        $mergeableOperation2 = new DefaultMergeableOperation(2);

        $notMergeableOperation1 = new NotMergeableOperation();
        $notMergeableOperation2 = new NotMergeableOperation();

        return [
            [
                [$notMergeableOperation1, $notMergeableOperation2],
                [$mergeableOperation1, $notMergeableOperation1, $notMergeableOperation2],
                new DefaultMergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2],
                [$mergeableOperation1, $mergeableOperation2, $notMergeableOperation1, $notMergeableOperation2],
                new DefaultMergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2],
                [$notMergeableOperation1, $notMergeableOperation2],
                new DefaultMergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                new DefaultMergeableOperation(3),
            ],
            [
                [],
                [$mergeableOperation1],
                new DefaultMergeableOperation(3),
            ],
            [
                [],
                [$mergeableOperation1, $mergeableOperation2],
                new DefaultMergeableOperation(3),
            ],
            [
                [],
                [],
                new DefaultMergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                new NotMergeableOperation(),
            ],
        ];
    }
}
