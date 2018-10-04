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

  sek_front_css: {
    expand: true,
    cwd: '<%= paths.front_assets %>css/',
    src: ['sek-base.css', 'sek-base-rtl.css'],
    dest: '<%= paths.front_assets %>css/',
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