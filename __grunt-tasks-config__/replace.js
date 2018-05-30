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