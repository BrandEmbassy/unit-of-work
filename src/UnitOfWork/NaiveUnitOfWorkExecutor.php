<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Assert\Assertion;
use Psr\Log\LoggerInterface;
use function get_class;
use function in_array;
use function sprintf;

final class NaiveUnitOfWorkExecutor implements UnitOfWorkExecutor
{
    /**
     * @var OperationProcessorAccessor[]
     */
    private $operationProcessorAccessors;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param OperationProcessorAccessor[] $operationProcessorAccessors
     * @param LoggerInterface              $logger
     */
    public function __construct(array $operationProcessorAccessors, LoggerInterface $logger)
    {
        Assertion::allIsInstanceOf($operationProcessorAccessors, OperationProcessorAccessor::class);
        $this->operationProcessorAccessors = $operationProcessorAccessors;
        $this->logger = $logger;
    }


    public function execute(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getOperations() as $operation) {
            $this->logger->info(sprintf('Executing operation %s.', get_class($operation)));

            $processed = false;
            foreach ($this->operationProcessorAccessors as $operationProcessorAccessor) {
                $operationProcessor = $operationProcessorAccessor->get();

                if (in_array(get_class($operation), $operationProcessor->getSupportedOperations(), true)) {
                    $processed = true;
                    $operationProcessor->process($operation);
                }
            }

            if (!$processed) {
                $errorMessage = sprintf('There is no Processor for operation %s.', get_class($operation));
                $this->logger->error($errorMessage);
            }
        }
    }
}
