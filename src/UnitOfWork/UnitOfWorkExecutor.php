<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface UnitOfWorkExecutor
{
    public function execute(UnitOfWork $unitOfWork): void;
}
