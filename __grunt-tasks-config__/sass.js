module.exports = {
	sek_main: {
		options: {
			sourceMap: true,
			precision: 6,
			outputStyle: 'expanded'
		},
		files : {
			'<%= paths.front_assets %>css/sek-base.css' : '<%= paths.front_assets %>scss/sek-base.scss'
		}
	}
};