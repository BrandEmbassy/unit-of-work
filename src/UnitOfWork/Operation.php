<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface Operation
{
    public function canBeMergedWith(Operation $nextOperation): bool;


    public function mergeWith(Operation $nextOperation): Operation;
}
