<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use function basename;
use function str_replace;

abstract class AbstractOperation implements Operation
{
    public function isChainBreakFor(Operation $operation): bool
    {
        return false;
    }


    public function __toString(): string
    {
        return basename(str_replace('\\', '/', $this::class));
    }
}
