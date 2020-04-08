<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\TestCase;
use function count;

final class UnitOfWorkReducerTest extends TestCase
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
        self::assertCount($expectedOperationsCount, $actualOperationsAfterReduction);

        for ($i = 0; $i < $expectedOperationsCount; $i++) {
            self::assertSame($expectedOperationsAfterReduction[$i], $actualOperationsAfterReduction[$i]);
        }
    }


    /**
     * @return Operation[][]|Operation[][][]
     */
    public function operationsToReduceProvider(): array
    {
        $mergeableOperation1 = new MergeableOperation(1);
        $mergeableOperation2 = new MergeableOperation(2);

        $notMergeableOperation1 = new NotMergeableOperation();
        $notMergeableOperation2 = new NotMergeableOperation();

        return [
            [
                [$notMergeableOperation1, $notMergeableOperation2],
                [$mergeableOperation1, $notMergeableOperation1, $notMergeableOperation2],
                new MergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2],
                [$mergeableOperation1, $mergeableOperation2, $notMergeableOperation1, $notMergeableOperation2],
                new MergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2],
                [$notMergeableOperation1, $notMergeableOperation2],
                new MergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                new MergeableOperation(3),
            ],
            [
                [],
                [$mergeableOperation1],
                new MergeableOperation(3),
            ],
            [
                [],
                [$mergeableOperation1, $mergeableOperation2],
                new MergeableOperation(3),
            ],
            [
                [],
                [],
                new MergeableOperation(3),
            ],
            [
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                [$notMergeableOperation1, $notMergeableOperation2, $mergeableOperation1],
                new NotMergeableOperation(),
            ],
        ];
    }
}
