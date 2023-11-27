<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @final
 */
class NaiveUnitOfWorkExecutorTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    public function testShouldExecuteOperation(): void
    {
        $mergeableOperationProcessor = $this->createMergeableOperationProcessorMock();
        $accessors = [new DummyTestOperationProcessorAccessor($mergeableOperationProcessor)];
        $executor = new NaiveUnitOfWorkExecutor($accessors, new NullLogger());

        $unitOfWork = new UnitOfWork();
        $unitOfWork->registerOperation(new DefaultMergeableOperation('a'));
        $unitOfWork->registerOperation(new NotMergeableOperation());

        $executor->execute($unitOfWork, new OperationConsolidationMode());
    }


    /**
     * @return MockInterface&OperationProcessor
     */
    private function createMergeableOperationProcessorMock(): OperationProcessor
    {
        /** @var MockInterface&OperationProcessor $mock */
        $mock = Mockery::mock(OperationProcessor::class);
        $mock->shouldReceive('getSupportedOperations')->twice()->andReturn([DefaultMergeableOperation::class]);
        $mock->shouldReceive('process')->once();

        return $mock;
    }
}
