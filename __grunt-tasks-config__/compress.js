module.exports = {
	main: {
		options: {
			mode: 'zip',
			archive: './__build__/<%= pkg.name %>.zip'
		},
		expand: true,
		cwd: '__build__/<%= pkg.name %>/',
		src: ['**/*'],
		dest: '<%= pkg.name %>/'
	}
};