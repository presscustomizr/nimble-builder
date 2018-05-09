
module.exports = function(grunt) {
	var path  = require('path');
  var _     = require('lodash');
	var global_config = {
      //path to task.js files, defaults to grunt dir
      configPath: path.join(process.cwd(), 'grunt-tasks-config/'),
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
            lang : 'lang/',

            //flat skope
            flat_skope_php : 'inc/czr-skope/',
            flat_skope_czr_js : 'inc/czr-skope/assets/czr/js/',

            // the base czr fmk to be used in themes and plugins
            czr_base_fmk : 'inc/czr-base-fmk/',

            sektions: 'inc/sektions/'
        },
			tasks : {
        //'pre_czr' : ['concat:czr_control_css', 'concat:czr_control_js', 'comments:czr_base_control_js', 'lineending:czr_js', 'uglify:czr_control_js', 'uglify:czr_preview_js', 'cssmin:czr_css'],
        //This task concat the css and js base and full + strip comments + uglify + copy to the themes and plugin folders
        'build_czr_fmk' : [
            'concat:czr_fmk_control_js',
            'concat:czr_theme_control_js',

            'uglify:czr_control_js',

            'concat:czr_control_css',
            'cssmin:czr_css',

            'concat:czr_base_fmk_php',

            'copy:czr_base_fmk_in_customizr_theme',
            'copy:czr_base_fmk_in_wfc'
        ],

        'nimble_dev' : [ 'concurrent:ac_dev'],

        build : [ 'build_czr_fmk'  ]
			},
			uglify_requested_paths : {
				src : '' || grunt.option('src'),
				dest : '' || grunt.option('dest')
			}
		}
	};
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