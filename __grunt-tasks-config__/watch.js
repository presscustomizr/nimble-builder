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

      'build_and_copy_czr_fmk'

      // 'comments:czr_base_control_js',

      //'copy:czr_base_fmk_in_customizr_theme',
      //'copy:czr_base_fmk_in_wfc'
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
      'build_and_copy_czr_fmk'
      //'copy:czr_base_fmk_in_customizr_theme'
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
      'build_and_copy_czr_fmk'
      //'copy:czr_base_fmk_in_customizr_theme'
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
        'build_and_copy_czr_fmk'
        //'copy:czr_base_fmk_in_customizr_theme'
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










  sektions_front_dev_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>_front_dev_php/**/*.php'
    ],
    tasks : [
      'concat:czr_sektions_front_php',
      'comments:sektions_front_php'
    ],
  },

  sektions_customizer_dev_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>_customizer_dev_php/**/*.php'
    ],
    tasks : [
      'concat:czr_sektions_customizer_php',
      'comments:sektions_customizer_php'
    ],
  },

  sektions_tmpl_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.tmpl %>**/*.php'
    ],
    tasks : [],
  },

  sektions_admin_folder_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      'inc/admin/**/*.php'
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
      '<%= paths.czr_assets %>sek/js/_dev_control/**/*.js'
    ],
    tasks : [
      'jshint:sektion_customizer_js',
      'concat:czr_sektions_customizer_control_js',
      'comments:czr_base_control_js'
    ],
  },

  sektions_customizer_libs_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.czr_assets %>sek/js/libs/**/*.js'
    ],
    tasks : [],
  },

  sektions_preview_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.czr_assets %>sek/js/_dev_preview/**/*.js'
    ],
    tasks : [
      'jshint:sektion_customizer_js',
      'concat:czr_sektions_customizer_preview_js',
      'comments:czr_base_preview_js'
    ],
  },

  sektions_czr_css : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.czr_assets %>sek/css/**/*.css'
    ],
    tasks : [],
  },

  // sektions_front_main_js : {
  //   options: {
  //     spawn : false,
  //     // Start a live reload server on the default port 35729
  //     livereload : true
  //   },
  //   files : [
  //     '<%= paths.front_assets %>js/sek-main.js'
  //   ],
  //   tasks : [],
  // },

  // sektions_front_fmk_js : {
  //   options: {
  //     spawn : false,
  //     // Start a live reload server on the default port 35729
  //     livereload : true
  //   },
  //   files : [
  //     '<%= paths.front_assets %>js/_front_js_fmk/**/*.js',
  //     '<%= paths.front_assets %>js/_parts/**/*.js',
  //     '<%= paths.front_assets %>js/libs/jquery-plugins/**/*.js'
  //   ],
  //   tasks : [
  //     'jshint:those',
  //     'concat:czr_sektions_front_fmk_js',
  //   ],
  // },

  sektions_front_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.front_assets %>js/_dev_front/**/*.js',
    ],
    tasks : [
      'jshint:front_js',
      'concat:sektions_front_js',
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
      '<%= paths.front_assets %>scss/**/*.scss'
    ],
    tasks : [
      'sass:sek_main',
      'postcss:sek_main',
      'sass:sek_main_rtl',
      'postcss:sek_main_rtl',
    ]
  },

  nimble_main_php_file : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [ 'nimble-builder.php' ],
    tasks : []
  },

  admin_css_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      'assets/admin/css/*.css','assets/admin/js/*.js'
    ],
    tasks : [],
  }
};