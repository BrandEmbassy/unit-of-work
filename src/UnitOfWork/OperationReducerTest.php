<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\TestCase;

final class OperationReducerTest extends TestCase
{
    public function testShouldReduce(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new MergeableOperation(1),
            new MergeableOperation(2),
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new MergeableOperation(4),
        ];

        $result = $reducer->consolidate($operations);

        self::assertCount(4, $result);
        /** @var MergeableOperation $mergedOperation */
        $mergedOperation = $result[0];
        self::assertInstanceOf(MergeableOperation::class, $mergedOperation);
        self::assertEquals(3, $mergedOperation->number);

        self::assertInstanceOf(NotMergeableOperation::class, $result[1]);
        self::assertInstanceOf(NotMergeableOperation::class, $result[2]);
        self::assertNotSame($result[1], $result[2]);

        /** @var MergeableOperation $lastNotMergerOperation */
        $lastNotMergerOperation = $result[3];
        self::assertInstanceOf(MergeableOperation::class, $lastNotMergerOperation);
        self::assertEquals(4, $lastNotMergerOperation->number);
    }


    public function testShouldMergeLastTwo(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new MergeableOperation(1),
            new MergeableOperation(2),
        ];

        $result = $reducer->consolidate($operations);

        self::assertCount(1, $result);
        /** @var MergeableOperation $mergedOperation */
        $mergedOperation = $result[0];
        self::assertInstanceOf(MergeableOperation::class, $mergedOperation);
        self::assertEquals(3, $mergedOperation->number);
    }


    /**
     * @dataProvider trivialOperationsProvider
     * @param Operation[] $data
     */
    public function testShouldReduceTrivial(array $data): void
    {
        $reducer = new OperationConsolidator();
        self::assertEquals($data, $reducer->consolidate($data));
    }


    /**
     * @return Operation[][][]
     */
    public function trivialOperationsProvider(): array
    {
        return [
            [[]],
            [[new MergeableOperation(666)]],
        ];
    }
}
