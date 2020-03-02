module.exports = {
  options: {
    // compatibility: {
    //     properties: {
    //         spaceAfterClosingBrace: true
    //     }
    // }
  },
  czr_css: {
    expand: true,
    cwd: '<%= paths.czr_base_fmk %>assets/css',
    src: ['*.css', '!*.min.css'],
    dest: '<%= paths.czr_base_fmk %>assets/css',
    ext: '.min.css'
  },
  czr_css_fmk_fonts : {
    expand: true,
    cwd: '<%= paths.czr_base_fmk %>assets/fonts/css',
    src: ['*.css', '!*.min.css'],
    dest: '<%= paths.czr_base_fmk %>assets/fonts/css',
    ext: '.min.css'
  },

  sek_customizer_css: {
    expand: true,
    cwd: '<%= paths.czr_assets %>sek/css/',
    src: ['*.css', '!*.min.css'],
    dest: '<%= paths.czr_assets %>sek/css/',
    ext: '.min.css'
  },

  sek_front_main_css: {
    expand: true,
    cwd: '<%= paths.front_assets %>css/',
    src: ['*.css', '!*.min.css'],
    dest: '<%= paths.front_assets %>css/',
    ext: '.min.css'
  },
  // added for https://github.com/presscustomizr/nimble-builder/issues/612
  sek_front_modules_css: {
    expand: true,
    cwd: '<%= paths.front_assets %>css/modules/',
    src: ['*.css', '!*.min.css'],
    dest: '<%= paths.front_assets %>css/modules/',
    ext: '.min.css'
  },
  sek_front_fonts_css: {
    expand: true,
    cwd: '<%= paths.front_assets %>fonts/css/',
    src: ['*.css', '!*.min.css'],
    dest: '<%= paths.front_assets %>fonts/css/',
    ext: '.min.css'
  }
};