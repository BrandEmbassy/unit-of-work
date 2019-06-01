<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use PHPUnit\Framework\TestCase;

final class UnitOfWorkTest extends TestCase
{
    public function testShouldUnionUnitOfWork(): void
    {
        $unitOfWorkA = new UnitOfWork();
        $unitOfWorkA->registerOperation(new MergeableOperation(1));
        $unitOfWorkB = new UnitOfWork();
        $unitOfWorkB->registerOperation(new MergeableOperation(2));

        $result = $unitOfWorkA->concatenate($unitOfWorkB);
        $operations = $result->getOperations();

        self::assertNotSame($result, $unitOfWorkA);
        self::assertNotSame($result, $unitOfWorkB);
        self::assertCount(2, $operations);

        /** @var MergeableOperation $first */
        $first = $operations[0];
        self::assertInstanceOf(MergeableOperation::class, $first);
        self::assertEquals(1, $first->number);

        /** @var MergeableOperation $second */
        $second = $operations[1];
        self::assertInstanceOf(MergeableOperation::class, $second);
        self::assertEquals(2, $second->number);
    }


    public function testShouldCreateUnitOfWorkFromOperations(): void
    {
        $operations = [
            new MergeableOperation(1),
            new MergeableOperation(2),
        ];

        $unitOfWork = UnitOfWork::fromOperations($operations);
        self::assertEquals($operations, $unitOfWork->getOperations());
    }


    public function testShouldBeEmpty(): void
    {
        $unitOfWorkNotEmpty = UnitOfWork::fromOperations([new MergeableOperation(1)]);
        self::assertFalse($unitOfWorkNotEmpty->isEmpty());

        $unitOfWorkEmpty = UnitOfWork::fromOperations([]);
        self::assertTrue($unitOfWorkEmpty->isEmpty());
    }
}
