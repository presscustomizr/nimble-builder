
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
            czr_assets : 'inc/assets/czr/',
            lang : 'lang/',

            //flat skope
            flat_skope_php : 'inc/czr-skope/',
            flat_skope_czr_js : 'inc/czr-skope/assets/czr/js/',

            // contextualizer
            contextualizer_php : 'inc/contextualizer/',
            contextualizer_czr_js : 'inc/contextualizer/assets/czr/js/',

            // social module
            social_links_module : 'inc/czr-modules/social-links/',

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

            // 'uglify:global_js',

            // 'concat:czr_control_css',

            // 'concat:czr_fmk_control_js',
            // 'concat:czr_skope_control_js',
            // 'concat:czr_theme_control_js',

            // 'concat:czr_base_control_js',

            // 'concat:czr_pro_modules_control_js',
            // 'concat:czr_pro_control_js',

            // //'comments:czr_base_control_js',
            // 'comments:czr_pro_control_js',
            // 'comments:czr_base_preview_js',

            // 'uglify:czr_control_js',
            // 'uglify:czr_pro_control_js',
            // 'uglify:czr_preview_js',

            // 'cssmin:czr_css',

            // //local copies
            // 'copy:czr_css',
            // 'copy:czr_js',

            //remote copies
            // 'copy:czr_js_in_hueman_addons',
            // 'copy:czr_css_in_hueman_addons',

            // 'copy:czr_js_in_hueman_theme',
            // 'copy:czr_css_in_hueman_theme',

            // 'copy:czr_js_in_hueman_pro_theme',
            // 'copy:czr_css_in_hueman_pro_theme',

            // 'copy:czr_js_in_customizr_theme',
            // 'copy:czr_css_in_customizr_theme',

            // WordPress Font Customizer
            // 'copy:czr_js_fmk_to_wfc',
            // 'copy:czr_css_to_wfc'
        ],


        'build_social_module_to_customizr_theme' : [
            'replace:social_links_module_for_customizr_theme',
            'copy:czr_social_links_module_in_customizr_theme',
            'replace:social_links_module_to_normal'
        ],

				//'build':  [ 'skop_php', 'jshint:front_js','uglify:front_js', 'pre_czr', 'replace', 'clean', 'copy', 'compress'],
				//PROD
				//'build':  [ 'skop_php', 'jshint:front_js','uglify:front_js', 'pre_czr', 'replace', 'clean', 'copy', 'compress'],

        /** For Advanced Customizer */
        'ac_dev' : [ 'concurrent:ac_dev'],

        /** For Hueman Pro Addons */
        // 'hueman_pro_addons_dev' : [ 'concurrent:hueman_pro_addons_dev'],

        /** For Hueman Addons Free Plugin **/
        //'hueman_addons_dev': [ 'concurrent:hueman_addons' ],

        /** For Hueman theme **/
        //'hueman_dev': [ 'concurrent:hueman_dev' ],

        /** For Customizr theme**/
        //'customizr_dev': [ 'concurrent:customizr_dev' ],

        /* PRE BUILD TASKS */
        // 'pre_base_czr_fmk' : [
        //     'uglify:global_js',

        //     'concat:czr_control_css',

        //     'concat:czr_fmk_control_js',
        //     'concat:czr_skope_control_js',
        //     'concat:czr_theme_control_js',

        //     'concat:czr_base_control_js',

        //     //'comments:czr_base_control_js',

        //     'lineending:czr_js',

        //     'uglify:czr_control_js',
        //     'uglify:czr_preview_js',

        //     'cssmin:czr_css'
        // ],

        // 'pre_pro_czr_fmk' : [
        //     'uglify:global_js',

        //     'concat:czr_control_css',
        //     'concat:czr_pro_control_js',

        //     //'comments:czr_base_control_js',
        //     'comments:czr_pro_control_js',

        //     'lineending:czr_js',

        //     'uglify:czr_pro_control_js',
        //     'uglify:czr_preview_js',

        //     'cssmin:czr_css'
        // ],

        /* BUILD TASKS FOR HUEMAN PRO */
        // 'hueman_pro_build': [
        //     'pre_base_czr_fmk',
        //     'pre_pro_czr_fmk',
        //     'copy:czr_css',
        //     'copy:czr_js'
        // ],

        // /* BUILD TASKS FOR HUEMAN ADDONS */
        // 'hueman_addons_build': [
        //     'pre_base_czr_fmk',
        //     'replace',
        //     'copy:czr_css',
        //     'copy:czr_js'
        // ],

        // /* BUILD TASKS FOR CUSTOMIZR */
        // 'customizr_build_czr': [
        //     'pre_base_czr_fmk',
        //     'replace',
        //     'copy:czr_css',
        //     'copy:czr_js',
        //     'copy:czr_css_in_customizr_theme',
        //     'copy:czr_js_in_customizr_theme'
        // ]
        build : [ 'build_czr_fmk', 'build_social_module_to_customizr_theme' ]
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