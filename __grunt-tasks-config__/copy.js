module.exports = {

  main: {
    src:  [
      '**',
      '!bin/**',

      '!__build__/**',
      '!__grunt-tasks-config__/**',
      '!node_modules/**',
      '!tests/**',
      '!wpcs/**',

      '!.git/**',
      '!gruntfile.js',
      '!package.json',
      '!package-lock.json',
      '!.gitignore',
      '!.ftpauth',
      '!.travis.yml',
      '!travis-examples/**',
      '!phpunit.xml',
      '!readme.md',
      '!npm-debug.log',

      '!nimble_banner.png',

      '!**/*.db',
      '!patches/**',

      // czr js dev fmk
      '!assets/czr/fmk/**',
      // czr php dev
      '!inc/czr-base-fmk/_dev_php/**',

      // skope js dev fmk
      '!inc/czr-skope/assets/czr/js/_dev/**',
      // skope php dev
      '!inc/czr-skope/_dev/**',

      // sektions customizer dev js and css
      '!assets/czr/sek/js/_dev_control/**',
      '!assets/czr/sek/css/_dev_control/**',
      '!assets/czr/sek/js/_dev_preview/**',
      // sektions dev php
      '!inc/sektions/_front_dev_php/**',
      '!inc/sektions/_customizer_dev_php/**',
      //'!inc/sektions/_admin_dev_php/**',

      // front dev fmk js
      '!assets/front/js/__FRONT_JS_FMK_TO_BE_UPDATED/**',
      '!assets/front/js/_dev_front_concat/**',
      '!assets/front/js/_dev_stand_alone/**',
      '!assets/front/js/_dev_to_uglify_manually/**',

      // front dev sass
      '!assets/front/scss/**',
      '!assets/front/css/sek-base.css.map',

      // preset_sections.json was removed for #802
      '!assets/_deprecated_preset_sections.json',

      // front tests php
      '!dev_logs.php',

      // nimblizer
      '!inc/nimblizer/**',
    ],
    dest: '__build__/<%= pkg.name %>/'
  },











  // Since may 2018 => not done from the advanced-customizer plugin anymore but from here
  czr_base_fmk_in_customizr_theme : {
    expand: true,
    flatten: false,
    //filter:'isFile',
    cwd : '<%= paths.czr_base_fmk %>',
    src: [ '**', '! _dev_php/**'],
    dest: '../../themes/customizr/core/czr-base-fmk/'
  },

  czr_base_fmk_in_hueman_theme : {
    expand: true,
    flatten: false,
    //filter:'isFile',
    cwd : '<%= paths.czr_base_fmk %>',
    src: [ '**', '! _dev_php/**'],
    dest: '../../themes/hueman/functions/czr-base-fmk/'
  },

  czr_base_fmk_in_wfc : {
    expand: true,
    flatten: false,
    //filter:'isFile',
    cwd : '<%= paths.czr_base_fmk %>',
    src: [ '**', '! _dev_php/**'],
    dest: '../wordpress-font-customizer/back/czr-base-fmk/'
  },

  czr_base_fmk_in_hueman_pro_addons : {
    expand: true,
    flatten: false,
    //filter:'isFile',
    cwd : '<%= paths.czr_base_fmk %>',
    src: [ '**', '! _dev_php/**'],
    dest: '../hueman-pro-addons/inc/czr-base-fmk/'
  },




  skope_in_hueman_pro_addons : {
    expand: true,
    flatten: false,
    //filter:'isFile',
    cwd : '<%= paths.flat_skope_php %>',
    src: [ '**', '! _dev/**'],
    dest: '../hueman-pro-addons/inc/czr-skope/'
  },

  // added march 2020 for https://github.com/presscustomizr/nimble-builder/issues/626
  // sek_stand_alone_module_js_in_main_js_folder : {
  //   expand: true,
  //   //flatten: false,
  //   filter:'isFile',
  //   cwd : '<%= paths.front_assets %>js/_dev_stand_alone/',
  //   src: [ '**' ],
  //   dest: '<%= paths.front_assets %>js/',
  //   rename : function (dest, srcpath) {
  //       // var regex = new RegExp(/_/, 'g');
  //       srcpath = srcpath.replace('dev_', 'prod_');
  //       srcpath = srcpath.replace(/_/g, '-');
  //       return dest + srcpath;
  //   }
  // }

  // czr_social_links_module_in_customizr_theme : {
  //   expand: true,
  //   flatten: false,
  //   //filter:'isFile',
  //   cwd : '<%= paths.social_links_module %>',
  //   src: [ '**' ],
  //   dest: '../../themes/customizr/core/czr-modules/social-links/'
  // }

  //
  // to_hueman_addons: {
  //   src:  [
  //     '**',
  //     '!bin/**',
  //     '!build/**',
  //     '!grunt-tasks-config/**',

  //     '!node_modules/**',
  //     '!tests/**',
  //     '!wpcs/**',
  //     '!.git/**',
  //     '!.travis.yml',
  //     '!travis-examples/**',
  //     '!phpunit.xml',
  //     '!**/*.db',
  //     '!patches/**',
  //     '!.ftpauth',

  //     '!.gitignore',
  //     '!.eslintrc.js',
  //     '!package-lock.json',
  //     '!.gitmodules',
  //     '!gruntfile.js',
  //     '!ha-fire.php',
  //     '!package.json',
  //     '!readme.md',
  //     '!readme.txt',

  //     '!lang/**',
  //     '!<%= paths.czr_assets %>fmk/**',
  //     '!<%= paths.czr_assets %>js/czr-control-full.js',
  //     '!<%= paths.czr_assets %>js/czr-control-full.min.js',

  //     '!addons/activation-key/**',
  //     '!addons/pro/**',
  //     '!addons/ha-init-pro.php',
  //     '!addons/init-hueman-pro.php',
  //     //'!addons/skop/_dev/**',

  //     '!addons/assets/front/js/hph-front*',
  //     '!addons/assets/front/css/hph-front*',
  //     '!addons/assets/back/js/hs-search.js',
  //     '!addons/assets/front/js/vendors/flickity*'
  //   ],
  //   dest: '../hueman-addons/'
  // },

  // czr_js_in_hueman_addons : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>js/**', '!<%= paths.czr_assets %>js/czr-control-full.js', '!<%= paths.czr_assets %>js/czr-control-full.min.js' ],
  //   dest: '../hueman-addons/addons/assets/czr/js/'
  // },
  // czr_css_in_hueman_addons : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>css/czr-control-base.css', '<%= paths.czr_assets %>css/czr-control-base.min.css' ],
  //   dest: '../hueman-addons/addons/assets/czr/css/'
  // },


  // czr_js_in_hueman_theme : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>js/**', '!<%= paths.czr_assets %>js/czr-control-full.js', '!<%= paths.czr_assets %>js/czr-control-full.min.js' ],
  //   dest: '../../themes/hueman/assets/czr/js/'
  // },
  // czr_css_in_hueman_theme : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>css/czr-control-base.css', '<%= paths.czr_assets %>css/czr-control-base.min.css' ],
  //   dest: '../../themes/hueman/assets/czr/css/'
  // },

  // czr_js_in_hueman_pro_theme : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>js/**', '!<%= paths.czr_assets %>js/czr-control-base.js', '!<%= paths.czr_assets %>js/czr-control-base.min.js'  ],
  //   dest: '../../themes/hueman-pro/assets/czr/js/'
  // },
  // czr_css_in_hueman_pro_theme : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>css/czr-control-base.css', '<%= paths.czr_assets %>css/czr-control-base.min.css' ],
  //   dest: '../../themes/hueman-pro/assets/czr/css/'
  // },


  // to_hueman_pro : {
  //   expand: true,
  //   // flatten: true,
  //   // filter:'isFile',
  //   src: [
  //     'addons/**/*',
  //     '!addons/assets/czr/fmk/**',
  //     '!.git/**',
  //     '!node_modules/**',
  //     '!addons/assets/back/js/hs-search.js'
  //   ],
  //   dest: '../../themes/hueman-pro/'
  // },
  // to_hueman_pro_init_pro : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.addons_php %>init-hueman-pro.php'],
  //   dest: '../../themes/hueman-pro/addons/'
  // },
  // czr_js_fmk_to_wfc : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>fmk/js/_part_0_czr-control-fmk.js' ],
  //   dest: '../wordpress-font-customizer/back/needed-when-stand-alone-plugin/assets/js/'
  // },
  // czr_css_to_wfc : {
  //   expand: true,
  //   flatten: true,
  //   filter:'isFile',
  //   src: [ '<%= paths.czr_assets %>css/czr-control-base.css', '<%= paths.czr_assets %>css/czr-control-base.min.css' ],
  //   dest: '../wordpress-font-customizer/back/needed-when-stand-alone-plugin/assets/css/'
  // },
};