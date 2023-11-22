<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class OperationConsolidatorTest extends TestCase
{
    public function testShouldReduce(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new DefaultMergeableOperation(1),
            new DefaultMergeableOperation(2),
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new DefaultMergeableOperation(4),
        ];

        $result = $reducer->consolidate($operations, false, false, false);

        Assert::assertCount(4, $result);
        $mergedOperation = $result[0];
        Assert::assertInstanceOf(DefaultMergeableOperation::class, $mergedOperation);
        Assert::assertEquals(3, $mergedOperation->number);

        Assert::assertInstanceOf(NotMergeableOperation::class, $result[1]);
        Assert::assertInstanceOf(NotMergeableOperation::class, $result[2]);
        Assert::assertNotSame($result[1], $result[2]);

        $lastNotMergerOperation = $result[3];
        Assert::assertInstanceOf(DefaultMergeableOperation::class, $lastNotMergerOperation);
        Assert::assertEquals(4, $lastNotMergerOperation->number);
    }


    public function testShouldMergeLastTwo(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new DefaultMergeableOperation(1),
            new DefaultMergeableOperation(2),
        ];

        $result = $reducer->consolidate($operations, false, false, false);

        Assert::assertCount(1, $result);
        $mergedOperation = $result[0];
        Assert::assertInstanceOf(DefaultMergeableOperation::class, $mergedOperation);
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
        Assert::assertEquals($data, $reducer->consolidate($data, false, false, false));
    }


    /**
     * @return Operation[][][]
     */
    public static function trivialOperationsProvider(): array
    {
        return [
            [[]],
            [[new DefaultMergeableOperation(666)]],
        ];
    }
}
