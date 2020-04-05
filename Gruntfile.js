'use strict';

const sass = require('sass');

module.exports = function(grunt) {

    // Initialize configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        wpversion: grunt.file.read( 'wp-typography.php' ).toString().match(/Version:\s*([0-9](?:\w|\.|\-)*)\s|\Z/)[1],

        eslint: {
            src: [
                'js/**/*.js',
                '!**/*.min.js',
            ]
        },

        composer : {
            build : {
                options : {
                    flags: ['quiet'],
                    cwd: 'build',
                },
            },
            dev : {
                options : {
                    flags: [],
                    cwd: '.',
                },
            },
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
            autoloader: [ "build/composer.*", "build/vendor-scoped/composer/*.json", "build/vendor-scoped/scoper-autoload.php", "build/vendor-scoped/mundschenk-at/composer-for-wordpress/**" ]
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
                        pattern: 'Dangoodman',
                        replacement: 'FOOBAR'
                    }]
                }
            },
            "composer-vendor-dir": {
                options: {
                    replacements: [{
                        pattern: /"vendor-dir":\s*"vendor"/g,
                        replacement: '"vendor-dir": "vendor-scoped"'
                    }],
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
            fix_dice_namespace: {
                options: {
                    replacements: [ {
                        pattern: /use Dice\\Dice;/g,
                        replacement: 'use WP_Typography\\Vendor\\Dice\\Dice;'
                    } ],
                },
                files: [ {
                    expand: true,
                    flatten: false,
                    src: ['build/includes/class-wp-typography-factory.php'],
                    dest: '',
                } ]
            },
            fix_mundschenk_namespace: {
                options: {
                    replacements: [ {
                        pattern: /(\b\\?)(Mundschenk\\[\w_]+)/g,
                        replacement: '$1WP_Typography\\Vendor\\$2'
                    } ],
                },
                files: [ {
                    expand: true,
                    flatten: false,
                    src: ['build/includes/**/*.php'],
                    dest: '',
                } ]
            }
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
                files: grunt.file.expandMapping(['js/**/*.js', '!js/**/*min.js'], '', {
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
        },

        regex_extract: {
            options: {

            },
            language_names: {
                options: {
                    regex: '"language"\\s*:\\s*.*("|\')([\\w\u0080-\u00FF() ]+)\\1',
                    modifiers: 'g',
                    output: "<?php _x( '$2', 'language name', 'wp-typography' ); ?>",
                    verbose: false,
                    includePath: false
                },
                files: {
                    "includes/_language_names.php": [
                        'vendor/mundschenk-at/php-typography/src/lang/*.json',
                        'vendor/mundschenk-at/php-typography/src/diacritics/*.json'
                    ],
                }
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

    // load all standard tasks
    require('load-grunt-tasks')(grunt, {
        scope: 'devDependencies'
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
        'composer:dev:phpcs',
    ]);

    grunt.registerTask('build', [
        // Clean house
        'clean:build',
        // Scope dependencies
        'composer:dev:scope-dependencies',
        // Rename vendor directory
        'string-replace:composer-vendor-dir',
        'rename:vendor',
        // Extract language names for translation
        'regex_extract:language_names',
        // Update valid top-level domains
        'exec:update_iana',
        // Generate stylesheets
        'newer:sass:dist',
        'newer:postcss:dist',
        'newer:minify',
        // Copy other files
        'copy:main',
        'copy:meta',
        // Use scoped dependencies
        'string-replace:fix_dice_namespace',
        'string-replace:fix_mundschenk_namespace',
        'composer:build:build-wordpress',
        'clean:autoloader',
        'string-replace:vendor-dir',
        'string-replace:autoloader'
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
        'regex_extract:language_names',
        'newer:delegate:sass:dev',
        'newer:postcss:dev',
        'newer:postcss:dev_default_styles',
        'newer:minify'
    ]);
};
