<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface Operation
{
    /**
     * Mit moznost logovat proc
     * Kdyz je FT vypnuta, sem posleme NullLogger
     */
    public function canBeMergedWith(self $nextOperation): bool;


    /**
     * ?
     * Mit moznost logovat proc
     * Kdyz je FT vypnuta, sem posleme NullLogger
     */
    public function mergeWith(self $nextOperation): self;


    /*
     * Always TRUE for now - meaning that operations won't merge if there is another operation in between them.
     */
    public function isChainBreakFor(self $operation): bool;
}
