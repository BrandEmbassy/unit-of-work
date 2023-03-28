<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface Operation
{
    public function canBeMergedWith(self $nextOperation): bool;


    public function mergeWith(self $nextOperation): self;
}
