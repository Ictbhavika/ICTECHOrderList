<?php

declare(strict_types=1);

return [
    'preset' => 'default',
    'exclude' => [
        'src/Resources',
    ],
    'remove' => [
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenSetterSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\Functions\UnusedParameterSniff::class,
        SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
        SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff::class,
    ],
];
