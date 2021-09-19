<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHPUnit84Migration:risky' => true,
        '@PHP74Migration:risky' => true,
        '@Symfony:risky' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'construct',
                'phpunit',
                'destruct',
                'method_public',
                'method_public_abstract',
                'method_public_static',
                'method_public_abstract_static',
                'magic',
                'method_protected',
                'method_protected_abstract',
                'method_protected_static',
                'method_protected_abstract_static',
                'method_private',
                'method_private_static',
            ]
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_order' => true,
        'array_indentation' => true,
        'align_multiline_comment' => true,
        'global_namespace_import' => true,
        'no_blank_lines_before_namespace' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'regular_callable_call' => true,
        'phpdoc_line_span' => [
            'const' => 'single',
            'property' => 'single',
            'method' => 'multi',
        ],
    ]);
