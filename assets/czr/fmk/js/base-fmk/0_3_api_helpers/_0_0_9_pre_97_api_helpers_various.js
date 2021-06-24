
(function (api, $, _) {
api.CZR_Helpers = api.CZR_Helpers || {};
//////////////////////////////////////////////////
/// ACTIONS AND DOM LISTENERS
//////////////////////////////////////////////////
//adds action to an existing event map
//@event map = [ {event1}, {event2}, ... ]
//@new_event = {  trigger   : event name , actions   : [ 'cb1', 'cb2', ... ] }
api.CZR_Helpers = $.extend( api.CZR_Helpers, {
      css_loader_html : '<div class="czr-css-loader czr-mr-loader" style="display:none"><div></div><div></div><div></div></div>',

      //While a control should always have a default setting,
      //It can have additional setting assigned
      //This method returns the default setting or the specified type if requested
      //Example : header_image has default and data
      getControlSettingId : function( control_id, setting_type ) {
            setting_type = 'default' || setting_type;
            if ( ! api.control.has( control_id ) ) {
                 // api.consoleLog( 'getControlSettingId : The requested control_id is not registered in the api yet : ' + control_id );
                  return control_id;
            }
            if ( ! _.has( api.control( control_id ), 'settings' ) || _.isEmpty( api.control( control_id ).settings ) )
              return control_id;

            if ( ! _.has( api.control( control_id ).settings, setting_type ) ) {
                  api.consoleLog( 'getControlSettingId : The requested control_id does not have the requested setting type : ' + control_id + ' , ' + setting_type );
                  return control_id;
            }
            if ( _.isUndefined( api.control( control_id ).settings[setting_type].id ) ) {
                  api.consoleLog( 'getControlSettingId : The requested control_id has no setting id assigned : ' + control_id );
                  return control_id;
            }
            return api.control( control_id ).settings[setting_type].id;
      },



      getDocSearchLink : function( text ) {
            text = ! _.isString(text) ? '' : text;
            var _searchtext = text.replace( / /g, '+'),
                _url = [ serverControlParams.docURL, 'search?query=', _searchtext ].join('');
            return [
              '<a href="' + _url + '" title="' + serverControlParams.i18n.readDocumentation + '" target="_blank">',
              ' ',
              '<span class="far fa-question-circle-o"></span>'
            ].join('');
      },


       /*
      * @return string
      * simple helper to build the setting wp api ready id
      */
      build_setId : function ( setId ) {
            if ( _.isUndefined( window.themeServerControlParams ) || ! _.isArray( themeServerControlParams.wpBuiltinSettings ) ) {
                //api.errorLog( 'build_setId => missing themeServerControlParams !');
                return setId;
            }
            //exclude the WP built-in settings like blogdescription, show_on_front, etc
            if (  _.contains( themeServerControlParams.wpBuiltinSettings, setId ) )
              return setId;

            // //extract the setting id for theme mods
            // var _pattern;

            //exclude the WP built-in settings like sidebars_widgets*, nav_menu_*, widget_*, custom_css
            // var _patterns = [ 'widget_', 'nav_menu', 'sidebars_', 'custom_css' ],
            //     _isExcld = false;
            // _.each( _patterns, function( _ptrn ) {
            //       if ( _isExcld )
            //         return;
            //       _isExcld = _ptrn == setId.substring( 0, _ptrn.length );
            // });
            // if ( _isExcld )
            // return setId;
            if ( ! _.contains( themeServerControlParams.themeSettingList, setId ) )
              return setId;

            return -1 == setId.indexOf( themeServerControlParams.themeOptions ) ? [ themeServerControlParams.themeOptions +'[' , setId  , ']' ].join('') : setId;
    },

      /*
      * @return string
      * simple helper to extract the option name from a setting id
      */
      getOptionName : function( name ) {
            if ( _.isEmpty( window.themeServerControlParams ) || _.isEmpty( themeServerControlParams.themeOptions ) ) {
                //api.errorLog( 'getOptionName => missing themeServerControlParams !');
                return name;
            }
            var self = this;
            //targets only the options of the theme
            if ( -1 == name.indexOf( themeServerControlParams.themeOptions) )
              return name;
            return name.replace(/\[|\]/g, '').replace( themeServerControlParams.themeOptions, '');
      },



      //@return bool
      //@uses api.czr_partials
      hasPartRefresh : function( setId ) {
            if ( ! _.has( api, 'czr_partials')  )
              return;
            return  _.contains( _.map( api.czr_partials(), function( partial, key ) {
                  return _.contains( partial.settings, setId );
            }), true );
      },

      //@return the array of controls in a given section_id
      getSectionControlIds : function( section_id ) {
            section_id = section_id || api.czr_activeSectionId();
            return ! api.section.has( section_id ) ?
                  [] :
                  _.map( api.section( section_id ).controls(), function( _ctrl ) {
                        return _ctrl.id;
                  });
      },


      //1) get the control of a given section
      //2) for each control get the associated setting(s)
      //=> important, a control might have several associated settings. Typical example : header_image.
      //@return [] of setting ids for a given czr section
      getSectionSettingIds : function( section_id ) {
            section_id = section_id || api.czr_activeSectionId();
            if ( ! api.section.has( section_id) )
              return;
            var self = this,
                _sec_settings = [],
                _sec_controls = self.getSectionControlIds( section_id );

            _.each( _sec_controls, function( ctrlId ) {
                  _.each( api.control(ctrlId).settings, function( _instance, _k ) {
                        _sec_settings.push( _instance.id );
                  });
            });
            return _sec_settings;
      },


      //////////////////////////////////////////////////
      /// STRINGS HELPERS
      //////////////////////////////////////////////////
      capitalize : function( string ) {
            if( ! _.isString(string) )
              return string;
            return string.charAt(0).toUpperCase() + string.slice(1);
      },

      truncate : function( string, n, useWordBoundary ){
            if ( ! _.isString( string ) )
              return '';
            n = n || 20;
            var isTooLong = string.length > n,
                s_ = isTooLong ? string.substr(0,n-1) : string;
                s_ = (useWordBoundary && isTooLong) ? s_.substr(0,s_.lastIndexOf(' ')) : s_;
            return  isTooLong ? s_ + '...' : s_;
      },


      //////////////////////////////////////////////////
      /// STRINGS HELPERS
      //////////////////////////////////////////////////
      //is a module multi item ?
      //@return bool
      isMultiItemModule : function( module_type, moduleInst ) {
            if ( _.isUndefined( module_type ) && ! _.isObject( moduleInst ) )
              return;
            if ( _.isObject( moduleInst ) && _.has( moduleInst, 'module_type' ) )
              module_type = moduleInst.module_type;
            else if ( _.isUndefined( module_type ) || _.isNull( module_type ) )
              return;
            if ( ! _.has( api.czrModuleMap, module_type ) )
              return;

            return api.czrModuleMap[module_type].crud || api.czrModuleMap[module_type].multi_item || false;
      },

      //is a module crud ?
      //@return bool
      isCrudModule : function( module_type, moduleInst ) {
            if ( _.isUndefined( module_type ) && ! _.isObject( moduleInst ) )
              return;
            if ( _.isObject( moduleInst ) && _.has( moduleInst, 'module_type' ) )
              module_type = moduleInst.module_type;
            else if ( _.isUndefined( module_type ) || _.isNull( module_type ) )
              return;
            if ( ! _.has( api.czrModuleMap, module_type ) )
              return;

            return api.czrModuleMap[module_type].crud || false;
      },

      //is a module crud ?
      //@return bool
      hasModuleModOpt : function( module_type, moduleInst ) {
            if ( _.isUndefined( module_type ) && ! _.isObject( moduleInst ) )
              return;
            if ( _.isObject( moduleInst ) && _.has( moduleInst, 'module_type' ) )
              module_type = moduleInst.module_type;
            else if ( _.isUndefined( module_type ) || _.isNull( module_type ) )
              return;
            if ( ! _.has( api.czrModuleMap, module_type ) )
              return;

            return api.czrModuleMap[module_type].has_mod_opt || false;
      },



      //@self explanatory: removes a collection of input from a parent item or modOpt instance
      //Triggered by : user actions usually when an item is collapsed or when the modOpt panel is closed
      removeInputCollection : function() {
            var inputParentInst = this;//<= because fired with .call( inputParentInst )
            if ( ! _.isFunction( inputParentInst ) ) {
                  throw new Error( 'removeInputCollection : inputParentInst is not valid.' );
            }
            if ( ! _.has( inputParentInst, 'czr_Input') )
              return;
            //remove each input api.Value() instance
            inputParentInst.czr_Input.each( function( _input ) {
                  inputParentInst.czr_Input.remove( _input.id );
            });
            //reset the input collection property
            inputParentInst.inputCollection({});
      },

      //Re-instantiate a module control based on its id
      //@param wpSetId : the api id of the control to refresh
      refreshModuleControl : function( wpSetId ) {
            var _constructor = api.controlConstructor.czr_module,
                _control_type = api.control( wpSetId ).params.type,
                _control_data = api.settings.controls[wpSetId];

            //remove the container and its control
            $.when( api.control( wpSetId ).container.remove() ).done( function() {
                  //remove the control from the api control collection
                  api.control.remove( wpSetId );

                  //re-instantiate the control with the updated _control_data
                  api.control.add( wpSetId,  new _constructor( wpSetId, { params : _control_data, previewer : api.previewer }) );
            });

      },


      // BOOLEAN CHECK
      // @return bool
      isChecked : function( v ) {
            if ( _.isBoolean( v ) ) {
                  return v;
            } else if ( _.isNumber( v ) ) {
                  return v > 0;
            } else if ( _.isString( v ) ) {
                return '0' !== v && '' !== v && 'off' !== v;
            }
            return false;
      },


      // COLORS
      hexToRgb : function( hex ) {
            // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
            var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
            try {
                  hex = hex.replace(shorthandRegex, function(m, r, g, b) {
                      return r + r + g + g + b + b;
                  });
            } catch( er ) {
                  api.errorLog( 'Error in Helpers::hexToRgb : ' + er );
                  return hex;
            }

            var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec( hex );
            result = result ? [
                  parseInt(result[1], 16),//r
                  parseInt(result[2], 16),//g
                  parseInt(result[3], 16)//b
            ] : [];
            return 'rgb(' + result.join(',') + ')';
      },

      rgbToHex : function ( r, g, b ) {
            var componentToHex = function(c) {
                  var hex = c.toString(16);
                  return hex.length == 1 ? "0" + hex : hex;
            };
            return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
      },



      // inspired from wp.template in wp-includes/js/wp-util.js
      parseTemplate : _.memoize(function ( html ) {
            var compiled,
              /*
               * Underscore's default ERB-style templates are incompatible with PHP
               * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
               *
               * @see trac ticket #22344.
               */
              options = {
                    evaluate:    /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
                    variable:    'data'
              };

            return function ( data ) {
                  compiled = compiled || _.template( html,  options );
                  return compiled( data );
            };
      })
});//$.extend
  // $( window ).on( 'message', function( e, o) {
  //   api.consoleLog('WHAT ARE WE LISTENING TO?', e, o );
  // });
})( wp.customize , jQuery, _);