'use strict';

const sass = require('sass');

module.exports = function(grunt) {

    // Initialize configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        wpversion: grunt.file.read( 'wp-typography.php' ).toString().match(/Version:\s*(\d(?:\w|\.|\-)*)\s|\Z/)[1],

        eslint: {
            src: [
                'js/**/src/**/*.js',
                '!**/*.min.js',
            ]
        },

        copy: {
            main: {
                files: [{
                    expand: true,
                    nonull: true,
                    src: [
                        'readme.txt',
                        'CHANGELOG.md',
                        '*.php',
                        'includes/**',
                        'admin/**',
                        '!admin/block-editor/js/**',
                        '!**/scss/**',
                        'js/**',
                        'vendor/**/partials/**',
                    ],
                    dest: 'build/',
                    rename: function(dest, src) {
                        return dest + src.replace( /\bvendor\b/, 'vendor-scoped');
                    }
                }],
            },
            meta: {
                files: [{
                    expand: true,
                    nonull: false,
                    src: [
                        'vendor/{composer,level-2,masterminds,mundschenk-at}/**/LICENSE*',
                        'vendor/{composer,level-2,masterminds,mundschenk-at}/**/README*',
                        'vendor/{composer,level-2,masterminds,mundschenk-at}/**/CREDITS*',
                        'vendor/{composer,level-2,masterminds,mundschenk-at}/**/COPYING*',
                        'vendor/{composer,level-2,masterminds,mundschenk-at}/**/CHANGE*',
                        '!vendor/composer/package-versions-deprecated/**',
                    ],
                    dest: 'build/',
                    rename: function(dest, src) {
                        return dest + src.replace( /\bvendor\b/, 'vendor-scoped');
                    }
                }],
            }
        },

        rename: {
            vendor: {
                files: [{
                    src: "build/vendor",
                    dest: "build/vendor-scoped"
                }]
            }
        },

        clean: {
            build: ["build/*"],
            autoloader: [
              "build/composer.*",
              "build/vendor-scoped/bin",
              "build/vendor-scoped/composer/*.json",
              "build/vendor-scoped/composer/InstalledVersions.php",
              "build/vendor-scoped/composer/installed.php",
              "build/vendor-scoped/scoper-autoload.php",
              "build/vendor-scoped/dangoodman/**",
              "build/vendor-scoped/mundschenk-at/composer-for-wordpress/**"
            ],
        },

        "string-replace": {
            autoloader: {
                files: {
                    "build/": "build/vendor-scoped/composer/autoload_{classmap,psr4,static}.php",
                },
                options: {
                    replacements: [{
                        pattern: /\s+'Dangoodman\\\\ComposerForWordpress\\\\' =>\s+array\s*\([^,]+,\s*\),/g,
                        replacement: ''
                    }, {
                        pattern: /\s+'Dangoodman\\\\ComposerForWordpress\\\\.*,(?=\n)/g,
                        replacement: ''
                    }, {
                       pattern: /\s+'Composer\\\\InstalledVersions.*,(?=\n)/g,
                       replacement: ''
                    }]
                }
            },
            "composer-vendor-dir": {
                options: {
                    replacements: [
                        {
                            pattern: /"vendor-dir":\s*"vendor"/g,
                            replacement: '"vendor-dir": "vendor-scoped"'
                        },
                        {
                            pattern: /"dealerdirect\\\/phpcodesniffer-composer-installer":\s*true/g,
                            replacement: '"dealerdirect\/phpcodesniffer-composer-installer": false'
                        },
                        {
                            pattern: /"phpstan\\\/extension-installer":\s*true/g,
                            replacement: '"phpstan\/extension-installer": false'
                        },
                    ],
                },
                files: [{
                    expand: true,
                    flatten: false,
                    src: ['build/composer.json'],
                    dest: '',
                }]
            },
            "vendor-dir": {
                options: {
                    replacements: [{
                        pattern: /vendor\//g,
                        replacement: 'vendor-scoped/'
                    }],
                },
                files: [{
                    expand: true,
                    flatten: false,
                    src: ['build/**/*.php'],
                    dest: '',
                }]
            },
            namespaces: {
                options: {
                    replacements: [{
                        pattern: '', // Set later.
                        replacement: `\$1WP_Typography\\Vendor\\\$2`
                    }],
                },
                files: [{
                    expand: true,
                    flatten: false,
                    src: ['build/includes/**/*.php'],
                    dest: '',
                }]
            },
        },

        extract_language_names: {
            default: {
                files: {
                    "includes/_language_names.php": [
                        'vendor/mundschenk-at/php-typography/src/lang/*.json',
                        'vendor/mundschenk-at/php-typography/src/diacritics/*.json'
                    ],
                }
            },
        },

        wp_deploy: {
            options: {
                svn_url: "https://plugins.svn.wordpress.org/{plugin-slug}/",
                plugin_slug: 'wp-typography',
                // svn_user: 'your-wp-repo-username',
                build_dir: 'build', //relative path to your build directory
                assets_dir: 'wp-assets', //relative path to your assets directory (optional).
                max_buffer: 1024 * 1024
            },
            release: {
                // nothing
            },
            trunk: {
                options: {
                    deploy_trunk: true,
                    deploy_tag: false,
                }
            },
            assets: {
                options: {
                    deploy_trunk: false,
                    deploy_tag: false,
                }
            }
        },

        delegate: {
            sass: {
                src: ['<%= sass.dev.files.src %>**/*.scss'],
                dest: '<%= sass.dev.files.dest %>'
            }
        },

        sass: {
            options: {
                implementation: sass,
            },
            dist: {
                options: {
                    outputStyle: 'compressed',
                    sourceComments: false,
                    sourcemap: 'none'
                },
                files: [
                  {
                      expand: true,
                      cwd: 'admin/scss',
                      src: ['*.scss', '!default-styles.scss'],
                      dest: 'build/admin/css',
                      ext: '.min.css'
                  },
                  {
                      expand: true,
                      cwd: 'public/scss',
                      src: ['*.scss'],
                      dest: 'build/public/css',
                      ext: '.min.css'
                  }
                ]
            },
            dev: {
                options: {
                    outputStyle: 'expanded',
                    sourceComments: false,
                    sourceMapEmbed: true,
                },
                files: [{
                        expand: true,
                        cwd: 'admin/scss',
                        src: ['*.scss'],
                        dest: 'admin/css',
                        ext: '.css'
                    },
                    {
                        expand: true,
                        cwd: 'public/scss',
                        src: ['*.scss'],
                        dest: 'public/css',
                        ext: '.css'
                    }
                ]
            }
        },

        postcss: {
            options: {
                map: true, // inline sourcemaps.
                processors: [
                    // add vendor prefixes
                    require('autoprefixer')()
                ]
            },
            dev: {
                files: [{
                    expand: true,
                    cwd: 'admin/css',
                    src: ['**/*.css', '!default-styles.css'],
                    dest: 'admin/css',
                    ext: '.css'
                }]
            },
            dev_default_styles: {
                files: [{
                    expand: true,
                    cwd: 'admin/css',
                    src: ['default-styles.css'],
                    dest: 'admin/css',
                    ext: '.css'
                }],
                options: {
                    map: false,
                }
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: 'build/admin/css',
                    src: ['**/*.css'],
                    dest: 'build/admin/css',
                    ext: '.css'
                }]
            }
        },

        // uglify targets are dynamically generated by the minify task
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= ugtargets[grunt.task.current.target].filename %> <%= grunt.template.today("yyyy-mm-dd h:MM:ss TT") %> */\n',
                report: 'min',
            },
        },

        minify: {
            dist: {
                files: grunt.file.expandMapping(
                    [
                        'js/**/*.js',
                        '!js/**/*min.js',
                        '!js/src/**/*.js'
                    ], '', {
                    rename: function(destBase, destPath) {
                        return destBase + destPath.replace('.js', '.min.js');
                    }
                })
            },
        },

        // Create dummy tests directory during build process.
        mkdir: {
            build_tests: {
                options: {
                    create: [ 'build/tests' ],
                }
            }
        },

        // Run update scripts on build.
        exec: {
            update_iana: {
                command: "vendor/bin/update-iana.php",
            },
            // Replacement for grunt-composer
            composer_build: {
                command: "composer --quiet <%= grunt.task.current.args[0] %>",
                cwd: 'build'
            },
            composer_dev: {
                command: "composer <%= grunt.task.current.args[0] %>",
            }
        },

        compress: {
          beta: {
            options: {
              mode: 'zip',
              archive: '<%= pkg.name %>-<%= wpversion %>.zip'
            },
            files: [{
                expand: true,
                cwd: 'build/',
                src: [ '**/*' ],
                dest: '<%= pkg.name %>/',
            }],
          }
        },
    });

    // Set correct pattern for naemspace replacement.
    grunt.config(
        'string-replace.namespaces.options.replacements.0.pattern',
        new RegExp( '([^\\w\\\\]|\\B\\\\?)((?:' + grunt.config('pkg.phpPrefixNamespaces').join('|') + ')\\\\[\\w_]+)', 'g' )
    );

    // load all standard tasks
    require('load-grunt-tasks')(grunt, {
        scope: 'devDependencies'
    });

    // Load NPM scripts as tasks.
    require('grunt-load-npm-run-tasks')(grunt);

    // Extract languages from JSON files.
    grunt.registerMultiTask('extract_language_names', function() {
        this.files.forEach(function(file) {
            if ( grunt.file.exists( file.dest ) ) {
                if ( grunt.file.isFile( file.dest ) ) {
                    grunt.file.delete( file.dest );
                } else {
                    grunt.fail.fatal( "Target destination " + file.dest + " is not a file." );
                }
            }

            var result = '';

            file.src.forEach( function( src ) {
                if ( ! grunt.file.exists( src ) || ! grunt.file.isFile( src ) ) {
                    grunt.fail.warn( "Target source file " + src + " does not exist or is not a file." );
                    return;
                }

                var lang = grunt.file.readJSON( src );

                if ( undefined !== lang.language ) {
                    result += "<?php _x( '" + lang.language + "', 'language name', 'wp-typography' ); ?>\n";
                } else {
                    grunt.fail.warn( "File does not contain language, skipping." );
                }
            } );

            grunt.file.write( file.dest, result );
        });
    });

    // delegate stuff
    grunt.registerTask('delegate', function() {
        grunt.task.run(this.args.join(':'));
    });

    // dynamically generate uglify targets
    grunt.registerMultiTask('minify', function() {
        this.files.forEach(function(file) {
            var path = file.src[0],
                target = path.match(/([^.]*)\.js/)[1];

            // store some information about this file in config
            grunt.config('ugtargets.' + target, {
                path: path,
                filename: path.split('/').pop()
            });

            // create and run an uglify target for this file
            grunt.config('uglify.' + target + '.files', [{
                src: [path],
                dest: path.replace(/^(.*)\.js$/, '$1.min.js')
            }]);
            grunt.task.run('uglify:' + target);
        });
    });

    grunt.registerTask('phpcs', [
        'newer:exec:composer_dev:phpcs',
    ]);

    grunt.registerTask('build', [
        // Clean house
        'clean:build',
        // Scope dependencies
        'exec:composer_dev:scope-dependencies',
        // Rename vendor directory
        'string-replace:composer-vendor-dir',
        'rename:vendor',
        // Extract language names for translation
        'extract_language_names',
        // Update valid top-level domains
        'exec:update_iana',
        // Generate stylesheets
        'newer:sass:dist',
        'newer:postcss:dist',
        // Build scripts.
        'build-js',
        // Copy other files
        'copy:main',
        'copy:meta',
        // Use scoped dependencies
        'string-replace:namespaces',
        'exec:composer_build:build-wordpress',
        // Clean up unused packages
        'clean:autoloader',
        'string-replace:vendor-dir',
        'string-replace:autoloader',
    ]);

    grunt.registerTask('build-js', [
        'npmRun:build-clipboard',
        'npmRun:build-blocks',
        'newer:minify',
    ]);

    grunt.registerTask( 'build-beta', [
			'build',
			'compress:beta',
	] );

    grunt.registerTask('deploy', [
        'phpcs',
        'eslint',
        'build',
        'wp_deploy:release'
    ]);
    grunt.registerTask('trunk', [
        'phpcs',
        'build',
        'wp_deploy:trunk'
    ]);
    grunt.registerTask('assets', [
        'clean:build',
        'copy:main',
        'copy:meta',
        'wp_deploy:assets'
    ]);

    grunt.registerTask('default', [
        'phpcs',
        'newer:eslint',
        'extract_language_names',
        'newer:delegate:sass:dev',
        'newer:postcss:dev',
        'newer:postcss:dev_default_styles',
        'npmRun:build-clipboard',
        'newer:minify'
    ]);
};
