<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface Operation
{
    /**
     * Mit moznost logovat proc
     * Kdyz je FT vypnuta, sem posleme NullLogger
     */
    public function canBeMergedWith(self $nextOperation, LoggerInterface $logger): bool;

    /*
     * Always FALSE for now - meaning that operations won't merge until we explicitly say so
     */
    public function isMergeable(): bool;


    /**
     * ?
     * Mit moznost logovat proc
     * Kdyz je FT vypnuta, sem posleme NullLogger
     */
    public function mergeWith(self $nextOperation, LoggerInterface $logger): self;


    /*
     * Always TRUE for now - meaning that operations won't merge if there is another operation in between them.
     */
    public function isChainBreakFor(self $operation): bool;


    /*
     * Define how it will be logged
     */
    public function toString(): string;
}
