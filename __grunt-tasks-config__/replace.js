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
  }
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