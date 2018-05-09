module.exports = {
  options : {
    force: true //This overrides this task from blocking deletion of folders outside current working dir (CWD). Use with caution.
  },
	main : ['build/**/*'],
  in_hueman_addons : [
    '../hueman-addons/**/*',
    '!../hueman-addons/build/**',
    '!../hueman-addons/grunt-tasks-config/**',
    '!../hueman-addons/lang/**',
    '!../hueman-addons/node_modules/**',
    '!../hueman-addons/.gitignore',
    '!../hueman-addons/.gitmodules',
    '!../hueman-addons/gruntfile.js',
    '!../hueman-addons/ha-fire.php',
    '!../hueman-addons/license.txt',
    '!../hueman-addons/package.json',
    '!../hueman-addons/readme.md',
    '!../hueman-addons/readme.txt'
  ],
  in_hueman_pro_theme : [
    '../../themes/hueman-pro/addons/**/*',
    '!../../themes/hueman-pro/addons/activation-key/**',
  ]
};