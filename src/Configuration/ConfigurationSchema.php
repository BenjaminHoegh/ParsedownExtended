<?php

declare(strict_types=1);

namespace BenjaminHoegh\ParsedownExtended\Configuration;

final class ConfigurationSchema
{
    public const DEFAULT = [
        'abbreviations' => [
            'allow_custom' => true,
            'predefined'   => [],
        ],
        'code'      => ['blocks' => true, 'inline' => true],
        'comments'  => true,
        'definition_lists' => true,
        'diagrams' => [
            'enabled' => false,
            'chartjs' => true,
            'mermaid' => true,
        ],
        'emojis' => true,
        'emphasis' => [
            'bold'           => true,
            'italic'         => true,
            'strikethroughs' => true,
            'insertions'     => true,
            'subscript'      => false,
            'superscript'    => false,
            'keystrokes'     => true,
            'mark'           => true,
        ],
        'footnotes' => true,
        'headings' => [
            'allowed_levels' => ['h1','h2','h3','h4','h5','h6'],
            'auto_anchors' => [
                'delimiter'     => '-',
                'lowercase'     => true,
                'replacements'  => [],
                'transliterate' => false,
                'blacklist'     => [],
            ],
            'special_attributes' => true,
        ],
        'images' => true,
        'links' => [
            'current_host' => '',
            'email_links' => true,
            'external_links' => [
                'nofollow'           => true,
                'noopener'           => true,
                'noreferrer'         => true,
                'open_in_new_window' => true,
                'internal_hosts'     => [],
            ],
        ],
        'lists' => ['tasks' => true],
        'allow_raw_html' => true,
        'alerts' => [
            'types' => ['note','tip','important','warning','caution'],
            'class' => 'markdown-alert',
        ],
        'math' => [
            'enabled' => false,
            'inline' => [
                'delimiters' => [['left' => '$',  'right' => '$']],
            ],
            'block'  => [
                'delimiters' => [['left' => '$$', 'right' => '$$']],
            ],
        ],
        'quotes' => true,
        'smartypants' => [
            'enabled'             => false,
            'smart_angled_quotes' => true,
            'smart_backticks'     => true,
            'smart_dashes'        => true,
            'smart_ellipses'      => true,
            'smart_quotes'        => true,
            'substitutions' => [
                'ellipses'           => '&hellip;',
                'left_angle_quote'   => '&laquo;',
                'left_double_quote'  => '&ldquo;',
                'left_single_quote'  => '&lsquo;',
                'mdash'              => '&mdash;',
                'ndash'              => '&ndash;',
                'right_angle_quote'  => '&raquo;',
                'right_double_quote' => '&rdquo;',
                'right_single_quote' => '&rsquo;',
            ],
        ],
        'tables'         => ['tablespan' => true],
        'thematic_breaks' => true,
        'toc' => [
            'levels' => ['h1','h2','h3','h4','h5','h6'],
            'tag'    => '[TOC]',
            'id'     => 'toc',
        ],
        'typographer' => true,
        'references'  => true,
    ];
}
