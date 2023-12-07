<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @final
 */
class OperationConsolidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    /**
     * @dataProvider operationsDataProvider
     *
     * @param array<int, array<int, int>> $expectedConsolidatedOperationsState
     * @param array<int, Operation> $expectedOperations
     * @param array<int, Operation> $operationsToMerge
     */
    public function testNewMerging(
        bool $isExpectedToLog,
        array $expectedConsolidatedOperationsState,
        array $expectedOperations,
        array $operationsToMerge,
        OperationConsolidationMode $consolidationMode
    ): void {
        $operationConsolidatorResultLogger = $isExpectedToLog
            ? $this->createOperationConsolidatorLoggerMock(
                $operationsToMerge,
                $expectedConsolidatedOperationsState,
            )
            : Mockery::mock(OperationConsolidatorLogger::class);
        $reducer = new OperationConsolidator($operationConsolidatorResultLogger);

        $result = $reducer->consolidate($operationsToMerge, $consolidationMode);

        Assert::assertContainsOnlyInstancesOf(Operation::class, $result);
        Assert::assertEquals($expectedOperations, $result);
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
                'isExpectedToLog' => true,
                'expectedConsolidatedOperationsState' => [
                    6 => [0, 2, 3, 6],
                    1 => [1],
                    4 => [4],
                    5 => [5],
                ],
                'expectedOperations' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new NotMergeableOperation(),
                    2 => new DefaultMergeableOperation('b+c'),
                    3 => new NotMergeableOperation(),
                    4 => new NotMergeableOperation(),
                    5 => new DefaultMergeableOperation('d'),
                ],
                'operationsToMerge' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new NotMergeableOperation(),
                    2 => new DefaultMergeableOperation('b'),
                    3 => new DefaultMergeableOperation('c'),
                    4 => new NotMergeableOperation(),
                    5 => new NotMergeableOperation(),
                    6 => new DefaultMergeableOperation('d'),
                ],
                'consolidationMode' => $dryRunUnlimitedMode,
            ],
            'New merging enabled. All mergeable operations are merged' => [
                'isExpectedToLog' => true,
                'expectedConsolidatedOperationsState' => [
                    9 => [2, 4, 9],
                    8 => [0, 3, 5, 8],
                    7 => [7],
                    6 => [6],
                    1 => [1],
                ],
                'expectedOperations' => [
                    0 => new NotMergeableOperation(),
                    1 => new NotMergeableOperation(),
                    2 => new NotMergeableOperation(),
                    3 => new DefaultMergeableOperation('a+b+c+d'),
                    4 => new TestOnlyMergeableOperation('a+b+c'),
                ],
                'operationsToMerge' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new NotMergeableOperation(),
                    2 => new TestOnlyMergeableOperation('a'),
                    3 => new DefaultMergeableOperation('b'),
                    4 => new TestOnlyMergeableOperation('b'),
                    5 => new DefaultMergeableOperation('c'),
                    6 => new NotMergeableOperation(),
                    7 => new NotMergeableOperation(),
                    8 => new DefaultMergeableOperation('d'),
                    9 => new TestOnlyMergeableOperation('c'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'New merging enabled. All mergeable operations are merged with chain break escape mechanism' => [
                'isExpectedToLog' => true,
                'expectedConsolidatedOperationsState' => [
                    10 => [5, 10],
                    9 => [0, 4, 6, 9],
                    8 => [8],
                    7 => [7],
                    3 => [3],
                    2 => [2],
                    1 => [1],
                ],
                'expectedOperations' => [
                    0 => new NotMergeableOperation(),
                    1 => new TestOnlyMergeableOperation('a'),
                    2 => new TestOnlyChainBreakingOperation(),
                    3 => new NotMergeableOperation(),
                    4 => new NotMergeableOperation(),
                    5 => new DefaultMergeableOperation('a+b+c+d'),
                    6 => new TestOnlyMergeableOperation('b+c'),
                ],
                'operationsToMerge' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new NotMergeableOperation(),
                    2 => new TestOnlyMergeableOperation('a'),
                    3 => new TestOnlyChainBreakingOperation(),
                    4 => new DefaultMergeableOperation('b'),
                    5 => new TestOnlyMergeableOperation('b'),
                    6 => new DefaultMergeableOperation('c'),
                    7 => new NotMergeableOperation(),
                    8 => new NotMergeableOperation(),
                    9 => new DefaultMergeableOperation('d'),
                    10 => new TestOnlyMergeableOperation('c'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'New merging disabled' => [
                'isExpectedToLog' => false,
                'expectedConsolidatedOperationsState' => [],
                'expectedOperations' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new NotMergeableOperation(),
                    2 => new DefaultMergeableOperation('b+c'),
                    3 => new NotMergeableOperation(),
                    4 => new NotMergeableOperation(),
                    5 => new DefaultMergeableOperation('d'),
                ],
                'operationsToMerge' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new NotMergeableOperation(),
                    2 => new DefaultMergeableOperation('b'),
                    3 => new DefaultMergeableOperation('c'),
                    4 => new NotMergeableOperation(),
                    5 => new NotMergeableOperation(),
                    6 => new DefaultMergeableOperation('d'),
                ],
                'consolidationMode' => $neighboursOnlyMode,
            ],

            'No operations to merge' => [
                'isExpectedToLog' => false,
                'expectedConsolidatedOperationsState' => [],
                'expectedOperations' => [],
                'operationsToMerge' => [],
                'consolidationMode' => $unlimitedMode,
            ],
            'No mergeable operations to merge' => [
                'isExpectedToLog' => true,
                'expectedConsolidatedOperationsState' => [
                    0 => [0],
                    1 => [1],
                ],
                'expectedOperations' => [
                    0 => new NotMergeableOperation(),
                    1 => new NotMergeableOperation(),
                ],
                'operationsToMerge' => [
                    0 => new NotMergeableOperation(),
                    1 => new NotMergeableOperation(),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'One mergeable operation to merge' => [
                'isExpectedToLog' => false,
                'expectedConsolidatedOperationsState' => [],
                'expectedOperations' => [
                    0 => new DefaultMergeableOperation('a'),
                ],
                'operationsToMerge' => [
                    0 => new DefaultMergeableOperation('a'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'Two mergeable operations to merge' => [
                'isExpectedToLog' => true,
                'expectedConsolidatedOperationsState' => [
                    1 => [0, 1],
                ],
                'expectedOperations' => [
                    0 => new DefaultMergeableOperation('a+b'),
                ],
                'operationsToMerge' => [
                    0 => new DefaultMergeableOperation('a'),
                    1 => new DefaultMergeableOperation('b'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
        ];
    }


    public function testShouldReduce(): void
    {
        $reducer = new OperationConsolidator(new OperationConsolidatorLogger(new NullLogger()));

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
        $reducer = new OperationConsolidator(new OperationConsolidatorLogger(new NullLogger()));

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
        $reducer = new OperationConsolidator(new OperationConsolidatorLogger(new NullLogger()));
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


    /**
     * @param Operation[] $initialOperations
     * @param array<int, array<int, int>> $consolidatedOperationsState
     *
     * @return OperationConsolidatorLogger&MockInterface
     */
    private function createOperationConsolidatorLoggerMock(
        array $initialOperations,
        array $consolidatedOperationsState
    ): OperationConsolidatorLogger {
        $mock = Mockery::mock(OperationConsolidatorLogger::class);
        $mock->expects('log')
            ->with($initialOperations, $consolidatedOperationsState);

        return $mock;
    }
}
