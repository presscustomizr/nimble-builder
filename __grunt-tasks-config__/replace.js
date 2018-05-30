module.exports = {
	main: {
		src: [
			'ha-fire.php'
		],
		overwrite: true,
		replacements: [ {
			from: /^.* Version: .*$/m,
			to: '* Version: <%= pkg.version %>'
		} ]
	},
	readme : {
		src: [
			'readme.md', 'readme.txt'
		],
		overwrite: true,
		replacements: [ {
			from: /^.*Stable tag: .*$/m,
			to: 'Stable tag: <%= pkg.version %>'
		} ]
	},
  lang : {
    src: [
      '<%= paths.lang %>*.po'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.* Hueman Addons v.*$/m,
      to: '"Project-Id-Version: Hueman Addons v<%= pkg.version %>\\n"'
    } ]
  },
  social_links_module_to_normal : {
    src: [
      '<%= paths.social_links_module %>/social_links_module.php'
    ],
    overwrite: true,
    replacements: [ {
      from: 'czr_fn_',// /^.* Hueman Addons v.*$/m,
      to: 'function_prefix_to_be_replaced_'// '"Project-Id-Version: Hueman Addons v<%= pkg.version %>\\n"'
    } ]
  },
  social_links_module_for_customizr_theme : {
    src: [
      '<%= paths.social_links_module %>/social_links_module.php'
    ],
    overwrite: true,
    replacements: [ {
      from: 'function_prefix_to_be_replaced_',// /^.* Hueman Addons v.*$/m,
      to: 'czr_fn_'// '"Project-Id-Version: Hueman Addons v<%= pkg.version %>\\n"'
    } ]
  },




  plug_version_one: {
    src: [
      'wordpress-font-customizer.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.* Version: .*$/m,
      to: " * Version: <%= pkg.version %>"
    } ]
  },
  plug_version_two: {
    src: [
      'wordpress-font-customizer.php'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*this -> plug_version = '.*$/m,
      to: "            $this -> plug_version = '<%= pkg.version %>';"
    } ]
  },
  stable_tag : {
    src: [
      'readme.txt', 'readme.md'
    ],
    overwrite: true,
    replacements: [ {
      from: /^.*Stable tag: .*$/m,
      to: "Stable tag: <%= pkg.version %>"
    } ]
  }


};