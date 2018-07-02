//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      //HEADING MODULE
      var HeadingModuleConstructor  = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;

                    //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRHeadingInputMths || {} );


                    //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    //module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );

                    // run the parent initialize
                    // Note : must be always invoked always after the input / item class extension
                    // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRHeadingInputMths: {
                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in heading module');
                                  return;
                            } else {
                                  //generates the options
                                  _.each( sektionsLocalizedData.selectOptions[input.id] , function( title, value ) {
                                        var _attributes = {
                                                  value : value,
                                                  html: title
                                            };
                                        if ( value == input() ) {
                                              $.extend( _attributes, { selected : "selected" } );
                                        }

                                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                  });
                                  $( 'select[data-czrtype]', input.container ).selecter();
                            }
                    }
            },//CZRHeadingsInputMths
      };//HeadingModuleConstructor

      //DIVIDER MODULE
      var DividerModuleConstructor = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;

                    //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRDividerInputMths || {} );

                    //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    //module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );

                    // run the parent initialize
                    // Note : must be always invoked always after the input / item class extension
                    // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            CZRDividerInputMths: {
                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions['border-type'] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in divider module');
                                  return;
                            } else {
                                  //generates the options
                                  _.each( sektionsLocalizedData.selectOptions['border-type'] , function( title, value ) {
                                        var _attributes = {
                                                  value : value,
                                                  html: title
                                            };
                                        if ( value == input() ) {
                                              $.extend( _attributes, { selected : "selected" } );
                                        }
                                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                  });
                                  $( 'select[data-czrtype]', input.container ).selecter();
                            }
                    }
            },//CZRDividerInputMths
      };//DividerModuleConstructor

      //ICON MODULE
      var IconModuleConstructor = {
              initialize: function( id, options ) {
                      //console.log('INITIALIZING IMAGE MODULE', id, options );
                      var module = this;

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                      module.inputConstructor = api.CZRInput.extend( module.CZRIconInputMths || {} );

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                      //module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );


                      //SET THE CONTENT PICKER DEFAULT OPTIONS
                      //@see ::setupContentPicker()
                      module.bind( 'set_default_content_picker_options', function( params ) {
                            params.defaultContentPickerOption.defaultOption = {
                                  'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                                  'type'       : '',
                                  'type_label' : '',
                                  'object'     : '',
                                  'id'         : '_custom_',
                                  'url'        : ''
                            };
                            return params;
                      });

                      module.icons = {
                        'fas' : [
                          'address-book',
                          'adjust'
                        ],
                        'far' : [
                          'calendar',
                          'calendar-alt'
                        ],
                        'fab' : [
                          'adn',
                          'adversal'
                        ]
                      };

                      //to localize
                      module.icon_groups = {
                        'fas' : 'Solid',
                        'far' : 'Regular',
                        'fab' : 'Brand'
                      };


                      // run the parent initialize
                      // Note : must be always invoked always after the input / item class extension
                      // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize

              /* Helpers */

              CZRIconInputMths: {
                      setupSelect : function() {
                              var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _model  = item(),
                                  _selected_found = false;

                              //Icon select
                              if ( 'icon' == input.id ) {
                                  //generates the options
                                  _.each( module.icons , function( icons, group ) {
                                          var $_group =  $( '<optgroup>', { label: module.icon_groups[ group ] } );
                                          $( 'select[data-czrtype="social-icon"]', input.container ).append( $( '<optgroup>', { label: module.icon_groups[ group ] } ) );
                                          _.each( icons, function( icon ) {
                                                var _attributes = {
                                                          value: group + ' fa-' + icon,
                                                          html: api.CZR_Helpers.capitalize( icon )
                                                    };
                                                if ( _attributes.value == _model['icon'] ) {
                                                      $.extend( _attributes, { selected : "selected" } );
                                                      _selected_found = true;
                                                }
                                                $_group.append( $('<option>', _attributes) );
                                          });

                                          $( 'select[data-czrtype]', input.container ).append( $_group );
                                  });

                                  var addIcon = function ( state ) {
                                        if (! state.id) { return state.text; }

                                        //two spans here because we cannot wrap the social text into the social icon span as the solid FA5 font-weight is bold
                                        var  $state = $(
                                          '<span class="' + state.element.value + '"></span><span class="social-name">&nbsp;&nbsp;' + state.text + '</span>'
                                        );
                                        return $state;
                                  };

                                  //blank option to allow placeholders
                                  var $_placeholder;
                                  if ( _selected_found ) {
                                        $_placeholder = $('<option>');
                                  } else {
                                        $_placeholder = $('<option>', { selected: 'selected' } );
                                  }
                                  //Initialize select2
                                  $( 'select[data-czrtype]', input.container )
                                    .prepend( $_placeholder )
                                    .select2({
                                          templateResult: addIcon,
                                          templateSelection: addIcon,
                                          placeholder: sektionsLocalizedData.i18n['Select an icon'],
                                          allowClear: true
                                  });

                              } //Link select
                              else if ( 'link-to' == input.id ) {
                                    if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                          api.errare( 'Missing select options for input id => ' + input.id + ' in icon module');
                                          return;
                                    } else {
                                          //generates the options
                                          _.each( sektionsLocalizedData.selectOptions[input.id] , function( title, value ) {
                                                //get only no-link and url
                                                if ( !(  _.contains([ 'no-link', 'url' ], value) ) ) {
                                                      return;
                                                }
                                                var _attributes = {
                                                          value : value,
                                                          html: title
                                                    };
                                                if ( value == input() ) {
                                                      $.extend( _attributes, { selected : "selected" } );
                                                }

                                                $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                          });
                                          $( 'select[data-czrtype]', input.container ).selecter();
                                    }
                              }
                      }
              },//CZRIconInputMths
      };//IconModuleConstructor

      //provides a description of each module
      //=> will determine :
      //1) how to initialize the module model. If not crud, then the initial item(s) model shall be provided
      //2) which js template(s) to use : if crud, the module template shall include the add new and pre-item elements.
      //   , if crud, the item shall be removable
      //3) how to render : if multi item, the item content is rendered when user click on edit button.
      //    If not multi item, the single item content is rendered as soon as the item wrapper is rendered.
      //4) some DOM behaviour. For example, a multi item shall be sortable.
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            czr_heading_module : {
                  mthds : HeadingModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_heading_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_heading_module' )
            },

            czr_spacer_module : {
                  //mthds : ModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_spacer_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_spacer_module' )
            },

            czr_divider_module : {
                  mthds : DividerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_divider_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_divider_module' )
            },

            czr_icon_module : {
                  mthds : IconModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_icon_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_icon_module' )
            },
      });
})( wp.customize , jQuery, _ );