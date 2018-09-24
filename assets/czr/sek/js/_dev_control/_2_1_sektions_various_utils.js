//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // @eturn void()
            rootPanelFocus : function() {
                  //close everything
                  if ( api.section.has( api.czr_activeSectionId() ) ) {
                        api.section( api.czr_activeSectionId() ).expanded( false );
                  } else {
                        api.section.each( function( _s ) {
                            _s.expanded( false );
                        });
                  }
                  api.panel.each( function( _p ) {
                        _p.expanded( false );
                  });
            },

            //@return a global unique identifier
            guid : function() {
                  function s4() {
                        return Math.floor((1 + Math.random()) * 0x10000)
                          .toString(16)
                          .substring(1);
                  }
                  return s4() + s4() + s4();//s4() + s4() + s4() + s4() + s4() + s4();
            },

            // @params = { id : '', level : '' }
            // Recursively walk the level tree until a match is found
            // @return the level model object
            getLevelModel : function( id, collection ) {
                  var self = this, _data_ = 'no_match';
                  // do we have a collection ?
                  // if not, let's use the root one
                  if ( _.isUndefined( collection ) ) {
                        var currentSektionSettingValue = api( self.sekCollectionSettingId() )();
                        var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : self.defaultSektionSettingValue;
                        collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                  }
                  _.each( collection, function( levelData ) {
                        // did we have a match recursively ?
                        if ( 'no_match' != _data_ )
                          return;
                        if ( id === levelData.id ) {
                              _data_ = levelData;
                        } else {
                              if ( _.isArray( levelData.collection ) ) {
                                    _data_ = self.getLevelModel( id, levelData.collection );
                              }
                        }
                  });
                  return _data_;
            },

            getLevelPositionInCollection : function( id, collection ) {
                  var self = this, _position_ = 'no_match';
                  // do we have a collection ?
                  // if not, let's use the root one
                  if ( _.isUndefined( collection ) ) {
                        var currentSektionSettingValue = api( self.sekCollectionSettingId() )();
                        var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : self.defaultSektionSettingValue;
                        collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                  }
                  _.each( collection, function( levelData, _key_ ) {
                        // did we have a match recursively ?
                        if ( 'no_match' != _position_ )
                          return;
                        if ( id === levelData.id ) {
                              _position_ = _key_;
                        } else {
                              if ( _.isArray( levelData.collection ) ) {
                                    _position_ = self.getLevelPositionInCollection( id, levelData.collection );
                              }
                        }
                  });
                  return _position_;
            },

            // @params = { property : 'options', id :  }
            // @return mixed type
            getLevelProperty : function( params ) {
                  params = _.extend( {
                        id : '',
                        property : ''
                  }, params );
                  if ( _.isEmpty( params.id ) ) {
                        api.errare( 'getLevelProperty => invalid id provided' );
                        return;
                  }
                  var self = this,
                      modelCandidate = self.getLevelModel( params.id );

                  if ( 'no_match' == modelCandidate ) {
                        api.errare( 'getLevelProperty => no level model found for id : ' + params.id );
                        return;
                  }
                  if ( ! _.isObject( modelCandidate ) ) {
                        api.errare( 'getLevelProperty => invalid model for id : ' + params.id, modelCandidate );
                        return;
                  }
                  return modelCandidate[ params.property ];
            },

            // @return a detached clone of a given level model, with new unique ids
            cloneLevel : function( levelId ) {
                  var self = this;
                  var levelModelCandidate = self.getLevelModel( levelId );
                  if ( 'no_match' == levelModelCandidate ) {
                        throw new Error( 'cloneLevel => no match for level id : ' + levelId );
                  }
                  var deepClonedLevel = $.extend( true, {}, levelModelCandidate );
                  // recursive
                  var newIdWalker = function( level_model ) {
                        if ( _.isEmpty( level_model.id ) ) {
                            throw new Error( 'cloneLevel => missing level id');
                        }
                        // No collection, we've reach the end of a branch
                        level_model.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                        if ( ! _.isEmpty( level_model.collection ) ) {
                              if ( ! _.isArray( level_model.collection ) ) {
                                    throw new Error( 'cloneLevel => the collection must be an array for level id : ' + level_model.id );
                              }
                              _.each( level_model.collection, function( levelData ) {
                                    levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                                    newIdWalker( levelData );
                              });
                        }
                        return level_model;
                  };
                  // recursively walk the provided level sub-tree until all collection ids are updated
                  return newIdWalker( deepClonedLevel );
            },

            // Extract the default model values from the server localized registered module
            // Invoked when registrating a module in api.czrModuleMap
            // For example :
            // czr_image_module : {
            //       mthds : ImageModuleConstructor,
            //       crud : false,
            //       name : 'Image',
            //       has_mod_opt : false,
            //       ready_on_section_expanded : true,
            //       defaultItemModel : _.extend(
            //             { id : '', title : '' },
            //             api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_module' )
            //       )
            // },
            // @return {}
            getDefaultItemModelFromRegisteredModuleData : function( moduleType ) {
                  if ( ! this.isModuleRegistered( moduleType ) ) {
                        return {};
                  }
                  // This method should normally not be invoked for a father module type
                  if ( sektionsLocalizedData.registeredModules[moduleType]['is_father'] ) {
                        api.errare( 'getDefaultItemModelFromRegisteredModuleData => Father modules should be treated specifically' );
                        return;
                  }
                  var data = sektionsLocalizedData.registeredModules[ moduleType ]['tmpl']['item-inputs'],
                      // title, id are always included in the defaultItemModel but those properties don't need to be saved in database
                      // title and id are legacy entries that can be used in multi-items modules to identify and name the item
                      defaultItemModel = {
                            id : '',
                            title : ''
                      },
                      self = this;

                  _.each( data, function( _d_, _key_ ) {
                        switch ( _key_ ) {
                              case 'tabs' :
                                    _.each( _d_ , function( _tabData_ ) {
                                          _.each( _tabData_['inputs'], function( _inputData_, _id_ ) {
                                                defaultItemModel[ _id_ ] = _inputData_['default'] || '';
                                          });
                                    });
                              break;
                              default :
                                    defaultItemModel[ _key_ ] = _d_['default'] || '';
                              break;
                        }
                  });
                  return defaultItemModel;
            },

            //@return mixed
            getRegisteredModuleProperty : function( moduleType, property ) {
                  if ( ! this.isModuleRegistered( moduleType ) ) {
                        return 'not_set';
                  }
                  return sektionsLocalizedData.registeredModules[ moduleType ][ property ];
            },

            // @return boolean
            isModuleRegistered : function( moduleType ) {
                  return sektionsLocalizedData.registeredModules && ! _.isUndefined( sektionsLocalizedData.registeredModules[ moduleType ] );
            },


            // Walk the main sektion setting and populate an array of google fonts
            // This method is used when processing the 'sek-update-fonts' action to update the .fonts property
            // To be a candidate for sniffing, an input font value  should meet those criteria :
            // 1) be the value of a '{...}_css' input id
            // 2) this input must be a font modifier ( @see 'refresh_fonts' params set on parent module registration )
            // 2) the font should start with [gfont]
            // @return array
            sniffGFonts : function( gfonts, level ) {
                  var self = this;
                  gfonts = gfonts || [];

                  if ( _.isUndefined( level ) ) {
                        var currentSektionSettingValue = api( self.sekCollectionSettingId() )();
                        level = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : self.defaultSektionSettingValue;
                  }
                  _.each( level, function( levelData, _key_ ) {
                        // example of input_id candidate 'font_family_css'
                        if ( _.isString( _key_ ) && '_css' === _key_.substr( _key_.length - 4 ) ) {
                              if ( true === self.inputIsAFontFamilyModifier( _key_ ) ) {
                                    if ( levelData.indexOf('gfont') > -1 && ! _.contains( gfonts, levelData ) ) {
                                          gfonts.push( levelData );
                                    }
                              }
                        }

                        if ( _.isArray( levelData ) || _.isObject( levelData ) ) {
                              self.sniffGFonts( gfonts, levelData );
                        }
                  });
                  return gfonts;
            },
















            //-------------------------------------------------------------------------------------------------
            // <RECURSIVE UTILITIES USING THE sektionsLocalizedData.registeredModules>
            //-------------------------------------------------------------------------------------------------
            // Invoked when updating a setting value => in normalizeAndSanitizeSingleItemInputValues(), when doing updateAPISettingAndExecutePreviewActions()
            // @return a mixed type default value
            // @param input_id string
            // @param module_type string
            // @param level array || object
            getInputDefaultValue : function( input_id, module_type, level ) {
                  var self = this;

                  // Do we have a cached default value ?
                  self.cachedDefaultInputValues = self.cachedDefaultInputValues || {};
                  self.cachedDefaultInputValues[ module_type ] = self.cachedDefaultInputValues[ module_type ] || {};
                  if ( _.has( self.cachedDefaultInputValues[ module_type ], input_id ) ) {
                        return self.cachedDefaultInputValues[ module_type ][ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputDefaultValue => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[module_type] ) ) {
                        api.errare( 'getInputDefaultValue => missing ' + module_type + ' in sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  // This method should normally not be invoked for a father module type
                  if ( sektionsLocalizedData.registeredModules[module_type]['is_father'] ) {
                        api.errare( 'getInputDefaultValue => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ][ 'tmpl' ];
                  }
                  var _defaultVal_ = 'no_default_value_specified';
                  _.each( level, function( levelData, _key_ ) {
                        // we found a match skip next levels
                        if ( 'no_default_value_specified' !== _defaultVal_ )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.default ) ) {
                              _defaultVal_ = levelData.default;
                        }
                        // if we have still no match, and the data are sniffable, let's go ahead recursively
                        if ( 'no_default_value_specified' === _defaultVal_ && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _defaultVal_ = self.getInputDefaultValue( input_id, module_type, levelData );
                        }
                        if ( 'no_default_value_specified' !== _defaultVal_ ) {
                            // cache it
                            self.cachedDefaultInputValues[ module_type ][ input_id ] = _defaultVal_;
                        }
                  });
                  return _defaultVal_;
            },



            // @return input_type string
            // @param input_id string
            // @param module_type string
            // @param level array || object
            getInputType : function( input_id, module_type, level ) {
                  var self = this;

                  // Do we have a cached default value ?
                  self.cachedInputTypes = self.cachedInputTypes || {};
                  self.cachedInputTypes[ module_type ] = self.cachedInputTypes[ module_type ] || {};
                  if ( _.has( self.cachedInputTypes[ module_type ], input_id ) ) {
                        return self.cachedInputTypes[ module_type ][ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputType => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[module_type] ) ) {
                        api.errare( 'getInputType => missing ' + module_type + ' in sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( sektionsLocalizedData.registeredModules[module_type]['is_father'] ) {
                        api.errare( 'getInputType => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ][ 'tmpl' ];
                  }
                  var _inputType_ = 'no_input_type_specified';
                  _.each( level, function( levelData, _key_ ) {
                        // we found a match skip next levels
                        if ( 'no_input_type_specified' !== _inputType_ )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.input_type ) ) {
                              _inputType_ = levelData.input_type;
                        }
                        // if we have still no match, and the data are sniffable, let's go ahead recursively
                        if ( 'no_input_type_specified' === _inputType_ && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _inputType_ = self.getInputType( input_id, module_type, levelData );
                        }
                        if ( 'no_input_type_specified' !== _inputType_ ) {
                              // cache it
                              self.cachedInputTypes[ module_type ][ input_id ] = _inputType_;
                        }
                  });
                  return _inputType_;
            },


            // Invoked when :
            // 1) updating a setting value, in ::updateAPISettingAndExecutePreviewActions()
            // 2) we need to get a registration param like the default value for example, @see spacing input
            // @return object of registration params
            // @param input_id string
            // @param module_type string
            // @param level array || object
            getInputRegistrationParams : function( input_id, module_type, level ) {
                  var self = this;

                  // Do we have a cached default value ?
                  self.cachedInputRegistrationParams = self.cachedInputRegistrationParams || {};
                  self.cachedInputRegistrationParams[ module_type ] = self.cachedInputRegistrationParams[ module_type ] || {};
                  if ( _.has( self.cachedInputRegistrationParams[ module_type ], input_id ) ) {
                        return self.cachedInputRegistrationParams[ module_type ][ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputRegistrationParams => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[module_type] ) ) {
                        api.errare( 'getInputRegistrationParams => missing ' + module_type + ' in sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  // This method should normally not be invoked for a father module type
                  if ( sektionsLocalizedData.registeredModules[module_type]['is_father'] ) {
                        api.errare( 'getInputRegistrationParams => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ][ 'tmpl' ];
                  }
                  var _params_ = {};
                  _.each( level, function( levelData, _key_ ) {
                        // we found a match skip next levels
                        if ( ! _.isEmpty( _params_ ) )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.input_type ) ) {
                              _params_ = levelData;
                        }
                        // if we have still no match, and the data are sniffable, let's go ahead recursively
                        if ( _.isEmpty( _params_ ) && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _params_ = self.getInputRegistrationParams( input_id, module_type, levelData );
                        }
                        if ( ! _.isEmpty( _params_ ) ) {
                              // cache it
                              self.cachedInputRegistrationParams[ module_type ][ input_id ] = _params_;
                        }
                  });
                  return _params_;
            },


            // @return bool
            // @param input_id string
            // @param module_type string
            // @param level array || object
            inputIsAFontFamilyModifier : function( input_id, level ) {
                  var self = this;

                  // Do we have a cached default value ?
                  self.cachedFontFamilyModifier = self.cachedFontFamilyModifier || {};
                  if ( _.has( self.cachedFontFamilyModifier, input_id ) ) {
                        return self.cachedFontFamilyModifier[ input_id ];
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'inputIsAFontFamilyModifier => missing sektionsLocalizedData.registeredModules' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules;
                  }
                  var _bool_ = 'not_set';
                  _.each( level, function( levelData, _key_ ) {
                        // we found a match skip next levels
                        if ( 'not_set' !== _bool_ )
                          return;
                        if ( input_id === _key_ && ! _.isUndefined( levelData.input_type ) ) {
                              _bool_ = _.isUndefined( levelData.refresh_fonts ) ? false : levelData.refresh_fonts;
                        }
                        // if we have still no match, and the data are sniffable, let's go ahead recursively
                        if ( 'not_set' === _bool_ && ( _.isArray( levelData ) || _.isObject( levelData ) ) ) {
                              _bool_ = self.inputIsAFontFamilyModifier( input_id, levelData );
                        }
                        if ( 'not_set' !== _bool_ ) {
                              // cache it
                              self.cachedFontFamilyModifier[ input_id ] = _bool_;
                        }
                  });
                  return _bool_;
            },
            //-------------------------------------------------------------------------------------------------
            // </RECURSIVE UTILITIES USING THE sektionsLocalizedData.registeredModules>
            //-------------------------------------------------------------------------------------------------















            // @return the item(s) ( array of items if multi-item module ) that we should use when adding the module to the main setting
            getModuleStartingValue : function( module_type ) {
                  if ( ! sektionsLocalizedData.registeredModules ) {
                        api.errare( 'getModuleStartingValue => missing sektionsLocalizedData.registeredModules' );
                        return 'no_starting_value';
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[ module_type ] ) ) {
                        api.errare( 'getModuleStartingValue => the module type ' + module_type + ' is not registered' );
                        return 'no_starting_value';
                  }
                  var starting_value = sektionsLocalizedData.registeredModules[ module_type ][ 'starting_value' ];
                  return _.isEmpty( starting_value ) ? 'no_starting_value' : starting_value;
            },



            /*
            * Following two functions taken from jQuery.tabbable 1.0
            * see https://github.com/marklagendijk/jquery.tabbable/blob/master/jquery.tabbable.js
            *
            * Copyright 2013, Mark Lagendijk
            * Released under the MIT license
            */
            selectNextTabbableOrFocusable : function( selector ) {
                  var selectables = $( selector );
                  var current = $( ':focus' );
                  var nextIndex = 0;
                  if( current.length === 1 ) {
                        var currentIndex = selectables.index( current );
                        if( currentIndex + 1 < selectables.length ) {
                              nextIndex = currentIndex + 1;
                        }
                  }

                  selectables.eq( nextIndex ).focus();
            },

            selectPrevTabbableOrFocusable : function( selector ) {
                  var selectables = $( selector );
                  var current = $( ':focus' );
                  var prevIndex = selectables.length - 1;
                  if( current.length === 1 ) {
                        var currentIndex = selectables.index( current );
                        if( currentIndex > 0 ) {
                              prevIndex = currentIndex - 1;
                        }
                  }

                  selectables.eq( prevIndex ).focus();
            },




            //-------------------------------------------------------------------------------------------------
            // GENERIC WAY TO SETUP SELECT INPUTS
            //-------------------------------------------------------------------------------------------------
            // used in the module input constructors
            // "this" is the input
            setupSelectInput : function() {
                  var input  = this,
                      item   = input.input_parent,
                      module = input.module,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type ),
                      selectOptions = inputRegistrationParams.choices;

                  if ( _.isEmpty( selectOptions ) ) {
                        api.errare( 'api.czr_sektions.setupSelectInput => missing select options for input id => ' + input.id + ' in image module');
                        return;
                  } else {
                        //generates the options
                        _.each( selectOptions , function( title, value ) {
                              var _attributes = {
                                        value : value,
                                        html: title
                                  };
                              if ( value == input() ) {
                                    $.extend( _attributes, { selected : "selected" } );
                              } else if ( 'px' === value ) {
                                    $.extend( _attributes, { selected : "selected" } );
                              }
                              $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                        });
                        $( 'select[data-czrtype]', input.container ).selecter();
                  }
            },


            //-------------------------------------------------------------------------------------------------
            // GENERIC WAY TO SETUP FONT SIZE AND LINE HEIGHT INPUTS
            // DEPRECATED
            //-------------------------------------------------------------------------------------------------
            // "this" is the input
            setupFontSizeAndLineHeightInputs : function( obj ) {
                  var input      = this,
                      $wrapper = $('.sek-font-size-line-height-wrapper', input.container ),
                      initial_unit = $wrapper.find('input[data-czrtype]').data('sek-unit'),
                      validateUnit = function( unit ) {
                            if ( ! _.contains( ['px', 'em', '%'], unit ) ) {
                                  api.errare( 'error : invalid unit for input ' + input.id, unit );
                                  unit = 'px';
                            }
                            return unit;
                      };
                  // initialize the unit with the value provided in the dom
                  input.css_unit = new api.Value( _.isEmpty( initial_unit ) ? 'px' : validateUnit( initial_unit ) );
                  // React to a unit change
                  input.css_unit.bind( function( to ) {
                        to = _.isEmpty( to ) ? 'px' : to;
                        $wrapper.find( 'input[type="number"]').trigger('change');
                  });

                  // instantiate stepper and schedule change reactions
                  $wrapper.find( 'input[type="number"]').on('input change', function( evt ) {
                        input( $(this).val() + validateUnit( input.css_unit() ) );
                  }).stepper();


                  // Schedule unit changes on button click
                  $wrapper.on( 'click', '[data-sek-unit]', function(evt) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        $wrapper.find('[data-sek-unit]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        // update the initial unit ( not mandatory)
                        $wrapper.find('input[data-czrtype]').data('sek-unit', $(this).data('sek-unit') );
                        // set the current unit Value
                        input.css_unit( $(this).data('sek-unit') );
                  });

                  // add is-selected button on init to the relevant unit button
                  $wrapper.find( '.sek-ui-button[data-sek-unit="'+ initial_unit +'"]').addClass('is-selected').attr( 'aria-pressed', true );
            },



            //-------------------------------------------------------------------------------------------------
            // PREPARE INPUT REGISTERED WITH has_device_switcher set to true
            //-------------------------------------------------------------------------------------------------
            // "this" is the input
            maybeSetupDeviceSwitcherForInput : function() {
                  var input = this;
                  // render the device switcher before the input title
                  var deviceSwitcherHtml = [
                        '<span class="sek-input-device-switcher">',
                          '<i data-sek-device="desktop" class="sek-switcher preview-desktop active" title="'+ sektionsLocalizedData.i18n['Settings on desktops'] +'"></i>',
                          '<i data-sek-device="tablet" class="sek-switcher preview-tablet" title="'+ sektionsLocalizedData.i18n['Settings on tablets'] +'"></i>',
                          '<i data-sek-device="mobile" class="sek-switcher preview-mobile" title="'+ sektionsLocalizedData.i18n['Settings on mobiles'] +'"></i>',
                        '</span>'
                  ].join(' ');

                  input.container.find('.customize-control-title').prepend( deviceSwitcherHtml );
                  input.previewedDevice = new api.Value( api.previewedDevice() );


                  syncWithPreviewedDevice = function( evt ) {
                        evt.stopPropagation();
                        input.container.find( '[data-sek-device]' ).removeClass('active');
                        $(this).addClass('active');
                        var device = 'desktop';
                        try { device = $(this).data('sek-device'); } catch( er ) {
                              api.errare( 'maybeSetupDeviceSwitcherForInput => error when binding sek-switcher', er );
                        }
                        try { api.previewedDevice( device ); } catch( er ) {
                              api.errare( 'maybeSetupDeviceSwitcherForInput => error when setting the previewed device', er );
                        }
                        input.previewedDevice( device );
                  };
                  // react on device click
                  input.container.on( 'click', '[data-sek-device]', syncWithPreviewedDevice );

                  // initialize with the currently previewed device
                  var $currentDeviceIcon = input.container.find('[data-sek-device="' + api.previewedDevice() + '"]');
                  if ( $currentDeviceIcon.length > 0 ) {
                        $currentDeviceIcon.trigger('click');
                  }
            },



            //-------------------------------------------------------------------------------------------------
            // GENERIC WAY TO SETUP ACCORDION BEHAVIOUR OF MODULES IN SECTIONS
            //-------------------------------------------------------------------------------------------------
            // "this" is the section
            // in the content picker section, control's container have the attribute "data-sek-accordion" to selectively enable the accordion
            // @see ::generateUIforDraggableContent()
            // @params { expand_first_module : boolean }
            scheduleModuleAccordion : function( params ) {
                  params = params || { expand_first_module : true };
                  var _section_ = this;
                  // Attach event on click
                  $( _section_.container ).on( 'click', '.customize-control label > .customize-control-title', function( evt ) {
                        //evt.preventDefault();
                        evt.stopPropagation();
                        var $control = $(this).closest( '.customize-control');

                        if ( "no" === $control.attr( 'data-sek-accordion' ))
                          return;

                        _section_.container.find('.customize-control').not( $control ).each( function() {
                              if ( $(this).attr( 'data-sek-accordion' ) )
                                return;
                              $(this).attr('data-sek-expanded', "false" );
                              $(this).find('.czr-items-wrapper').stop( true, true ).slideUp( 'fast' );
                        });
                        $control.find('.czr-items-wrapper').stop( true, true ).slideToggle({
                              duration : 'fast',
                              start : function() {
                                    $control.attr('data-sek-expanded', "false" == $control.attr('data-sek-expanded') ? "true" : "false" );
                              }
                        });
                  });

                  // Expand the first module if requested
                  if ( params.expand_first_module ) {
                        _section_.container.find('.customize-control').first().find('label > .customize-control-title').trigger('click');
                  }
            },



            //-------------------------------------------------------------------------------------------------
            // HELPERS USED WHEN UPLOADING IMAGES FROM PRESET SECTIONS
            //-------------------------------------------------------------------------------------------------
            isPromise : function (fn) {
                  return fn && typeof fn.then === 'function' && String( $.Deferred().then ) === String( fn.then );
            },

            // @param deferreds = { '::img-path::/assets/img/tests/1.jpg' : 'dfd1', '::img-path::/assets/img/tests/2.jpg' : dfd2, ..., '::img-path::/assets/img/tests/n.jpg' : dfdn }
            whenAllPromisesInParallel : function ( deferreds ) {
                var self = this,
                    mainDfd = $.Deferred(),
                    args = [],
                    _keys_ = _.keys( deferreds );

                _.each( deferreds, function( mayBeDfd, _k_ ) {
                      args.push( $.Deferred( function( _dfd_ ) {
                            var dfdCandidate = self.isPromise( mayBeDfd ) ? mayBeDfd : $.Deferred();
                            dfdCandidate
                                  .done( _dfd_.resolve )
                                  .fail( function (err) { _dfd_.reject( err ); } );
                      }) );
                });
                $.when.apply( this, args )
                      .done( function () {
                          var resObj = {},
                              resArgs = Array.prototype.slice.call( arguments );

                          _.each( resArgs, function( v, i ) {
                                resObj[ _keys_[i] ] = v;
                          });
                          mainDfd.resolve( resObj );
                      })
                      .fail( mainDfd.reject );

                return mainDfd;
            },

            // Run the deferred in sequence, only one asynchronous method at a time
            // Was an experiment when implementing the img assets upload for preset sections
            // Abandonned for whenAllPromisesInParallel
            whenAllPromisesInSerie : function ( deferreds, ind, promiseMessages, mainDfd ) {
                ind = ind || 0;
                promiseMessages = promiseMessages || {};
                mainDfd = mainDfd || $.Deferred();
                var self = this;
                if ( _.isArray( deferreds ) ) {
                      var mayBeDfd = deferreds[ind],
                          dfdCandidate = self.isPromise( mayBeDfd ) ? mayBeDfd : $.Deferred( function( _d_ ) { _d_.resolve(); } );

                      dfdCandidate.always( function( msg ) {
                            promiseMessages[ ind ] = msg;
                            if ( ( ind + 1 ) == deferreds.length ) {
                                  mainDfd.resolve( promiseMessages );
                            } else {
                                  if ( ind + 1 < deferreds.length ) {
                                      self.whenAllPromisesInSerie( deferreds, ind + 1, promiseMessages, mainDfd );
                                  }
                            }
                      });
                }//if
                return mainDfd;
            },


            // @param relpath = string : '/assets/img/41883.jpg'
            // @return a promise
            importAttachment : function( relpath ) {
                  // @see php wp_ajax_sek_import_attachment
                  return wp.ajax.post( 'sek_import_attachment', {
                        rel_path : relpath,
                        nonce: api.settings.nonce.save//<= do we need to set a specific nonce to fetch the attachment
                  })
                  .fail( function( _er_ ) {
                        api.errare( 'sek_import_attachment ajax action failed for image ' +  relpath, _er_ );
                  });
                  // .done( function( data) {
                  //       api.infoLog('relpath and DATA ' + relpath , data );
                  // });
            }

      });//$.extend()
})( wp.customize, jQuery );