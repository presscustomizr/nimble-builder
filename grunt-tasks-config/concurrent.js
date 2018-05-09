module.exports = {
  options: {
    logConcurrentOutput: true
  },
  // hueman_dev : {
  //   tasks: [
  //     'watch:czr_hueman_concat_control_css',
  //     'watch:czr_hueman_control_js',
  //     'watch:czr_hueman_preview_js'
  //   ]
  // },
  // customizr_dev: {
  //   tasks: [
  //     'watch:czr_customizr_concat_control_css',
  //     'watch:czr_customizr_control_js',
  //     'watch:czr_customizr_preview_js'
  //   ]
  // },//'dev': [ 'watch:front_js', 'watch:front_php', 'watch:php', 'watch:skop_php', 'watch:global_js', 'watch:czr_concat_control_css', 'watch:czr_control_js', 'watch:czr_preview_js' ],
  // hueman_addons: {
  //   tasks: [
  //     'watch:czr_hueman_addons_concat_control_css',
  //     'watch:czr_hueman_addons_control_js'
  //     //'watch:czr_hueman_addons_preview_js'
  //   ]
  // },
  // hueman_pro_addons_dev : {
  //   tasks : [
  //     'watch:front_js',
  //     'watch:php',
  //     'watch:skop_php',
  //     'watch:flat_skop_php',
  //     'watch:global_js',
  //     'watch:czr_concat_control_css',
  //     'watch:czr_control_js',
  //     'watch:czr_preview_js',
  //     // 'watch:czr_customizr_concat_control_css',
  //     // 'watch:czr_customizr_control_js',
  //     // 'watch:czr_customizr_preview_js'
  //   ]
  // }
  nimble_dev : {
    tasks : [
      // 'watch:front_js',
      // 'watch:php',
      // 'watch:skop_php',
      //
      'watch:flat_skop_php',

      // 'watch:global_js',
      //
      'watch:czr_concat_control_css',
      'watch:czr_control_js',
      'watch:czr_flat_skope_js',

      // 'watch:czr_preview_js',
      // 'watch:czr_customizr_concat_control_css',
      // 'watch:czr_customizr_control_js',
      // 'watch:czr_customizr_preview_js'
    ]
  }
};