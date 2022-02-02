<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface HavingPriority
{
    public const DEFAULT_PRIORITY = 500;


    public function getPriority(): int;
}
