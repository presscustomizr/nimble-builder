module.exports = {
  php: {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.addons_php %>skop/czr-skop.php' ] // files to remove comments from
  },
  czr_base_control_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_assets %>fmk/js/czr-control-base.js'] // files to remove comments from
  },
  czr_base_preview_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_assets %>fmk/js/czr-preview-base.js'] // files to remove comments from
  },
  czr_pro_control_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_assets %>fmk/js/czr-control-full.js'] // files to remove comments from
  }
};