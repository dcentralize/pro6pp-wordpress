// Generated on 2013-12-27 using generator-webapp 0.4.6
'use strict';

// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// use this if you want to recursively match all subfolders:
// 'test/spec/**/*.js'

module.exports = function(grunt) {

    // Load grunt tasks automatically
    require('load-grunt-tasks')(grunt);

    // Time how long tasks take. Can help when optimizing build times
    require('time-grunt')(grunt);

    // Define the configuration for all the tasks
    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        // Watches files for changes and runs tasks based on the changed files
        watch: {
            js: {
                files: ['/js_tests/*.js', '../js/*.js'],
                tasks: ['jshint', ],
                options: {
                    livereload: true
                }
            },
            jstest: {
                files: ['js_tests/*.js'],
                tasks: ['casperjs'],
                options: {
                    interrupt: true,
                }
            },
            gruntfile: {
                files: ['Gruntfile.js'],
                task: ['jshint', ],
                options: {
                    livereload: true
                }
            },
        },
        // Make sure code styles are up to par and there are no obvious mistakes
        jshint: {
            files: ['js_tests/*.js', '../js/*.js'],
            options: {
                curly: true,
                eqeqeq: true,
                immed: true,
                latedef: true,
                newcap: true,
                noarg: true,
                sub: true,
                undef: true,
                boss: true,
                eqnull: true,
                globals: {
                    exports: true,
                    module: false
                }
            }
        },
        // The actual grunt server settings
        connect: {
            options: {
                port: 9000,
                livereload: 35729,
                // Change this to '0.0.0.0' to access the server from outside
                hostname: 'localhost'
            },
            test: {
                options: {
                    port: 9001
                }
            }
        },
        //CasperJS testing framework
        casperjs: {
            options: {
                casperjsOptions: ['--uri=127.0.0.1/wordpress', '--user=wp_tester', '--pass=1234567', '--log-level=debug', '--waitTimeout=10000'],
                async: {
                    parallel: false
                }
            },
            files: ['js_tests/**.js']
        }
    });

    grunt.loadNpmTasks('grunt-casperjs');
    grunt.registerTask('test', function(target) {
        grunt.task.run([
            'connect:test',
            'casperjs'
        ]);
    });

    grunt.registerTask('casper', [
        'casperjs',
    ]);

    grunt.registerTask('default', [
        'jshint',
        'casperjs',
    ]);

};