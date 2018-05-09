module.exports = {
  // gruntfile: {
	// files: 'Gruntfile.js',
	// tasks: ['jshint:gruntfile'],
	// },
  options: {
		spawn : false,
		// Start a live reload server on the default port 35729
		livereload : false
	},
	// front_js : {
	//	files : ['<%= paths.front_js %>*.js', '!*.min.js'],
	//	tasks : ['jshint:front_js','uglify:front_js'],
	//	//tasks: ['concat:front_js', 'jshint:front', 'ftp_push:those'],
	// },
	// php : {
	//	files: ['**/*.php' , '!build/**.*.php'],
	//	tasks: []
	// },
 //  skop_php : {
 //    files : [ '<%= paths.skop_dev_php %>*.php'],
 //    tasks : [ 'concat:skop_php', 'comments:php' ]
 //  },

  czr_control_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files :
      [
        '<%= paths.czr_assets %>fmk/js/base-fmk/**',
        '<%= paths.czr_assets %>fmk/js/themes/**',
      ],
    tasks : [
      'jshint:those' ,
      'concat:czr_fmk_control_js',
      'concat:czr_theme_control_js',

      'uglify:czr_control_js',

      // 'comments:czr_base_control_js',

      'copy:czr_base_fmk_in_customizr_theme',
      'copy:czr_base_fmk_in_wfc'
      // 'copy:czr_js_in_hueman_addons',
      // 'copy:czr_js_in_hueman_theme',
      // 'copy:czr_js_in_hueman_pro_theme'
    ],
  },

  czr_preview_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files :
      [ '<%= paths.czr_base_fmk %>assets/js/czr-preview-base.js' ],
    tasks : [
      'uglify:czr_preview_js',

      'copy:czr_base_fmk_in_customizr_theme'
      // 'copy:czr_js_in_hueman_addons',
      // 'copy:czr_js_in_hueman_theme',
      // 'copy:czr_js_in_hueman_pro_theme'
    ],
  },

  czr_control_css : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : ['<%= paths.czr_assets %>fmk/css/parts/*.css'],
    tasks : [
      'concat:czr_control_css',
      'cssmin:czr_css',
      'copy:czr_base_fmk_in_customizr_theme'
      // 'copy:czr_css_in_hueman_addons',
      // 'copy:czr_css_in_hueman_theme',
      // 'copy:czr_css_in_hueman_pro_theme',

    ],
  },

  czr_base_fmk_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [ '<%= paths.czr_base_fmk %>_dev_php/*.php'],
    tasks : [
        'concat:czr_base_fmk_php',
        'copy:czr_base_fmk_in_customizr_theme'
    ]
  },

  flat_skop_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [ '<%= paths.flat_skope_php %>_dev/*.php'],
    tasks : [ 'concat:czr_flat_skope_php']
  },

  czr_contextualizer_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [ '<%= paths.contextualizer_php %>_dev/*.php'],
    tasks : [ 'concat:czr_contextualizer_php']
  },
  // global_js : {
  //   files : ['<%= paths.global_js %>*.js', '!*.min.js'],
  //   tasks : [ 'jshint:global_js', 'uglify:global_js'],
  // },

  // czr_min_copy_control_css : {
  //   files : ['<%= paths.czr_assets %>fmk/css/czr-control.css'],
  //   tasks : ['cssmin:czr_css', 'copy:czr_css'],
  // },


  czr_flat_skope_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.flat_skope_czr_js %>_dev/**/*.js'
    ],
    tasks : [
      'jshint:those',
      'concat:czr_flat_skope_js',
    ],
  },

  czr_contextualizer_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.contextualizer_czr_js %>_dev/**/*.js'
    ],
    tasks : [
      'jshint:those',
      'concat:czr_contextualizer_js',
    ],
  },

  social_links_module : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.social_links_module %>/assets/**'
    ],
    tasks : [
      //'jshint:those',
      //'concat:czr_contextualizer_js',
      'replace:social_links_module_for_customizr_theme',
      'copy:czr_social_links_module_in_customizr_theme',
      'replace:social_links_module_to_normal'
    ],
  },




  sektions_dev_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>_dev_php/**/*.php'
    ],
    tasks : [
      'concat:czr_sektions_php',
    ],
  },

  sektions_tmpl_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>tmpl/**/*.php'
    ],
    tasks : [],
  },

  sektions_customizer_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>assets/czr/js/_dev_control/**/*.js'
    ],
    tasks : [
      'jshint:those',
      'concat:czr_sektions_customizer_js',
    ],
  },

  sektions_preview_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>assets/czr/js/_dev_preview/**/*.js'
    ],
    tasks : [
      'jshint:those',
      'concat:czr_sektions_customizer_preview_js',
    ],
  },

  sektions_czr_css : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>assets/czr/css/**/*.css'
    ],
    tasks : [],
  },

  sektions_font_main_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>assets/front/js/sek-main.js'
    ],
    tasks : [],
  },

  sektions_front_fmk_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>assets/front/js/_front_js_fmk/**/*.js'
    ],
    tasks : [
      'jshint:those',
      'concat:czr_sektions_front_fmk_js',
    ],
  },

  sektions_modules : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [ '<%= paths.sektions %>modules/**/*' ],
    tasks : []
  },

  sektions_front_main_css : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>assets/front/scss/**/*.scss'
    ],
    tasks : [
      'sass:sek_main',
      'postcss:sek_main'
    ]
  },

  // //Other admin js assets are jshinted on change
  // czr_preview_js : {
  //   files : ['<%= paths.czr_assets %>fmk/js/czr-preview-base.js'],
  //   tasks : ['jshint:those', 'uglify:czr_preview_js', 'copy:czr_js', 'copy:czr_js_in_hueman_addons', 'copy:czr_js_in_hueman_theme', 'copy:czr_js_in_hueman_pro_theme'],
  // },


  // /** HUEMAN THEME **/
  // czr_hueman_concat_control_css : {
  //   files : [
  //     '<%= paths.czr_assets %>fmk/css/parts/*.css',

  //     //exclude concatenated files from watch
  //     '!<%= paths.czr_assets %>fmk/css/czr-control-base.css',
  //   ],
  //   tasks : [
  //     'concat:czr_control_css',
  //     'cssmin:czr_css',
  //     'copy:czr_css',
  //     'copy:czr_css_in_hueman_theme'
  //   ],
  // },

  // czr_hueman_control_js : {
  //   options: {
  //     spawn : false,
  //     // Start a live reload server on the default port 35729
  //     livereload : true
  //   },
  //   files : [
  //     '<%= paths.czr_assets %>fmk/js/control_dev/**/*.js',
  //     '!<%= paths.global_js %>*.js',
  //     '!*.min.js',

  //     //exclude concatenated files from watch
  //     '! <%= paths.czr_assets %>fmk/js/czr-control-base.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-control-full.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-preview-base.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-pro-modules-control.js',
  //   ],
  //   tasks : [
  //     'jshint:those',
  //     'uglify:global_js',

  //     'concat:czr_fmk_control_js',
  //     //'concat:czr_skope_control_js',
  //     'concat:czr_theme_control_js',

  //     'concat:czr_base_control_js',
  //     'concat:czr_pro_modules_control_js',
  //     'concat:czr_pro_control_js',
  //     'copy:czr_js',
  //     'comments:czr_base_control_js',
  //     'comments:czr_base_preview_js',
  //     'copy:czr_js_in_hueman_theme',
  //   ],
  // },
  // //Other admin js assets are jshinted on change
  // czr_hueman_preview_js : {
  //   files : ['<%= paths.czr_assets %>fmk/js/czr-preview-base.js'],
  //   tasks : [
  //     'jshint:those',
  //     'comments:czr_base_preview_js',
  //     'uglify:czr_preview_js',
  //     'copy:czr_js',
  //     'copy:czr_js_in_hueman_theme'
  //   ],
  // },



  // /** HUEMAN ADDONS FREE **/
  // czr_hueman_addons_concat_control_css : {
  //   files : [
  //     '<%= paths.czr_assets %>fmk/css/parts/*.css',
  //     //exclude concatenated files from watch
  //     '!<%= paths.czr_assets %>fmk/css/czr-control-base.css',
  //   ],
  //   tasks : [
  //     'concat:czr_control_css',
  //     'cssmin:czr_css',
  //     'copy:czr_css',

  //     'copy:czr_css_in_hueman_theme',
  //     'copy:czr_css_in_hueman_addons',
  //   ],
  // },

  // czr_hueman_addons_control_js : {
  //   options: {
  //     spawn : false,
  //     // Start a live reload server on the default port 35729
  //     livereload : true
  //   },
  //   files : [
  //     '<%= paths.czr_assets %>fmk/js/control_dev/**/*.js',
  //     '!<%= paths.global_js %>*.js',
  //     '!*.min.js',

  //     //exclude concatenated files from watch
  //     '! <%= paths.czr_assets %>fmk/js/czr-control-base.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-control-full.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-preview-base.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-pro-modules-control.js',
  //   ],
  //   tasks : [
  //     'jshint:those',
  //     'uglify:global_js',
  //     'concat:czr_fmk_control_js',
  //     //'concat:czr_skope_control_js',
  //     'concat:czr_theme_control_js',

  //     'concat:czr_base_control_js',
  //     'concat:czr_pro_modules_control_js',
  //     'concat:czr_pro_control_js',
  //     'copy:czr_js',
  //     'comments:czr_base_control_js',
  //     'copy:czr_js_in_hueman_theme',
  //     'copy:czr_js_in_hueman_addons',
  //   ],
  // },




  // /** CUSTOMIZR THEME **/
  // czr_customizr_concat_control_css : {
  //   files : [
  //     '<%= paths.czr_assets %>fmk/css/parts/*.css',

  //     //exclude concatenated files from watch
  //     '!<%= paths.czr_assets %>fmk/css/czr-control-base.css',
  //   ],
  //   tasks : [
  //     'concat:czr_control_css',
  //     'cssmin:czr_css',
  //     'copy:czr_css',
  //     'copy:czr_css_in_customizr_theme'
  //   ],
  // },

  // czr_customizr_control_js : {
  //   options: {
  //     spawn : false,
  //     // Start a live reload server on the default port 35729
  //     livereload : true
  //   },
  //   files : [
  //     '<%= paths.czr_assets %>fmk/js/control_dev/**/*.js',
  //     '!<%= paths.global_js %>*.js',
  //     '!*.min.js',

  //     //exclude concatenated files from watch
  //     '! <%= paths.czr_assets %>fmk/js/czr-control-base.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-control-full.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-preview-base.js',
  //     '! <%= paths.czr_assets %>fmk/js/czr-pro-modules-control.js',
  //   ],
  //   tasks : [
  //     'jshint:those',
  //     'uglify:global_js',

  //     'concat:czr_fmk_control_js',
  //     //'concat:czr_skope_control_js',
  //     'concat:czr_theme_control_js',

  //     'concat:czr_base_control_js',
  //     'concat:czr_pro_modules_control_js',
  //     'concat:czr_pro_control_js',
  //     'copy:czr_js',
  //     'comments:czr_base_control_js',
  //     'comments:czr_base_preview_js',
  //     'copy:czr_js_in_customizr_theme',
  //   ],
  // },
  // //Other admin js assets are jshinted on change
  // czr_customizr_preview_js : {
  //   files : ['<%= paths.czr_assets %>fmk/js/czr-preview-base.js'],
  //   tasks : [
  //     'jshint:those',
  //     'uglify:czr_preview_js',
  //     'copy:czr_js',
  //     'copy:czr_js_in_customizr_theme'
  //   ],
  // }
};