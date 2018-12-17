module.exports = {
  options : {
    force: true //This overrides this task from blocking deletion of folders outside current working dir (CWD). Use with caution.
  },
	main : ['__build__/**/*'],
  czr_base_fmk_in_customizr_theme : ['../../themes/customizr/core/czr-base-fmk/'],
  czr_base_fmk_in_hueman_theme : ['../../themes/hueman/functions/czr-base-fmk/'],
  czr_base_fmk_in_hueman_pro_addons : ['../hueman-pro-addons/inc/czr-base-fmk/'],
  czr_base_fmk_in_wfc : ['../wordpress-font-customizer/back/czr-base-fmk/'],
  skope_in_hueman_pro_addons: ['../hueman-pro-addons/inc/czr-skope/']
  // in_hueman_addons : [
  //   '../hueman-addons/**/*',
  //   '!../hueman-addons/build/**',
  //   '!../hueman-addons/grunt-tasks-config/**',
  //   '!../hueman-addons/lang/**',
  //   '!../hueman-addons/node_modules/**',
  //   '!../hueman-addons/.gitignore',
  //   '!../hueman-addons/.gitmodules',
  //   '!../hueman-addons/gruntfile.js',
  //   '!../hueman-addons/ha-fire.php',
  //   '!../hueman-addons/license.txt',
  //   '!../hueman-addons/package.json',
  //   '!../hueman-addons/readme.md',
  //   '!../hueman-addons/readme.txt'
  // ],
  // in_hueman_pro_theme : [
  //   '../../themes/hueman-pro/addons/**/*',
  //   '!../../themes/hueman-pro/addons/activation-key/**',
  // ]
};