module.exports = {
	options : {
		reporter : require('jshint-stylish'),
      scripturl:true
	},
	gruntfile : ['Gruntfile.js'],
	front_js : ['<%= paths.front_js %>*.js', '!<%= paths.front_js %>*.min.js'],
	those : [], //populated dynamically with the watch event
};