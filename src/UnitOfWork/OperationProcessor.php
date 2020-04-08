<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface OperationProcessor
{
    /**
     * @return string[]
     */
    public function getSupportedOperations(): array;


    /**
     * @throws UnableToProcessOperationException
     */
    public function process(Operation $operation): void;
}
