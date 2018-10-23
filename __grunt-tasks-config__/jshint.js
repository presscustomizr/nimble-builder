module.exports = {
	options : {
		reporter : require('jshint-stylish'),
      scripturl:true
	},
	gruntfile : ['Gruntfile.js'],
	front_js : [
    '<%= paths.front_assets %>js/_dev_front/*.js',
    '!<%= paths.front_assets %>js/_dev_front/0_0_0_front_underscore.js'
  ],
  sektion_customizer_js : [
    '<%= paths.czr_assets %>sek/js/_dev_control/*.js',
    '<%= paths.czr_assets %>sek/js/_dev_preview/*.js'
  ],
	those : [], //populated dynamically with the watch event
};