<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

/**
 * @final
 */
class OperationConsolidatorTest extends TestCase
{
    /**
     * @dataProvider operationsDataProvider
     *
     * @param array<int, Operation> $expectedOperations
     * @param array<int, Operation> $operationsToMerge
     */
    public function testNewMerging(
        ?string $expectedLogMessage,
        array $expectedOperations,
        array $operationsToMerge,
        OperationConsolidationMode $consolidationMode
    ): void {
        $logger = new TestLogger();
        $reducer = new OperationConsolidator($logger);

        $result = $reducer->consolidate($operationsToMerge, $consolidationMode);

        Assert::assertContainsOnlyInstancesOf(Operation::class, $result);
        Assert::assertEquals($expectedOperations, $result);
        if ($expectedLogMessage !== null) {
            Assert::assertTrue($logger->hasDebugThatContains($expectedLogMessage));
        }
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public function operationsDataProvider(): array
    {
        $dryRunUnlimitedMode = new OperationConsolidationMode(true, true, false);
        $unlimitedMode = new OperationConsolidationMode(true, true, true);
        $neighboursOnlyMode = new OperationConsolidationMode(false, false, false);

        return [
            'New merging dry run. Expected only neighbouring operations to merge' => [
                'expectedLogMessage' => 'UoW Operations [(0) DefaultMergeableOperation, (1) NotMergeableOperation, (2) DefaultMergeableOperation, (3) DefaultMergeableOperation, (4) NotMergeableOperation, (5) NotMergeableOperation, (6) DefaultMergeableOperation] got merged into [(1) NotMergeableOperation, (4) NotMergeableOperation, (5) NotMergeableOperation, (0, 2, 3, 6) DefaultMergeableOperation]',
                'expectedOperations' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('b+c'),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('d'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('b'),
                    new DefaultMergeableOperation('c'),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('d'),
                ],
                'consolidationMode' => $dryRunUnlimitedMode,
            ],
            'New merging enabled. All mergeable operations are merged' => [
                'expectedLogMessage' => 'UoW Operations [(0) DefaultMergeableOperation, (1) NotMergeableOperation, (2) AnotherDefaultMergeableOperation, (3) DefaultMergeableOperation, (4) AnotherDefaultMergeableOperation, (5) DefaultMergeableOperation, (6) NotMergeableOperation, (7) NotMergeableOperation, (8) DefaultMergeableOperation, (9) AnotherDefaultMergeableOperation] got merged into [(1) NotMergeableOperation, (6) NotMergeableOperation, (7) NotMergeableOperation, (0, 3, 5, 8) DefaultMergeableOperation, (2, 4, 9) AnotherDefaultMergeableOperation]',
                'expectedOperations' => [
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('a+b+c+d'),
                    new TestOnlyMergeableOperation('a+b+c'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new TestOnlyMergeableOperation('a'),
                    new DefaultMergeableOperation('b'),
                    new TestOnlyMergeableOperation('b'),
                    new DefaultMergeableOperation('c'),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('d'),
                    new TestOnlyMergeableOperation('c'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'New merging disabled' => [
                'expectedLogMessage' => null,
                'expectedOperations' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('b+c'),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('d'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('b'),
                    new DefaultMergeableOperation('c'),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('d'),
                ],
                'consolidationMode' => $neighboursOnlyMode,
            ],

            'No operations to merge' => [
                'expectedLogMessage' => null,
                'expectedOperations' => [],
                'operationsToMerge' => [],
                'consolidationMode' => $unlimitedMode,
            ],
            'No mergeable operations to merge' => [
                'expectedLogMessage' => 'UoW Operations [(0) NotMergeableOperation, (1) NotMergeableOperation] got merged into [(0) NotMergeableOperation, (1) NotMergeableOperation]',
                'expectedOperations' => [
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                ],
                'operationsToMerge' => [
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'One mergeable operation to merge' => [
                'expectedLogMessage' => null,
                'expectedOperations' => [
                    new DefaultMergeableOperation('a'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'Two mergeable operations to merge' => [
                'expectedLogMessage' => 'UoW Operations [(0) DefaultMergeableOperation, (1) DefaultMergeableOperation] got merged into [(0, 1) DefaultMergeableOperation]',
                'expectedOperations' => [
                    new DefaultMergeableOperation('a+b'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                    new DefaultMergeableOperation('b'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
        ];
    }


    public function testShouldReduce(): void
    {
        $reducer = new OperationConsolidator(new TestLogger());

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
        $reducer = new OperationConsolidator(new TestLogger());

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
        $reducer = new OperationConsolidator(new TestLogger());
        Assert::assertEquals($data, $reducer->consolidate($data, new OperationConsolidationMode()));
    }


    /**
     * @return Operation[][][]
     */
    public static function trivialOperationsProvider(): array
    {
        return [
            [[]],
            [[new DefaultMergeableOperation('a+a+a')]],
        ];
    }
}
