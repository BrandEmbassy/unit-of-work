<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

/**
 * @final
 */
class OperationConsolidatorLoggerTest extends TestCase
{
    /**
     * @dataProvider operationsDataProvider
     *
     * @param array<int, array<int, int>> $consolidatedOperationsState
     * @param array<int, Operation> $initialOperations
     */
    public function testNewMerging(
        ?string $expectedLogMessage,
        array $consolidatedOperationsState,
        array $initialOperations
    ): void {
        $logger = new TestLogger();
        $operationConsolidatorLogger = new OperationConsolidatorLogger($logger);

        $operationConsolidatorLogger->log($initialOperations, $consolidatedOperationsState);

        if ($expectedLogMessage !== null) {
            Assert::assertTrue($logger->hasDebugThatContains($expectedLogMessage));
        } else {
            Assert::assertFalse($logger->hasDebugThatContains('got merged into'));
        }
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public function operationsDataProvider(): array
    {
        return [
            'New merging enabled. All mergeable operations are merged' => [
                'expectedLogMessage' => 'UoW Operations [(0) DefaultMergeableOperation, (1) NotMergeableOperation, (2) TestOnlyMergeableOperation, (3) DefaultMergeableOperation, (4) TestOnlyMergeableOperation, (5) DefaultMergeableOperation, (6) NotMergeableOperation, (7) NotMergeableOperation, (8) DefaultMergeableOperation, (9) TestOnlyMergeableOperation] got merged into [(1) NotMergeableOperation, (6) NotMergeableOperation, (7) NotMergeableOperation, (0, 3, 5, 8) DefaultMergeableOperation, (2, 4, 9) TestOnlyMergeableOperation]',
                'consolidatedOperationsState' => [
                    9 => [2, 4, 9],
                    8 => [0, 3, 5, 8],
                    7 => [7],
                    6 => [6],
                    1 => [1],
                ],
                'initialOperations' => [
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
            ],

            'No operations merged' => [
                'expectedLogMessage' => null,
                'consolidatedOperationsState' => [],
                'initialOperations' => [],
            ],
            'One operation to merge' => [
                'expectedLogMessage' => null,
                'consolidatedOperationsState' => [
                    0 => [0],
                ],
                'initialOperations' => [
                    new DefaultMergeableOperation('a'),
                ],
            ],
            'Two mergeable operations to merge' => [
                'expectedLogMessage' => 'UoW Operations [(0) DefaultMergeableOperation, (1) DefaultMergeableOperation] got merged into [(0, 1) DefaultMergeableOperation]',
                'consolidatedOperationsState' => [
                    1 => [0, 1],
                ],
                'initialOperations' => [
                    new DefaultMergeableOperation('a'),
                    new DefaultMergeableOperation('b'),
                ],
            ],
        ];
    }
}
