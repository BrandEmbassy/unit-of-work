<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

use Stringable;

interface Operation extends Stringable
{
    public function isChainBreakFor(self $operation): bool;
}
