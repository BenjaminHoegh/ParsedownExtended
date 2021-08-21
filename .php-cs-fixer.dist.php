<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('docs')
    ->exclude('lab')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PhpCsFixer' => true,
        'yoda_style' => false,
        'phpdoc_add_missing_param_annotation' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => false,
        'phpdoc_order_by_value' => false,
        'phpdoc_types_order' => false,
        'phpdoc_var_annotation_correct_order' => false,
        'phpdoc_inline_tag_normalizer' => false,
        'phpdoc_no_access' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_no_package' => false,
        'phpdoc_no_useless_inheritdoc' => false,
        'phpdoc_return_self_reference' => false,
        'phpdoc_scalar' => false,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => false,
        'phpdoc_summary' => false,
        'phpdoc_tag_type' => false,
        'phpdoc_types' => false,
        'phpdoc_var_without_name' => false,
    ])
    ->setFinder($finder)
;
