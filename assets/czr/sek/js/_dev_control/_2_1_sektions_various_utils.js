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

            //@return a 24 digits global unique identifier
            guid : function() {
                  function s4() {
                        return Math.floor((1 + Math.random()) * 0x10000)
                          .toString(16)
                          .substring(1);
                  }
                  return s4() + s4() + s4() + s4() + s4() + s4();
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
            // Invoked when registrating a module.
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
                  var data = sektionsLocalizedData.registeredModules[ moduleType ]['tmpl']['item-inputs'],
                      // title, id are always included in the defaultItemModel but those properties don't need to be saved in database
                      // title and id are legacy entries that can be used in multi-items modules to identify and name the item
                      defaultItemModem = {
                            id : '',
                            title : ''
                      },
                      self = this;

                  _.each( data, function( _d_, _key_ ) {
                        switch ( _key_ ) {
                              case 'tabs' :
                                    _.each( _d_ , function( _tabData_ ) {
                                          _.each( _tabData_['inputs'], function( _inputData_, _id_ ) {
                                                defaultItemModem[ _id_ ] = _inputData_['default'] || '';
                                          });
                                    });
                              break;
                              default :
                                    defaultItemModem[ _key_ ] = _d_['default'] || '';
                              break;
                        }
                  });
                  return defaultItemModem;
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
                  //console.log('DEFAULT INPUT VALUE NO CACHED', input_id, module_type );
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputDefaultValue => missing sektionsLocalizedData.registeredModules' );
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
                  //console.log('DEFAULT INPUT VALUE NO CACHED', input_id, module_type );
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputType => missing sektionsLocalizedData.registeredModules' );
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
                  //console.log('DEFAULT INPUT VALUE NO CACHED', input_id, module_type );
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules ) ) {
                        api.errare( 'getInputType => missing sektionsLocalizedData.registeredModules' );
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
            }

      });//$.extend()
})( wp.customize, jQuery );