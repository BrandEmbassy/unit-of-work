<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface OperationProcessorAccessor
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     * @return OperationProcessor
     */
    public function get();
}
