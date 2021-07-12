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

  // Added April 2021 for site templates
  sek_wp_comments : {
    files : {
      '<%= paths.front_assets %>css/sek-wp-comments.css' : '<%= paths.front_assets %>scss/sek-wp-comments.scss'
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
  },
  sek_module_quote: {
    files : {
      '<%= paths.front_assets %>css/modules/quote-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/quote-module.scss'
    }
  },
  sek_module_image: {
    files : {
      '<%= paths.front_assets %>css/modules/image-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/image-module.scss'
    }
  },
  sek_module_icon: {
    files : {
      '<%= paths.front_assets %>css/modules/icon-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/icon-module.scss'
    }
  },
  sek_module_social_icons: {
    files : {
      '<%= paths.front_assets %>css/modules/social-icons-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/social-icons-module.scss'
    }
  },
  sek_module_button: {
    files : {
      '<%= paths.front_assets %>css/modules/button-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/button-module.scss'
    }
  },
  sek_module_heading: {
    files : {
      '<%= paths.front_assets %>css/modules/heading-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/heading-module.scss'
    }
  },
  // pro modules
  sek_module_special_image: {
    files : {
      '<%= paths.front_assets %>css/modules/special-image-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/special-image-module.scss'
    }
  },
  sek_module_advanced_list: {
    files : {
      '<%= paths.front_assets %>css/modules/advanced-list-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/advanced-list-module.scss'
    }
  },
  sek_module_gallery: {
    files : {
      '<%= paths.front_assets %>css/modules/gallery-module.css' : '<%= paths.front_assets %>scss/stand-alone-module-generators/gallery-module.scss'
    }
  }
};