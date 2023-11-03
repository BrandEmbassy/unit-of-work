<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class OperationReducerTest extends TestCase
{
    public function testShouldReduce(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new BaseMergeableOperation(1),
            new BaseMergeableOperation(2),
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new BaseMergeableOperation(4),
        ];

        $result = $reducer->consolidate($operations);

        Assert::assertCount(4, $result);
        /** @var BaseMergeableOperation $mergedOperation */
        $mergedOperation = $result[0];
        Assert::assertInstanceOf(BaseMergeableOperation::class, $mergedOperation);
        Assert::assertEquals(3, $mergedOperation->number);

        Assert::assertInstanceOf(NotMergeableOperation::class, $result[1]);
        Assert::assertInstanceOf(NotMergeableOperation::class, $result[2]);
        Assert::assertNotSame($result[1], $result[2]);

        /** @var BaseMergeableOperation $lastNotMergerOperation */
        $lastNotMergerOperation = $result[3];
        Assert::assertInstanceOf(BaseMergeableOperation::class, $lastNotMergerOperation);
        Assert::assertEquals(4, $lastNotMergerOperation->number);
    }


    public function testShouldMergeLastTwo(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new BaseMergeableOperation(1),
            new BaseMergeableOperation(2),
        ];

        $result = $reducer->consolidate($operations);

        Assert::assertCount(1, $result);
        /** @var BaseMergeableOperation $mergedOperation */
        $mergedOperation = $result[0];
        Assert::assertInstanceOf(BaseMergeableOperation::class, $mergedOperation);
        Assert::assertEquals(3, $mergedOperation->number);
    }


    /**
     * @dataProvider trivialOperationsProvider
     *
     * @param Operation[] $data
     */
    public function testShouldReduceTrivial(array $data): void
    {
        $reducer = new OperationConsolidator();
        Assert::assertEquals($data, $reducer->consolidate($data));
    }


    /**
     * @return Operation[][][]
     */
    public static function trivialOperationsProvider(): array
    {
        return [
            [[]],
            [[new BaseMergeableOperation(666)]],
        ];
    }
}
