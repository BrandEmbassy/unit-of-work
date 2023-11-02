<?php declare(strict_types = 1);

namespace BrandEmbassy\UnitOfWork;

interface Operation
{
    public function canBeMergedFrom(self $operation): bool;


    /*
     * Merging with previous operation - meaning that this operation is staying and the other one is removed.
     */
    public function mergeFrom(self $operation): self;


    public function canBeMergedTo(self $operation): bool;


    /*
     * Merging with following operation - meaning that this operation is removed and the other one is staying.
     */
    public function mergeTo(self $operation): self;


    /*
     * Always TRUE for now - meaning that operations won't merge if there is another operation in between them.
     */
    public function isChainBreakFor(self $operation): bool;
}
