module.exports = {
  php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.sektions %>ccat-sektions.php' ] // files to remove comments from
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