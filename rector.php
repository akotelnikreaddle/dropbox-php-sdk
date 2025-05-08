<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\AddPropertyTypeDeclarationRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php84: true)
    ->withComposerBased(symfony: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        rectorPreset: true,
    )
    ->withPhpVersion(Rector\ValueObject\PhpVersion::PHP_84)
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withParallel()
    ->withSkip([
        EncapsedStringsToSprintfRector::class,
    ])
    ->withRules([
        AddPropertyTypeDeclarationRector::class,
    ]);
