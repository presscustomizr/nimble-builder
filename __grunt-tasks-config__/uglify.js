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

  czr_sektions_customizer_control_js : {
    files: [{
      expand: true,
      cwd: '<%= paths.czr_assets %>sek/js/',
      src: [ 'czr-skope-base.js' ],
      dest: '<%= paths.czr_assets %>sek/js/',
      ext: '.min.js'
    }]
  }
	// any_file : {
	//	files: { '<%= uglify_requested_paths.dest %>': ['<%= uglify_requested_paths.src %>']
 //      }
	// }
};