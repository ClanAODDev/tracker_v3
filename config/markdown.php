<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Markdown.
 *
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Node\Block\Paragraph;

return [

    /*
    |--------------------------------------------------------------------------
    | Enable View Integration
    |--------------------------------------------------------------------------
    |
    | This option specifies if the view integration is enabled so you can write
    | markdown views and have them rendered as html. The following extensions
    | are currently supported: ".md", ".md.php", and ".md.blade.php". You may
    | disable this integration if it is conflicting with another package.
    |
    | Default: true
    |
    */

    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | CommonMark Extensions
    |--------------------------------------------------------------------------
    |
    | This option specifies what extensions will be automatically enabled.
    | Simply provide your extension class names here.
    |
    | Default: [
    |              League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension::class,
    |              League\CommonMark\Extension\Table\TableExtension::class,
    |          ]
    |
    */

    'extensions' => [
        League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension::class,
        League\CommonMark\Extension\Table\TableExtension::class,
        League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension::class,
        League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension::class,
        League\CommonMark\Extension\Autolink\AutolinkExtension::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Renderer Configuration
    |--------------------------------------------------------------------------
    */

    'renderer' => [
        'block_separator' => "\n\n",
        'inner_separator' => "\n",
        'soft_break' => "\n",
    ],

    'commonmark' => [
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisk' => true,
        'use_underscore' => true,
        'unordered_list_markers' => ['-', '+', '*'],
    ],

    'heading_permalink' => [
        'html_class' => 'heading-permalink smooth-scroll',
        'id_prefix' => 'content',
        'apply_id_to_heading' => false,
        'heading_class' => '',
        'fragment_prefix' => 'content',
        'insert' => 'after',
        'min_heading_level' => 1,
        'max_heading_level' => 6,
        'title' => 'Permalink',
        'symbol' => HeadingPermalinkRenderer::DEFAULT_SYMBOL,
        'aria_hidden' => true,
    ],

    'default_attributes' => [
        Table::class => [
            'class' => ['table', 'table-responsive', 'table-hover'],
        ],

        Heading::class => [
          'style' => 'border-bottom: 1px solid #3d404c; padding-bottom:10px',
        ],

        BlockQuote::class => [
            'class' => 'blockquote',
        ],

        Link::class => [
            'class' => '',
            'target' => '_blank',
        ],

        Code::class => [
            'class' => 'line-numbers'
        ],

        Paragraph::class => [
            'class' => static function (Paragraph $paragraph) {
                if ($paragraph->previous() instanceof Heading && $paragraph->previous()->getLevel() === 1) {
                    return 'lead';
                }

                return null;
            },
        ],


    ],

    'table' => [
        'alignment_attributes' => [
            'left' => ['class' => 'text-start'],
            'center' => ['class' => 'text-center'],
            'right' => ['class' => 'text-end'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTML Input
    |--------------------------------------------------------------------------
    |
    | This option specifies how to handle untrusted HTML input.
    |
    | Default: 'strip'
    |
    */

    'html_input' => 'strip',

    /*
    |--------------------------------------------------------------------------
    | Allow Unsafe Links
    |--------------------------------------------------------------------------
    |
    | This option specifies whether to allow risky image URLs and links.
    |
    | Default: true
    |
    */

    'allow_unsafe_links' => true,

    /*
    |--------------------------------------------------------------------------
    | Maximum Nesting Level
    |--------------------------------------------------------------------------
    |
    | This option specifies the maximum permitted block nesting level.
    |
    | Default: PHP_INT_MAX
    |
    */

    'max_nesting_level' => PHP_INT_MAX,

    /*
    |--------------------------------------------------------------------------
    | Slug Normalizer
    |--------------------------------------------------------------------------
    |
    | This option specifies an array of options for slug normalization.
    |
    | Default: [
    |              'max_length' => 255,
    |              'unique' => 'document',
    |          ]
    |
    */

    'slug_normalizer' => [
        'max_length' => 255,
        'unique' => 'document',
    ],

];
