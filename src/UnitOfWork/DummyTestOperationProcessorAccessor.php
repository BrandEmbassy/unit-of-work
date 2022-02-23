<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class DummyTestOperationProcessorAccessor implements OperationProcessorAccessor
{
    /**
     * @var OperationProcessor
     */
    private $operationProcessor;


    public function __construct(OperationProcessor $operationProcessor)
    {
        $this->operationProcessor = $operationProcessor;
    }


    public function get(): OperationProcessor
    {
        return $this->operationProcessor;
    }
}
