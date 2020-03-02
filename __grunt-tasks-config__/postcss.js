module.exports = {
	options: {
		map: false,
		processors: [
			//see browsersList defined in package.json for browsers to prefix
      // added grid:true for https://github.com/presscustomizr/nimble-builder/issues/427
			require('autoprefixer')({cascade: false, grid: true}),
			require('postcss-calc')() // fix issues with calc: postcss-calc uses reduce-css-calc to reduce CSS calc() function. see https://github.com/twbs/bootstrap/pull/26328
		]
	},
	sek_main: {
    options: {
      map: {
        inline: false // save all sourcemaps as separate files...
      },
    },
		src: '<%= paths.front_assets %>css/sek-base.css'
	},
	sek_main_rtl: {
    options: {
      map: {
        inline: false // save all sourcemaps as separate files...
      },
    },
		src: '<%= paths.front_assets %>css/sek-base-rtl.css'
	},

  // performance => use separated stylesheets for modules
  // implemented for https://github.com/presscustomizr/nimble-builder/issues/612
  sek_module_menu: {
    src:'<%= paths.front_assets %>css/modules/menu-module.css'
  },
  sek_module_post_grid: {
    src:'<%= paths.front_assets %>css/modules/post-grid-module.css'
  },
  sek_module_simple_form: {
    src:'<%= paths.front_assets %>css/modules/simple-form-module.css'
  },
  sek_module_img_slider: {
    src:'<%= paths.front_assets %>css/modules/img-slider-module.css'
  },
  sek_module_accordion: {
    src:'<%= paths.front_assets %>css/modules/accordion-module.css'
  }
};