module.exports = {
	options: {
		sourceMap: false,
		precision: 6,
		outputStyle: 'expanded'
	},
	sek_main: {
    options: {
        sourceMap: true
    },
		files : {
			'<%= paths.front_assets %>css/sek-base.css' : '<%= paths.front_assets %>scss/sek-base.scss',
      '<%= paths.front_assets %>css/sek-base-light.css' : '<%= paths.front_assets %>scss/sek-base-light.scss'
		}
	},
	sek_main_rtl : {
    options: {
        sourceMap: true
    },
		files : {
			'<%= paths.front_assets %>css/sek-base-rtl.css' : '<%= paths.front_assets %>scss/sek-base-rtl.scss',
      '<%= paths.front_assets %>css/sek-base-light-rtl.css' : '<%= paths.front_assets %>scss/sek-base-light-rtl.scss'
    }
	},

  // performance => use separated stylesheets for modules
  // implemented for https://github.com/presscustomizr/nimble-builder/issues/612
  sek_module_menu: {
    files : {
      '<%= paths.front_assets %>css/modules/menu-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/menu-module.scss'
    }
  },
  sek_module_post_grid: {
    files : {
      '<%= paths.front_assets %>css/modules/post-grid-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/post-grid-module.scss'
    }
  },
  sek_module_simple_form: {
    files : {
      '<%= paths.front_assets %>css/modules/simple-form-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/simple-form-module.scss'
    }
  },
  sek_module_img_slider: {
    files : {
      '<%= paths.front_assets %>css/modules/img-slider-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/img-slider-module.scss'
    }
  },
  sek_module_accordion: {
    files : {
      '<%= paths.front_assets %>css/modules/accordion-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/accordion-module.scss'
    }
  }
};