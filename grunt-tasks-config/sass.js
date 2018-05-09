module.exports = {
	sek_main: {
		options: {
			sourceMap: true,
			precision: 6,
			outputStyle: 'expanded'
		},
		files : {
			'<%= paths.sektions %>assets/front/css/sek-base.css' : '<%= paths.sektions %>assets/front/scss/sek-base.scss'
		}
	}
};