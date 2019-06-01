<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class ReducingUnitOfWorkExecutorTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    public function testShouldExecute(): void
    {
        $consolidator = Mockery::spy(OperationConsolidator::class);
        $parentExecutor = Mockery::spy(UnitOfWorkExecutor::class);
        $executor = new ReducingUnitOfWorkExecutor($parentExecutor, $consolidator);

        $executor->execute(new UnitOfWork());

        $consolidator->shouldHaveReceived('consolidate');
        $parentExecutor->shouldHaveReceived('execute');
    }
}
