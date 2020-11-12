module.exports = {
	options : {
		reporter : require('jshint-stylish'),
      scripturl:true
	},
	gruntfile : ['Gruntfile.js'],
	front_js : [
    '<%= paths.front_assets %>js/_dev_front_concat/*.js',
    '<%= paths.front_assets %>js/partials/*.js',
    '!<%= paths.front_assets %>js/partials/*min.js',
    '<%= paths.front_assets %>js/libs/nimble-video-bg.js'
    //'<%= paths.front_assets %>js/_dev_stand_alone/*.js'
  ],
  sektion_customizer_js : [
    '<%= paths.czr_assets %>sek/js/_dev_control/*.js',
    '<%= paths.czr_assets %>sek/js/_dev_preview/*.js'
  ],
	those : [], //populated dynamically with the watch event
};