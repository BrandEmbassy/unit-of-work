<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface OperationProcessorAccessor
{
    public function get(): OperationProcessor;
}
