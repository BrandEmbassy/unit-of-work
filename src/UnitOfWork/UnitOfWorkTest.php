<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class UnitOfWorkTest extends TestCase
{
    public function testShouldUnionUnitOfWork(): void
    {
        $unitOfWorkA = new UnitOfWork();
        $unitOfWorkA->registerOperation(new BaseMergeableOperation(1));
        $unitOfWorkB = new UnitOfWork();
        $unitOfWorkB->registerOperation(new BaseMergeableOperation(2));

        $result = $unitOfWorkA->concatenate($unitOfWorkB);
        $operations = $result->getOperations();

        Assert::assertNotSame($result, $unitOfWorkA);
        Assert::assertNotSame($result, $unitOfWorkB);
        Assert::assertCount(2, $operations);

        /** @var BaseMergeableOperation $first */
        $first = $operations[0];
        Assert::assertInstanceOf(BaseMergeableOperation::class, $first);
        Assert::assertEquals(1, $first->number);

        /** @var BaseMergeableOperation $second */
        $second = $operations[1];
        Assert::assertInstanceOf(BaseMergeableOperation::class, $second);
        Assert::assertEquals(2, $second->number);
    }


    public function testShouldCreateUnitOfWorkFromOperations(): void
    {
        $operations = [
            new BaseMergeableOperation(1),
            new BaseMergeableOperation(2),
        ];

        $unitOfWork = UnitOfWork::fromOperations($operations);
        Assert::assertEquals($operations, $unitOfWork->getOperations());
    }


    public function testShouldBeEmpty(): void
    {
        $unitOfWorkNotEmpty = UnitOfWork::fromOperations([new BaseMergeableOperation(1)]);
        Assert::assertFalse($unitOfWorkNotEmpty->isEmpty());

        $unitOfWorkEmpty = UnitOfWork::fromOperations([]);
        Assert::assertTrue($unitOfWorkEmpty->isEmpty());
    }
}
