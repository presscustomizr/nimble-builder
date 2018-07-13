module.exports = {
	options: {
		sourceMap: true,
		precision: 6,
		outputStyle: 'expanded'
	},
	sek_main: {
		files : {
			'<%= paths.front_assets %>css/sek-base.css' : '<%= paths.front_assets %>scss/sek-base.scss'
		}
	},
	sek_main_rtl : {
		files : {
			'<%= paths.front_assets %>css/sek-base-rtl.css' : '<%= paths.front_assets %>scss/sek-base-rtl.scss'
        }
	}
};