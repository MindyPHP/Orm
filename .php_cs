<?php

$header = <<<EOF
This file is part of Mindy Framework.
(c) {year} Maxim Falaleev

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude(__DIR__.'/vendor')
    ->in(__DIR__);

$config = (new PhpCsFixer\Config('ISO3166', 'ISO3166 style guide'))
    ->setRules([
        '@Symfony' => true,
        '@PSR2' => true,
        'header_comment' => [
            'header' => strtr($header, ['{year}' => date('Y')])
        ],
        'blank_line_before_return' => true,
        'array_syntax' => ['syntax' => 'short'],
        'simplified_null_return' => false,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_indent' => true,
        'phpdoc_order' => true,
        'phpdoc_align' => true,
        'phpdoc_summary' => false,
    ])
    ->setFinder($finder);

return $config;
