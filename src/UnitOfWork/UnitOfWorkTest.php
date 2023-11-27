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
        $unitOfWorkA->registerOperation(new DefaultMergeableOperation('a'));
        $unitOfWorkB = new UnitOfWork();
        $unitOfWorkB->registerOperation(new DefaultMergeableOperation('b'));

        $result = $unitOfWorkA->concatenate($unitOfWorkB);
        $operations = $result->getOperations();

        Assert::assertNotSame($result, $unitOfWorkA);
        Assert::assertNotSame($result, $unitOfWorkB);
        Assert::assertCount(2, $operations);

        /** @var DefaultMergeableOperation $first */
        $first = $operations[0];
        Assert::assertEquals('a', $first->text);

        /** @var DefaultMergeableOperation $second */
        $second = $operations[1];
        Assert::assertEquals('b', $second->text);
    }


    public function testShouldCreateUnitOfWorkFromOperations(): void
    {
        $operations = [
            new DefaultMergeableOperation('a'),
            new DefaultMergeableOperation('b'),
        ];

        $unitOfWork = UnitOfWork::fromOperations($operations);
        Assert::assertEquals($operations, $unitOfWork->getOperations());
    }


    public function testShouldBeEmpty(): void
    {
        $unitOfWorkNotEmpty = UnitOfWork::fromOperations([new DefaultMergeableOperation('a')]);
        Assert::assertFalse($unitOfWorkNotEmpty->isEmpty());

        $unitOfWorkEmpty = UnitOfWork::fromOperations([]);
        Assert::assertTrue($unitOfWorkEmpty->isEmpty());
    }
}
