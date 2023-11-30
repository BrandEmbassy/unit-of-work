<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

/**
 * @final
 */
class OperationConsolidationResult
{
    /**
     * @param Operation[] $initialOperations
     * @param Operation[] $consolidatedOperations
     * @param array<int, array<int, mixed>> $consolidatedOperationsState
     */
    public function __construct(
        private readonly array $initialOperations,
        private readonly array $consolidatedOperations,
        private readonly array $consolidatedOperationsState
    ) {
    }


    /**
     * @return Operation[]
     */
    public function getInitialOperations(): array
    {
        return $this->initialOperations;
    }


    /**
     * @return Operation[]
     */
    public function getConsolidatedOperations(): array
    {
        return $this->consolidatedOperations;
    }


    /**
     * @return array<int, array<int, mixed>> $consolidatedOperationsState
     */
    public function getConsolidatedOperationsState(): array
    {
        return $this->consolidatedOperationsState;
    }
}
