<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class OperationConsolidatorTest extends TestCase
{
    /**
     * @param array<int, Operation> $expectedOperations
     * @param array<int, Operation> $operationsToMerge
     *
     * @dataProvider operationsDataProvider
     */
    public function testNewMerging(
        array $expectedOperations,
        array $operationsToMerge,
        OperationConsolidationMode $consolidationMode
    ): void {
        $reducer = new OperationConsolidator();

        $result = $reducer->consolidate($operationsToMerge, $consolidationMode);

        Assert::assertContainsOnlyInstancesOf(Operation::class, $result);
        Assert::assertEquals($expectedOperations, $result);
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public function operationsDataProvider(): array
    {
        $dryRunUnlimitedMode = new OperationConsolidationMode(false, true, false);
        $unlimitedMode = new OperationConsolidationMode(false, true, true);
        $neighboursOnlyMode = new OperationConsolidationMode(false, false, false);

        return [
            'New merging dry run. Expected only neighbouring operations to merge' => [
                'expectedOperations' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('bc'),
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
                'expectedOperations' => [
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('abcd'),
                    $this->createAnotherMergeableOperation('abc'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    $this->createAnotherMergeableOperation('a'),
                    new DefaultMergeableOperation('b'),
                    $this->createAnotherMergeableOperation('b'),
                    new DefaultMergeableOperation('c'),
                    new NotMergeableOperation(),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('d'),
                    $this->createAnotherMergeableOperation('c'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'New merging disabled' => [
                'expectedOperations' => [
                    new DefaultMergeableOperation('a'),
                    new NotMergeableOperation(),
                    new DefaultMergeableOperation('bc'),
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
                'expectedOperations' => [],
                'operationsToMerge' => [],
                'consolidationMode' => $unlimitedMode,
            ],
            'No mergeable operations to merge' => [
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
                'expectedOperations' => [
                    new DefaultMergeableOperation('a'),
                ],
                'operationsToMerge' => [
                    new DefaultMergeableOperation('a'),
                ],
                'consolidationMode' => $unlimitedMode,
            ],
            'Two mergeable operations to merge' => [
                'expectedOperations' => [
                    new DefaultMergeableOperation('ab'),
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


    private function createAnotherMergeableOperation(string $text): MergeableOperation
    {
        return new class($text) implements MergeableOperation {
            public function __construct(
                public readonly string $text
            ) {
            }


            public function canBeMergedWith(Operation $nextOperation): bool
            {
                return $nextOperation instanceof self;
            }


            public function mergeWith(Operation $nextOperation): MergeableOperation
            {
                assert($nextOperation instanceof self);

                return new self($this->text . $nextOperation->text);
            }
        };
    }
}
