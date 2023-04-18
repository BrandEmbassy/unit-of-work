<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Nette\StaticClass;
use PHPUnit\Framework\TestCase;
use function count;
use function get_class;
use function implode;
use function sprintf;

/**
 * @final
 */
class UnitOfWorkAssertions
{
    use StaticClass;


    /**
     * @param array<Operation> $expectedOperations
     */
    public static function assertOperationList(UnitOfWork $resultUnitOfWork, array $expectedOperations): void
    {
        $remainingOperations = [];
        foreach ($resultUnitOfWork->getOperations() as $resultOperation) {
            $remainingOperations = [];
            foreach ($expectedOperations as $expectedOperation) {
                // phpcs:disable
                /** @noinspection TypeUnsafeComparisonInspection */
                if ($expectedOperation == $resultOperation) {
                    continue;
                }
                // phpcs:enable

                $remainingOperations[] = $expectedOperation;
            }

            if (count($remainingOperations) === count($expectedOperations)) {
                TestCase::fail(sprintf('Operation %s is not expected.', get_class($resultOperation)));
            }

            $expectedOperations = $remainingOperations;
        }

        if ($remainingOperations !== []) {
            $operationClassNames = array_map(
                static fn(Operation $operation): string => get_class($operation),
                $remainingOperations,
            );
            TestCase::fail(
                sprintf(
                    'Some expected operations are missing: %s',
                    implode(', ', $operationClassNames),
                ),
            );
        }
    }
}
