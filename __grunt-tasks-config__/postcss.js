module.exports = {
	options: {
		map: {
			inline: false // save all sourcemaps as separate files...
		},
		processors: [
			//see browsersList defined in package.json for browsers to prefix
      // added grid:true for https://github.com/presscustomizr/nimble-builder/issues/427
			require('autoprefixer')({cascade: false, grid: true}),
			require('postcss-calc')() // fix issues with calc: postcss-calc uses reduce-css-calc to reduce CSS calc() function. see https://github.com/twbs/bootstrap/pull/26328
		]
	},
	sek_main: {
		src: '<%= paths.front_assets %>css/sek-base.css'
	},
	sek_main_rtl: {
		src: '<%= paths.front_assets %>css/sek-base-rtl.css'
	}
};