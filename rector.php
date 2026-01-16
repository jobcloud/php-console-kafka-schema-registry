<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        naming: false,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withAttributesSets(
        phpunit: true,
    )
    ->withPhpSets(php84: true)
    ->withSkip([
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        ReadOnlyPropertyRector::class,
        SimplifyBoolIdenticalTrueRector::class,
        SimplifyQuoteEscapeRector::class
    ]);
