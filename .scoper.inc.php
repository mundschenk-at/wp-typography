<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

const WP_TYPOGRAPHY_EXCLUDED_FILES      = '.*\\.dist|Makefile|composer\\.json|composer\\.lock|phpcs\\.xml|phpunit.xml|phpbench\\.json|.*\\.md|.*\\.txt';
const WP_TYPOGRAPHY_EXCLUDED_DIRS       = [
    'bin',
    'doc',
    'test',
    'test_old',
    'tests',
    'Tests',
    'vendor-bin',
    // Partial templates will be copied by Grunt.
    'partials'
];
// Global WordPress functions we use.
const WP_TYPOGRAPHY_WORDPRESS_FUNCTIONS = [
    // Transients.
    'get_transient',
    'set_transient',
    'delete_transient',
    'get_site_transient',
    'set_site_transient',
    'delete_site_transient',

    // Options.
    'get_option',
    'update_option',
    'delete_option',
    'get_network_option',
    'update_network_option',
    'delete_network_option',

    // Object caching.
    'wp_cache_get',
    'wp_cache_set',
    'wp_cache_delete',
    'wp_using_ext_object_cache',

    // Multisite.
    'get_current_network_id',

    // Escaping and sanitization.
    'esc_attr',
    'esc_html',
    'esc_textarea',
    'sanitize_text_field',
    'wp_kses',
    'wp_kses_allowed_html',

    // Settings API.
    'add_settings_field',

    // Markup.
    'checked',
    'selected',

    // Utility functions.
    'wp_list_pluck',
    'wp_parse_args',
];

return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'WP_Typography\Vendor',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        // The DI container needs only one file.
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName( '/' . WP_TYPOGRAPHY_EXCLUDED_FILES . '/' )
            ->exclude( [
                'Extra',
                'Loader',
                'tests',
            ] )
            ->in('vendor/level-2'),
        // Our own vendor modules.
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName( '/' . WP_TYPOGRAPHY_EXCLUDED_FILES . '/' )
            ->exclude( WP_TYPOGRAPHY_EXCLUDED_DIRS )
            ->in('vendor/mundschenk-at'),
        // The HTML parser.
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName( '/' . WP_TYPOGRAPHY_EXCLUDED_FILES . '|example\\.php|sami\\.php/' )
            ->exclude( WP_TYPOGRAPHY_EXCLUDED_DIRS )
            ->in('vendor/masterminds'),
        Finder::create()->append([
            'composer.json',
            'vendor/composer/installed.json',
        ]),
    ],

    // Whitelists a list of files. Unlike the other whitelist related features, this one is about completely leaving
    // a file untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'files-whitelist' => [
        'vendor/mundschenk-at/check-wp-requirements/class-mundschenk-wp-requirements.php',
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        function (string $file_path, string $prefix, string $contents): string {
            // Quote prefix.
            $prefix = \preg_quote( $prefix, '#' );

            if (
                // Skip partials and dummy files.
                \preg_match( '#.*/partials/\w+.*\.php$#', $file_path ) ||
                \preg_match( '#.*/includes/_language_names\.php$#', $file_path )
            ) {
                $contents = \preg_replace( "#\b{$prefix}\\\\([\w_]+\()#", '$1', $contents );
            } elseif (
                // Only for other PHP files.
                \preg_match( '#\w+/\w+.*\.php#', $file_path )
            ) {
                // Un-preix WordPress functions.
                $functions = \join( '|', WP_TYPOGRAPHY_WORDPRESS_FUNCTIONS );
                $contents  = \preg_replace( "/\b{$prefix}\\\\({$functions}\()/", '$1', $contents );
            }

            return $contents;
        },
    ],

    // PHP-Scoper's goal is to make sure that all code for a project lies in a distinct PHP namespace. However, you
    // may want to share a common API between the bundled code of your PHAR and the consumer code. For example if
    // you have a PHPUnit PHAR with isolated code, you still want the PHAR to be able to understand the
    // PHPUnit\Framework\TestCase class.
    //
    // A way to achieve this is by specifying a list of classes to not prefix with the following configuration key. Note
    // that this does not work with functions or constants neither with classes belonging to the global namespace.
    //
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        'PHP_Typography\*',
    ],

    // If `true` then the user defined constants belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-constants' => true,

    // If `true` then the user defined classes belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-classes' => true,

    // If `true` then the user defined functions belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-functions' => true,
];
