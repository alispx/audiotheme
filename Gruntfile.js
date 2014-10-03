/*jshint node:true */

module.exports = function(grunt) {
	'use strict';

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		version: '<%= pkg.version %>',

		/**
		 * Autoprefix CSS files.
		 */
		autoprefixer: {
			options: {
				browsers: ['> 1%', 'last 2 versions', 'ff 17', 'opera 12.1', 'android 4']
			},
			dist: {
				files: [
					{ src: 'admin/css/admin.min.css' },
					{ src: 'admin/css/jquery-ui-audiotheme.min.css' },
					{ src: 'includes/css/audiotheme.min.css' }
				]
			}
		},

		/**
		 * Check JavaScript for errors and warnings.
		 */
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'admin/js/*.js',
				'!admin/js/*.min.js',
				'admin/js/*.js',
				'!admin/js/*.min.js',
				'includes/js/*.js',
				'!includes/js/*.min.js'
			]
		},

		/**
		 * Minimize CSS files.
		 */
		cssmin: {
			dist: {
				files: [
					{ src: 'admin/css/admin.min.css', dest: 'admin/css/admin.min.css' },
					{ src: 'admin/css/jquery-ui-audiotheme.min.css', dest: 'admin/css/jquery-ui-audiotheme.min.css' },
					{ src: 'includes/css/audiotheme.min.css', dest: 'includes/css/audiotheme.min.css' }
				]
			}
		},

		/**
		 * Compile Sass style sheets.
		 */
		sass: {
			dist: {
				files: [
					{ src: 'admin/css/sass/admin.scss', dest: 'admin/css/admin.min.css' },
					{ src: 'admin/css/sass/jquery-ui-audiotheme.scss', dest: 'admin/css/jquery-ui-audiotheme.min.css' },
					{ src: 'includes/css/sass/audiotheme.scss', dest: 'includes/css/audiotheme.min.css' }
				]
			}
		},

		/**
		 * Minify JavaScript source files.
		 */
		uglify: {
			dist: {
				files: [
					{ src: 'admin/js/admin.js', dest: 'admin/js/admin.min.js' },
					{ src: 'admin/js/media.js', dest: 'admin/js/media.min.js' }
				]
			}
		},

		/**
		 * Watch sources files and compile when they're changed.
		 */
		watch: {
			js: {
				files: ['<%= jshint.all %>'],
				tasks: ['jshint', 'uglify']
			},
			sass: {
				files: ['includes/css/**/*.scss', 'admin/css/**/*.scss'],
				tasks: ['sass', 'autoprefixer', 'cssmin']
			}
		},

		/**
		 * Archive the plugin in the /release directory, excluding development
		 * and build related files.
		 *
		 * The zip archive will be named: audiotheme-plugin-{{version}}.zip
		 */
		compress: {
			build: {
				options: {
					archive: 'release/<%= pkg.name %>-plugin-<%= version %>.zip'
				},
				files: [
					{
						src: [
							'**',
							'!node_modules/**',
							'!release/**',
							'!tests/**',
							'!.jshintrc',
							'!config.json',
							'!Gruntfile.js',
							'!package.json',
							'!phpunit.xml',
							'!README.md'
						],
						dest: '<%= pkg.name %>/'
					}
				]
			}
		},

		makepot: {
			build: {
				options: {
					exclude: ['.git/.*', 'node_modules/.*', 'release/.*', 'tests/.*'],
					mainFile: 'audiotheme.php',
					potHeaders: {
						'report-msgid-bugs-to': 'https://audiotheme.com/support/',
						poedit: true
					},
					type: 'wp-plugin'
				}
			}
		},

		/**
		 * Replace version numbers during a build.
		 */
		'string-replace': {
			build: {
				options: {
					replacements: [{
						pattern: /Version: .+/,
						replacement: 'Version: <%= version %>'
					}, {
						pattern: /@version .+/,
						replacement: '@version <%= version %>'
					}, {
						pattern: /'AUDIOTHEME_VERSION', '[^']+'/,
						replacement: '\'AUDIOTHEME_VERSION\', \'<%= version %>\''
					}]
				},
				files: {
					'audiotheme.php': 'audiotheme.php'
				}
			},
			release: {
				options: {
					replacements: [{
						pattern: /@since x\.x\.x/g,
						replacement: '@since <%= version %>'
					}]
				},
				files: [
					{
						src: [
							'*.php',
							'**/*.php'
						],
						dest: './'
					}
				]
			}
		}

	});

	/**
	 * Default task.
	 */
	grunt.registerTask( 'default', ['jshint', 'uglify', 'sass', 'autoprefixer', 'cssmin', 'watch'] );

	/**
	 * Build a release.
	 *
	 * Bumps version numbers. Defaults to the version set in package.json, but a
	 * specific version number can be passed as the first argument.
	 * Ex: grunt release:1.2.3
	 *
	 * The project is then zipped into an archive in the release directory,
	 * excluding unnecessary source files in the process.
	 */
	grunt.registerTask('build', function(arg1) {
		var pkg = grunt.file.readJSON('package.json'),
			version = 0 === arguments.length ? pkg.version : arg1;

		grunt.config.set('version', version);
		grunt.task.run('string-replace:build');
		grunt.task.run('jshint');
		grunt.task.run('sass');
		grunt.task.run('autoprefixer');
		grunt.task.run('cssmin');
		grunt.task.run('uglify');
		grunt.task.run('makepot');
		grunt.task.run('compress:build');
	});

	/**
	 * Release a new version.
	 *
	 * Builds a release and pushes it to the remote git repo and uploads it to
	 * the production server.
	 *
	 * @todo "@since x.x.x" tags are also replaced with the new version number.
	 */
	grunt.registerTask('release', function(arg1) {
		var pkg = grunt.file.readJSON('package.json'),
			version = 0 === arguments.length ? pkg.version : arg1;

		grunt.config.set('version', version);
		grunt.task.run('build:' + version);
		grunt.task.run('string-replace:release');
	});

};
