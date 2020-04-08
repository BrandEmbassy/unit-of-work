<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface OperationProcessorAccessor
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @return OperationProcessor
     */
    public function get();
}
