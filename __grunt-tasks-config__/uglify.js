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
  }

 //  czr_pro_control_js : {
 //    files: [{
 //      expand: true,
 //      cwd: '<%= paths.czr_assets %>fmk/js/',
 //      src: ['czr-control-full.js'],
 //      dest: '<%= paths.czr_assets %>js',
 //      ext: '.min.js'
 //    }]
 //  },

	// any_file : {
	//	files: { '<%= uglify_requested_paths.dest %>': ['<%= uglify_requested_paths.src %>']
 //      }
	// }
};