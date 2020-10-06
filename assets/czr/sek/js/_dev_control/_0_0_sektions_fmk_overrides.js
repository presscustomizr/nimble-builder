//global sektionsLocalizedData
//global serverControlParams
(function ( api, $ ) {
      api.CZR_Helpers.getInputSubTemplate = function( template_name ) {
            if ( $('#tmpl-nimble-subtemplate___' + template_name ).length > 0 ) {
                return wp.template( 'nimble-subtemplate___' + template_name );
            } else {
                api.errare( 'problem in api.czr_sektions.getInputSubTemplate(), missing js template in the DOM for template_name : ' + template_name );
                return null;
            }
      };

      var getInputTemplate = function( input_type ) {
            var template_name = input_type;
            switch( input_type ) {
                  case 'czr_layouts' ://<= specific to the hueman theme
                  case 'select' ://<= used in the customizr and hueman theme
                  case 'simpleselect' ://<=used in Nimble Builder
                  case 'fa_icon_picker' :
                  case 'font_picker':
                  case 'animation_picker' ://<= oct 2020 added for https://github.com/presscustomizr/nimble-builder-pro/issues/73
                        template_name = 'simpleselect';
                  break;

                  case 'simpleselectWithDeviceSwitcher':
                        template_name = 'simpleselect_deviceswitcher';
                  break;

                  case 'multiselect':
                  case 'category_picker':
                        template_name = 'multiselect';
                  break;

                  case 'h_alignment' :
                  case 'horizAlignmentWithDeviceSwitcher' :
                        template_name = 'h_alignment';
                  break;

                  case 'h_text_alignment' :
                  case 'horizTextAlignmentWithDeviceSwitcher' :
                        template_name = 'h_text_alignment';
                  break;

                  case 'range_simple' :
                  case 'range_simple_device_switcher' :
                        template_name = 'range_simple';
                  break;

                  case 'number_simple' :
                        template_name = 'number_simple';
                  break;

                  case 'font_size' :
                  case 'line_height' :
                  case 'range_with_unit_picker' :
                  case 'range_with_unit_picker_device_switcher' :
                        template_name = 'range_with_unit_picker';
                  break;

                  case 'spacing' :
                  case 'spacingWithDeviceSwitcher' :
                        template_name = 'spacing';
                  break;

                  case 'upload' :
                  case 'upload_url' :
                        template_name = 'upload';
                  break;

                  case 'bg_position' :
                  case 'bgPositionWithDeviceSwitcher' :
                        template_name = 'bg_position';
                  break;

                  case 'verticalAlignWithDeviceSwitcher' :
                        template_name = 'v_alignment';
                  break;
            }
            if ( $('#tmpl-nimble-input___' + template_name ).length > 0 ) {
                return wp.template( 'nimble-input___' + template_name );
            } else {
                api.errare( 'problem in getInputTemplate(), missing js template in the DOM for input_type : ' + input_type );
                return null;
            }
      };


      // Overrides FMK method
      // @param args
      // control_id: "__nimble__51b2f35191b3__main_settings"
      // item_model: {id: "czr_heading_child_0", title: "", heading_text: "This is a heading.", heading_tag: "h1", h_alignment_css: {…}, …}
      // module_id: "__nimble__51b2f35191b3__main_settings_czr_module"
      // module_type: "czr_heading_child"
      // tmpl: "item-inputs"
      var originalMethod = api.CZR_Helpers.getModuleTmpl;
      api.CZR_Helpers.getModuleTmpl = function( args ) {
            args = _.extend( {
                  tmpl : '',
                  module_type: '',
                  module_id : '',
                  cache : true,//<= shall we cache the tmpl or not. Should be true in almost all cases.
                  nonce: api.settings.nonce.save//<= do we need to set a specific nonce to fetch the tmpls ?
            }, args );

            // target only Nimble modules
            // a nimble module id looks like : "__nimble__00b8efefe207_czr_module"
            if ( -1 === args.module_id.indexOf('__nimble__') ) {
                  return originalMethod( args );
            }

            var dfd = $.Deferred();
            // are we good to go ?
            if ( _.isEmpty( args.tmpl ) || _.isEmpty( args.module_type ) ) {
                  dfd.reject( 'api.CZR_Helpers.getModuleTmpl => missing tmpl or module_type param' );
            }

            if ( ! api.czr_sektions.isModuleRegistered( args.module_type ) ) {
                  dfd.reject( 'api.CZR_Helpers.getModuleTmpl => module type not registered' );
                  dfd.resolve();
                  return originalMethod( args );
            }

            /// TEMP !! ///
            // dfd.resolve();
            // return originalMethod( args );
            if ( _.contains( [
              // 'sek_content_type_switcher_module',
              // 'sek_module_picker_module'
            ], args.module_type ) ) {
                  dfd.resolve();
                  return originalMethod( args );
            }
            /// TEMP !! ///

            // Get the item input map
            var item_input_tmpls = api.czr_sektions.getRegisteredModuleProperty( args.module_type, 'tmpl' );
            var item_input_map = ( _.isObject( item_input_tmpls ) && item_input_tmpls[ args.tmpl ] ) ? item_input_tmpls[ args.tmpl ] : {};
            if ( _.isEmpty( item_input_map ) || !_.isObject( item_input_map ) ) {
                  api.errare( 'getModuleTmpl => Error empty or invalid input map for module : ', args.module_type );
                  dfd.reject( 'getModuleTmpl => Error empty or invalid input map for module : ', args.module_type );
            }

            // Get the item value
            var item_model = args.item_model,
                default_item_model = $.extend( true, {}, api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( args.module_type ) ),
                cloned_default = $.extend( true, {},default_item_model );


            // normalizes the item_model with defaults
            item_model = $.extend( cloned_default, item_model );

            if ( _.isEmpty( item_model ) ) {
                  api.errare( 'getModuleTmpl => Error invalid item model for module : ', args.module_type );
                  dfd.reject( 'getModuleTmpl => Error invalid item model for module : ', args.module_type );
            }

            var input_html = '', input_type;

            // Loop on the item input map and render the input
            // the rendering uses a nested _ template mechanism
            // see https://stackoverflow.com/questions/8938841/underscore-js-nested-templates#13649447
            var renderInputCollection = function( inputCollection ) {
                  var _html = '';
                  _.each( inputCollection, function( input_data, input_id ){
                        input_type = input_data.input_type;
                        // render generic input wrapper
                        try { _html +=  wp.template( 'nimble-input-wrapper' )( {
                            input_type : input_type,
                            input_data : input_data,
                            input_id : input_id,
                            item_model : item_model,
                            input_tmpl : getInputTemplate( input_type ),
                            control_id : args.control_id //<= needed for some modules like tiny_mce_editor
                        }); } catch( er ) {
                              api.errare( 'getModuleTmpl => Error when parsing the nimble-input-wrapper template', er );
                              dfd.reject( 'getModuleTmpl => Error when parsing the nimble-input-wrapper template');
                              return false;
                        }
                  });
                  return _html;
            };//renderInputCollection


            // GENERATE MODULE HTML : two cases, with or without tabs
            if ( item_input_map.tabs ) {
                  var _tabNavHtml = '', _tabContentHtml ='';

                  _.each( item_input_map.tabs, function( rawTabData, tabKey ) {
                        // normalizes
                        var tabData = $.extend( true, {}, rawTabData );
                        tabData = $.extend( { inputs : {}, title : '' }, tabData );
                        // generate tab nav html
                        var _attributes = !_.isEmpty( tabData.attributes ) ? tabData.attributes : '';
                        _tabNavHtml += '<li data-tab-id="section-topline-' + ( +tabKey+1 ) +'" '+ _attributes +'><a href="#" title="' + tabData.title + '"><span>' + tabData.title +'</span></a></li>';
                        // generate tab content html
                        var _inputCollectionHtml = renderInputCollection( tabData.inputs );
                        _tabContentHtml += '<section id="section-topline-' + ( +tabKey+1 ) +'">' + _inputCollectionHtml +'</section>';
                  });


                  // put it all together
                  input_html += [
                      '<div class="tabs tabs-style-topline">',
                        '<nav>',
                          '<ul>',
                            _tabNavHtml,
                          '</ul>',
                        '</nav>',
                        '<div class="content-wrap">',
                          _tabContentHtml,
                        '</div>',
                      '</div>',
                  ].join('');

            } else {
                  input_html = renderInputCollection(item_input_map);
            }



            // 1) Get the input map from the module registration params
            // 2) Normalizes input data with defaults
            // 3) loop on the input map
            // 4) print the default input tmpl wrapper

            return dfd.resolve( input_html ).promise();
      };
})( wp.customize, jQuery );