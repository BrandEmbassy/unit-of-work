<?php declare(strict_types = 1);

use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$defaultEcsConfigurationSetup = require 'vendor/brandembassy/coding-standard/default-ecs.php';

return static function (ECSConfig $ecsConfig) use ($defaultEcsConfigurationSetup): void {
    $defaultSkipList = $defaultEcsConfigurationSetup($ecsConfig, __DIR__);

    $ecsConfig->paths([
        'src',
        'ecs.php',
    ]);

    $skipList = [
        StrictComparisonFixer::class => [
            __DIR__ . '/src/UnitOfWork/UnitOfWorkAssertions.php',
        ],
    ];

    $ecsConfig->skip(array_merge($defaultSkipList, $skipList));
};
