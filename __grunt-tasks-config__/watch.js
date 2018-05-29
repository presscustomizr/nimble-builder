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
      '<%= paths.tmpl %>**/*.php'
    ],
    tasks : [],
  },

  sektions_customizer_folder_php : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.sektions %>customizer/**/*.php'
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
      'jshint:those',
      'concat:czr_sektions_customizer_js',
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
      '<%= paths.czr_assets %>sek/czr/css/**/*.css'
    ],
    tasks : [],
  },

  sektions_front_main_js : {
    options: {
      spawn : false,
      // Start a live reload server on the default port 35729
      livereload : true
    },
    files : [
      '<%= paths.front_assets %>js/sek-main.js'
    ],
    tasks : [],
  },

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
      'postcss:sek_main'
    ]
  },
};