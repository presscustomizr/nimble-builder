module.exports = {
	options: {
		compress: {
			global_defs: {
				"DEBUG": false
		},
		dead_code: true
		},
    preserveComments: function(node, comment) {
      // preserve comments that start with a bang
      return /^!/.test( comment.value );
    },
	},

  czr_control_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.czr_base_fmk %>assets/js/',
      src: [ '_0_ccat_czr-base-fmk.js', '_1_ccat_czr-theme-fmk.js' ],
      dest: '<%= paths.czr_base_fmk %>assets/js/',
      ext: '.min.js'
    }]
  },

  czr_preview_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.czr_base_fmk %>assets/js/',
      src: [ 'czr-preview-base.js' ],
      dest: '<%= paths.czr_base_fmk %>assets/js/',
      ext: '.min.js'
    }]
  },


  czr_flat_skope_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.flat_skope_czr_js %>',
      src: [ 'czr-skope-base.js' ],
      dest: '<%= paths.flat_skope_czr_js %>',
      ext: '.min.js'
    }]
  },

  // <FRONT>
  sektions_front_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.front_assets %>js/',
      src: [ '*.js', '!*.min.js' ],
      dest: '<%= paths.front_assets %>js/',
      ext: '.min.js'
    }]
  },

  sektions_partial_front_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.front_assets %>js/partials',
      src: [ '*.js', '!*.min.js' ],
      dest: '<%= paths.front_assets %>js/partials',
      ext: '.min.js'
    }]
  },
  // sektions_front_js_stand_alone_module_scripts : {
  //   files: [{
  //     expand: true,
  //     cwd: '<%= paths.front_assets %>js/',
  //     src: [ '*.js', '!*.min.js', '!ccat-nimble-front.js', '!ccat-nimble-front.min.js' ],
  //     dest: '<%= paths.front_assets %>js/',
  //     ext: '.min.js'
  //   }]
  // },
  sektions_front_libs_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.front_assets %>js/libs/',
      src: [ '*.js', '!*.min.js', '!swiper-bundle.js' ],
      dest: '<%= paths.front_assets %>js/libs/',
      ext: '.min.js'
    }]
  },
  // </FRONT>

  czr_sektions_customizer_control_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.czr_assets %>sek/js/',
      src: [ 'ccat-sek-control.js' ],
      dest: '<%= paths.czr_assets %>sek/js/',
      ext: '.min.js'
    }]
  },

  czr_sektions_customizer_preview_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.czr_assets %>sek/js/',
      src: [ 'ccat-sek-preview.js' ],
      dest: '<%= paths.czr_assets %>sek/js/',
      ext: '.min.js'
    }]
  },

  czr_sektions_customizer_libs_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.czr_assets %>sek/js/libs/',
      src: [ '*.js', '!*.min.js' ],
      dest: '<%= paths.czr_assets %>sek/js/libs/',
      ext: '.min.js'
    }]
  }
	// any_file : {
	//	files: { '<%= uglify_requested_paths.dest %>': ['<%= uglify_requested_paths.src %>']
 //      }
	// }
};