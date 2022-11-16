<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

const WP_TYPOGRAPHY_EXCLUDED_FILES      = '.*\\.dist|Makefile|composer\\.json|composer\\.lock|phpcs\\.xml|phpunit.xml|phpbench\\.json|.*\\.md|sonar-project\\.properties';
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
                // Fix bad constant-patching in class-re.php.
                \preg_match( '#.*/php-typography/src/class-re\.php$#', $file_path )
            ) {
                $contents = \str_replace( "const NORMAL_SPACES = '{$prefix}\\\\ \\\\f\\\\n\\\\r\\\\t\\\\v';", 'const NORMAL_SPACES = \' \f\n\r\t\v\';', $contents );
            }

            if (
                // Skip partials and dummy files.
                \preg_match( '#.*/partials/\w+.*\.php$#', $file_path ) ||
                \preg_match( '#.*/includes/_language_names\.php$#', $file_path )
            ) {
                $contents = \preg_replace( "#\b{$prefix}\\\\([\w_]+\()#", '$1', $contents );
            }

            return $contents;
        },
    ],

    // Whitelists a list of files. Unlike the other whitelist related features, this one is about completely leaving
    // a file untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'exclude-files' => [],

    'exclude-namespaces' => [],
    'exclude-classes' => \array_merge(
        \json_decode( \file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-classes.json' ) ),
        \json_decode( \file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-interfaces.json' ) ),
    ),
    'exclude-functions' => \json_decode( \file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-functions.json' ) ),
    'exclude-constants' => \json_decode( \file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-constants.json' ) ),

    // Expose necessary namespaces for extensions.
    'expose-namespaces' => [
        'PHP_Typography',
    ],

    // If `true` then the user defined constants belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'expose-global-constants' => true,

    // If `true` then the user defined classes belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'expose-global-classes' => true,

    // If `true` then the user defined functions belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'expose-global-functions' => true,
];
