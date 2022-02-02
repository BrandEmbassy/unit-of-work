<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ReducingUnitOfWorkExecutorTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    /**
     * @throws Throwable
     */
    public function testShouldExecute(): void
    {
        $consolidator = Mockery::spy(OperationConsolidator::class);
        $sorter = Mockery::spy(OperationsByPrioritySorter::class);
        $parentExecutor = Mockery::spy(UnitOfWorkExecutor::class);
        $executor = new ReducingUnitOfWorkExecutor($parentExecutor, $consolidator, $sorter);

        $executor->execute(new UnitOfWork());

        $consolidator->shouldHaveReceived('consolidate');
        $sorter->shouldHaveReceived('sort');
        $parentExecutor->shouldHaveReceived('execute');
    }
}
