
module.exports = function(grunt) {
	var path  = require('path');
  var _     = require('lodash');
	var global_config = {
      //path to task.js files, defaults to grunt dir
      configPath: path.join(process.cwd(), '__grunt-tasks-config__/'),
      // auto grunt.initConfig
      init: true,
      // data passed into config ( => the basic grunt.initConfig(config) ). Can be used afterwards with < %= test % >
      data: {
        pkg: grunt.file.readJSON( 'package.json' ),
        paths : {
            // addons_php : 'addons/',
            // front_css : 'addons/assets/front/css/',
            // front_js : 'addons/assets/front/js/',
            // back_js : 'addons/assets/back/js/',
            czr_assets : 'assets/czr/',
            front_assets : 'assets/front/',

            lang : 'lang/',

            //flat skope
            flat_skope_php : 'inc/czr-skope/',
            flat_skope_czr_js : 'inc/czr-skope/assets/czr/js/',

            // the base czr fmk to be used in themes and plugins
            czr_base_fmk : 'inc/czr-base-fmk/',

            sektions: 'inc/sektions/',

            tmpl: 'tmpl/'
        },
        vars : {
          textdomain : 'nimble-builder'//@see nimble-builder.php
        },
        tasks : {
          //'pre_czr' : ['concat:czr_control_css', 'concat:czr_control_js', 'comments:czr_base_control_js', 'lineending:czr_js', 'uglify:czr_control_js', 'uglify:czr_preview_js', 'cssmin:czr_css'],
          //This task concat the css and js base and full + strip comments + uglify + copy to the themes and plugin folders
          'build_customizer_css_js_php_fmk_panel_and_preview' : [
              'concat:czr_base_fmk_php',

              'concat:czr_fmk_control_js',
              'concat:czr_theme_control_js',
              'uglify:czr_control_js',
              'uglify:czr_preview_js',

              'concat:czr_control_css',
              'cssmin:czr_css',
          ],

          'build_skope_php_js' : [
              'concat:czr_flat_skope_php',
              'concat:czr_flat_skope_js',
              'uglify:czr_flat_skope_js'
          ],

          'build_sektion_php_js_css' : [
              'concat:czr_sektions_php',

              'concat:czr_sektions_customizer_control_js',
              'uglify:czr_sektions_customizer_control_js',
              'concat:czr_sektions_customizer_preview_js',
              'uglify:czr_sektions_customizer_preview_js',
              'uglify:czr_sektions_customizer_libs_js',

              'cssmin:sek_customizer_css',

              'sass:sek_main',
              'postcss:sek_main',
              'cssmin:sek_front_css'
          ],

          build : [
            'build_customizer_css_js_php_fmk_panel_and_preview',
            'build_skope_php_js',
            'build_sektion_php_js_css',
            'replace',
            'clean',
            'copy:main',
            'addtextdomain',
            'compress'
          ],

          build_and_copy_czr_fmk : [
            'build_customizer_css_js_php_fmk_panel_and_preview',

            'replace:czr_fmk_namespace_from_nimble_to_customizr',
            'copy:czr_base_fmk_in_customizr_theme',
            'replace:czr_fmk_namespace_from_customizr_to_nimble',

            'replace:czr_fmk_namespace_from_nimble_to_hueman',
            'copy:czr_base_fmk_in_hueman_theme',
            'replace:czr_fmk_namespace_from_hueman_to_nimble',

            'replace:czr_fmk_namespace_from_nimble_to_wfc',
            'copy:czr_base_fmk_in_wfc',
            'replace:czr_fmk_namespace_from_wfc_to_nimble',

          ]
        },
        uglify_requested_paths : {
          src : '' || grunt.option('src'),
          dest : '' || grunt.option('dest')
        }
      }
	};//global_config

	// LOAD GRUNT PACKAGES AND CONFIGS
	// https://www.npmjs.org/package/load-grunt-config
	require( 'load-grunt-config' )( grunt , global_config );

	// REGISTER TASKS
	_.map( grunt.config('tasks'), function(task, name) {
		grunt.registerTask(name, task);
	});

	//DEV WATCH EVENT
	//watch is enabled only in dev mode
	grunt.event.on('watch', function(action, filepath, target) {
		var files = [
			{
				expand: true,
				cwd: '.',
				src: [
				filepath,
				]
			}
		];
		grunt.log.writeln( 'WATCH EVENT INFOS : ', grunt.task.current.name , action, filepath, target);

		if ( 'jquery.sharrre.js' == target ) {
			//if some js admin scripts have been changed in dev mode, jshint them dynamically
			grunt.config('jshint.those', [filepath]);
		}
	});
};