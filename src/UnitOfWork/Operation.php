<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface Operation
{
    public function isChainBreakFor(self $operation): bool;
}
