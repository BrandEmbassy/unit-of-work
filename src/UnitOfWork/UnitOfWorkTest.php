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
        $unitOfWorkA->registerOperation(new MergeableOperation(1));
        $unitOfWorkB = new UnitOfWork();
        $unitOfWorkB->registerOperation(new MergeableOperation(2));

        $result = $unitOfWorkA->concatenate($unitOfWorkB);
        $operations = $result->getOperations();

        Assert::assertNotSame($result, $unitOfWorkA);
        Assert::assertNotSame($result, $unitOfWorkB);
        Assert::assertCount(2, $operations);

        /** @var MergeableOperation $first */
        $first = $operations[0];
        Assert::assertInstanceOf(MergeableOperation::class, $first);
        Assert::assertEquals(1, $first->number);

        /** @var MergeableOperation $second */
        $second = $operations[1];
        Assert::assertInstanceOf(MergeableOperation::class, $second);
        Assert::assertEquals(2, $second->number);
    }


    public function testShouldCreateUnitOfWorkFromOperations(): void
    {
        $operations = [
            new MergeableOperation(1),
            new MergeableOperation(2),
        ];

        $unitOfWork = UnitOfWork::fromOperations($operations);
        Assert::assertEquals($operations, $unitOfWork->getOperations());
    }


    public function testShouldBeEmpty(): void
    {
        $unitOfWorkNotEmpty = UnitOfWork::fromOperations([new MergeableOperation(1)]);
        Assert::assertFalse($unitOfWorkNotEmpty->isEmpty());

        $unitOfWorkEmpty = UnitOfWork::fromOperations([]);
        Assert::assertTrue($unitOfWorkEmpty->isEmpty());
    }
}
