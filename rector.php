<?php declare(strict_types = 1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return static function (RectorConfig $rectorConfig): void {
    $defaultRectorConfigurationSetup = require 'vendor/brandembassy/coding-standard/default-rector.php';
    $defaultSkipList = $defaultRectorConfigurationSetup($rectorConfig);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('./var/rector');

    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $skipList = [];

    $rectorConfig->skip(
        array_merge(
            $defaultSkipList,
            $skipList
        )
    );
};
