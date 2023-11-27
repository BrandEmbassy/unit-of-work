<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class OperationConsolidatorTest extends TestCase
{
    public function testNewMerging(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new DefaultMergeableOperation(1),
            new NotMergeableOperation(),
            new DefaultMergeableOperation(2),
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new DefaultMergeableOperation(4),
        ];

        $result = $reducer->consolidate($operations, new OperationConsolidationMode(true, true, true));

        Assert::assertContainsOnlyInstancesOf(Operation::class, $result);
        $expectedOperations = [
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new DefaultMergeableOperation(7),
        ];
        Assert::assertEquals($expectedOperations, $result);
    }


    public function testShouldReduce(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new DefaultMergeableOperation('a'),
            new DefaultMergeableOperation('b'),
            new NotMergeableOperation(),
            new NotMergeableOperation(),
            new DefaultMergeableOperation('c'),
        ];

        $result = $reducer->consolidate($operations, new OperationConsolidationMode());

        Assert::assertCount(4, $result);
        $mergedOperation = $result[0];
        Assert::assertInstanceOf(DefaultMergeableOperation::class, $mergedOperation);
        Assert::assertEquals('a+b', $mergedOperation->text);

        Assert::assertInstanceOf(NotMergeableOperation::class, $result[1]);
        Assert::assertInstanceOf(NotMergeableOperation::class, $result[2]);
        Assert::assertNotSame($result[1], $result[2]);

        $lastNotMergerOperation = $result[3];
        Assert::assertInstanceOf(DefaultMergeableOperation::class, $lastNotMergerOperation);
        Assert::assertEquals('c', $lastNotMergerOperation->text);
    }


    public function testShouldMergeLastTwo(): void
    {
        $reducer = new OperationConsolidator();

        $operations = [
            new DefaultMergeableOperation('a'),
            new DefaultMergeableOperation('b'),
        ];

        $result = $reducer->consolidate($operations, new OperationConsolidationMode());

        Assert::assertCount(1, $result);
        $mergedOperation = $result[0];
        Assert::assertInstanceOf(DefaultMergeableOperation::class, $mergedOperation);
        Assert::assertEquals('a+b', $mergedOperation->text);
    }


    /**
     * @dataProvider trivialOperationsProvider
     *
     * @param Operation[] $data
     */
    public function testShouldReduceTrivial(array $data): void
    {
        $reducer = new OperationConsolidator();
        Assert::assertEquals($data, $reducer->consolidate($data, new OperationConsolidationMode()));
    }


    /**
     * @return Operation[][][]
     */
    public static function trivialOperationsProvider(): array
    {
        return [
            [[]],
            [[new DefaultMergeableOperation('aaa')]],
        ];
    }


//    private function assertExpectedOperations(array $expectedOperations, array $actualOperations): bool
//    {
//
//    }
}
