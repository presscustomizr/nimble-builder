module.exports = {
  sektions_front_php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: ['<%= paths.sektions %>ccat-constants-and-helper-functions.php', '<%= paths.sektions %>ccat-sektions-base.php' ] // files to remove comments from
  },
  sektions_admin_php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '__build__/nimble-builder/inc/admin/nimble-admin.php' ] // files to remove comments from
  },
  sektions_customizer_php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.sektions %>ccat-czr-sektions.php' ] // files to remove comments from
  },
  czr_base_fmk_php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.sektions %>czr-base-fmk.php' ] // files to remove comments from
  },
  czr_base_fmk_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_base_fmk %>assets/js/_0_ccat_czr-base-fmk.js'] // files to remove comments from
  },
  czr_theme_fmk_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_base_fmk %>assets/js/_1_ccat_czr-theme-fmk.js'] // files to remove comments from
  },
  czr_preview_base_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_base_fmk %>assets/js/czr-preview-base.js'] // files to remove comments from
  },

  czr_skope_php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ 'inc/czr-skope/index.php' ] // files to remove comments from
  },
  czr_skope_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ 'inc/czr-skope/assets/czr/js/czr-skope-base.js'] // files to remove comments from
  },

  sektions_front_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.front_assets %>js/ccat-nimble-front.js'] // files to remove comments from
  },
  czr_base_control_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_assets %>sek/js/ccat-sek-control.js'] // files to remove comments from
  },
  czr_base_preview_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_assets %>sek/js/ccat-sek-preview.js'] // files to remove comments from
  },
  // czr_pro_control_js : {
  //   // Target-specific file lists and/or options go here.
  //   options: {
  //       singleline: true,
  //       multiline: false
  //   },
  //   src: [ '<%= paths.czr_assets %>fmk/js/czr-control-full.js'] // files to remove comments from
  // }
};