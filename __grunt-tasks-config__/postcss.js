module.exports = {
	sek_main: {
		options: {
			map: {
				inline: false // save all sourcemaps as separate files...
			},
			processors: [
				//see browsersList defined in package.json for browsers to prefix
				require('autoprefixer')({cascade: false}),
				require('postcss-calc')() // fix issues with calc: postcss-calc uses reduce-css-calc to reduce CSS calc() function. see https://github.com/twbs/bootstrap/pull/26328
			]
		},
		src: '<%= paths.sektions %>assets/front/css/sek-base.css'
	}
};