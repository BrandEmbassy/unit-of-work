<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function count;

final class UnitOfWork
{
    /**
     * @var Operation[]
     */
    private $operations = [];


    public function registerOperation(Operation $operation): void
    {
        $this->operations[] = $operation;
    }


    /**
     * @param Operation[] $operations
     * @return UnitOfWork
     */
    public static function fromOperations(array $operations): self
    {
        $unitOfWork = new self();
        $unitOfWork->operations = $operations;

        return $unitOfWork;
    }


    /**
     * @return Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }


    public function concatenate(UnitOfWork $otherUnitOfWork): UnitOfWork
    {
        $unitOfWork = new self();

        foreach ($this->operations as $operation) {
            $unitOfWork->registerOperation($operation);
        }

        foreach ($otherUnitOfWork->getOperations() as $operation) {
            $unitOfWork->registerOperation($operation);
        }

        return $unitOfWork;
    }


    public function isEmpty(): bool
    {
        return count($this->operations) === 0;
    }
}
