module.exports = {
	main: {
		src: [
			'nimble-builder.php'
		],
		overwrite: true,
		replacements: [ {
			from: /^.* Version: .*$/m,
			to: '* Version: <%= pkg.version %>'
		} ]
	},
  two: {
    src: [
      'nimble-builder.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*current_version = ".*$/m,
      to: '$current_version = "<%= pkg.version %>";'
    } ]
  },
	readme_txt : {
		src: [
			'readme.txt'
		],
		overwrite: true,
		replacements: [ {
			from: /^.*Stable tag: .*$/m,
			to: 'Stable tag: <%= pkg.version %>'
		} ]
	},
  readme_md : {
    src: [
      'readme.md'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*# Nimble Builder v.*$/m,
      to: '# Nimble Builder v<%= pkg.version %> [![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/)'
    } ]
  },




  // REPLACEMENTS BEFORE AND AFTER PROCESSING THE COPIES
  czr_fmk_namespace_from_nimble_to_customizr : {
    src: [
      '<%= paths.czr_base_fmk %>czr-base-fmk.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace Nimble;.*$/m,//
      to: 'namespace czr_fn;'
    } ]
  },

  czr_fmk_namespace_from_customizr_to_nimble : {
    src: [
      '<%= paths.czr_base_fmk %>czr-base-fmk.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace czr_fn;.*$/m,//
      to: 'namespace Nimble;'
    } ]
  },

  czr_fmk_namespace_from_nimble_to_hueman : {
    src: [
      '<%= paths.czr_base_fmk %>czr-base-fmk.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace Nimble;.*$/m,//
      to: 'namespace hu_czr_fmk;'
    } ]
  },

  czr_fmk_namespace_from_hueman_to_nimble : {
    src: [
      '<%= paths.czr_base_fmk %>czr-base-fmk.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace hu_czr_fmk;.*$/m,//
      to: 'namespace Nimble;'
    } ]
  },

  czr_fmk_namespace_from_nimble_to_wfc : {
    src: [
      '<%= paths.czr_base_fmk %>czr-base-fmk.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace Nimble;.*$/m,
      to: 'namespace wfc_czr_fmk;'
    } ]
  },

  czr_fmk_namespace_from_wfc_to_nimble : {
    src: [
      '<%= paths.czr_base_fmk %>czr-base-fmk.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace wfc_czr_fmk;.*$/m,//
      to: 'namespace Nimble;'
    } ]
  },




  skope_namespace_from_nimble_to_hueman : {
    src: [
      '<%= paths.flat_skope_php %>index.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace Nimble;.*$/m,
      to: 'namespace hueman_skp;'
    } ]
  },
  skope_namespace_from_hueman_to_nimble : {
    src: [
      '<%= paths.flat_skope_php %>index.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*namespace hueman_skp;.*$/m,//
      to: 'namespace Nimble;'
    } ]
  },


  // lang : {
  //   src: [
  //     '<%= paths.lang %>*.po'
  //   ],
  //   overwrite: true,
  //   replacements: [ {
  //     from: /^.* Hueman Addons v.*$/m,
  //     to: '"Project-Id-Version: Hueman Addons v<%= pkg.version %>\\n"'
  //   } ]
  // },
};