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

            //@return a string "nimble___[skp__global]"
            getGlobalSectionsSettingId : function() {
                  return sektionsLocalizedData.settingIdForGlobalSections;
            },


            // @params = { id : '', level : '' }
            // Recursively walk the level tree until a match is found
            // @return the level model object
            getParentSectionFromColumnId : function( id, collection ) {
                  var self = this, _section_model_ = 'no_match',
                      // @param id mandatory
                      // @param collection mandatory
                      // @param collectionSettingId optional
                      // @param localOrGlobal optional
                      _walk_ = function( id, collection, collectionSettingId, localOrGlobal ) {
                            // do we have a collection ?
                            // if not, let's use the root one
                            if ( _.isUndefined( collection ) ) {
                                  var currentSektionSettingValue = api( collectionSettingId )();
                                  var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                                  collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                            }
                            _.each( collection, function( levelData ) {
                                  // did we found a match recursively ?
                                  if ( 'no_match' != _section_model_ )
                                    return;

                                  var colCandidate;
                                  if ( 'section' == levelData.level ) {
                                        colCandidate = _.findWhere( levelData.collection, { id : id });
                                  }
                                  if ( ! _.isEmpty( colCandidate ) ) {
                                        // we found our column in this section
                                        _section_model_ = levelData;
                                  } else {
                                        if ( _.isArray( levelData.collection ) ) {
                                              _walk_( id, levelData.collection, collectionSettingId, localOrGlobal );
                                        }
                                  }
                            });
                            return _section_model_;
                      };

                  // if a collection has been provided in the signature, let's walk it.
                  // Otherwise, let's walk the local and global ones until a match is found.
                  if ( ! _.isEmpty( collection ) ) {
                        _walk_( id, collection );
                  } else {
                        _.each( {
                              local : self.localSectionsSettingId(),
                              global : self.getGlobalSectionsSettingId()
                        }, function( collectionSettingId, localOrGlobal ) {
                              if ( 'no_match' === _section_model_ ) {
                                    _walk_( id, collection, collectionSettingId, localOrGlobal );
                              }
                        });
                  }

                  return _section_model_;
            },









            //-------------------------------------------------------------------------------------------------
            //-- LOCATION HELPERS
            //-------------------------------------------------------------------------------------------------
            // used in react to preview or update api settings
            // @params is an object {
            //
            // }
            isGlobalLocation : function( params ) {
                  var self = this, is_global_location = false;
                  params = params || {};
                  if ( _.has( params, 'is_global_location' ) ) {
                        is_global_location = params.is_global_location;
                  } else if ( _.has( params, 'scope' ) ) {
                        is_global_location = 'global' === params.scope;
                  } else if ( !_.isEmpty( params.location ) ) {
                        is_global_location = self.isChildOfAGlobalLocation( params.location );
                  } else if ( !_.isEmpty( params.in_sektion ) ) {
                        is_global_location = self.isChildOfAGlobalLocation( params.in_sektion );
                  } else if ( !_.isEmpty( params.id ) ) {
                        is_global_location = self.isChildOfAGlobalLocation( params.id );
                  }
                  return is_global_location;
            },

            // @params = { id : '', level : '' }
            // Recursively walk the level tree until a match is found
            // @return the level model object
            isChildOfAGlobalLocation : function( id ) {
                  var self = this,
                      walkCollection = function( id, collection ) {
                            var _data_ = 'no_match';
                            // do we have a collection ?
                            // if not, let's use the root global one
                            if ( _.isUndefined( collection ) ) {
                                  var currentSettingValue = api( self.getGlobalSectionsSettingId() )();
                                  var sektionSettingValue = _.isObject( currentSettingValue ) ? $.extend( true, {}, currentSettingValue ) : self.getDefaultSektionSettingValue( 'global' );
                                  collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                            }
                            _.each( collection, function( levelData ) {
                                  // did we found a match recursively ?
                                  if ( 'no_match' != _data_ )
                                    return;
                                  if ( id === levelData.id ) {
                                        _data_ = levelData;
                                  } else {
                                        if ( _.isArray( levelData.collection ) ) {
                                              _data_ = walkCollection( id, levelData.collection );
                                        }
                                  }
                            });
                            return _data_;
                      };
                  return walkCollection( id ) !== 'no_match';
            },

            // @return bool
            isGlobalLocationId : function( id ) {
                var _locCollection = this.activeLocationsInfo(),
                    _currentLocInfo = !_.isArray( _locCollection ) ? {} : _.findWhere( _locCollection, { id : id } );
                return _.isObject( _currentLocInfo ) && _currentLocInfo.is_global;
            },

            // @return bool
            // June 2020 : introduced for https://github.com/presscustomizr/nimble-builder-pro/issues/6
            isHeaderLocation : function( id ) {
                  var _locCollection = this.activeLocationsInfo(),
                      _currentLocInfo = !_.isArray( _locCollection ) ? {} : _.findWhere( _locCollection, { id : id } );
                  return _.isObject( _currentLocInfo ) && _currentLocInfo.is_header;
            },

            // @return bool
            // June 2020 : introduced for https://github.com/presscustomizr/nimble-builder-pro/issues/6
            isFooterLocation : function( id ) {
                  var _locCollection = this.activeLocationsInfo(),
                      _currentLocInfo = !_.isArray( _locCollection ) ? {} : _.findWhere( _locCollection, { id : id } );
                  return _.isObject( _currentLocInfo ) && _currentLocInfo.is_footer;
            },



            //-------------------------------------------------------------------------------------------------
            //-- LEVEL HELPERS
            //-------------------------------------------------------------------------------------------------
            // @params = { id : '', level : '' }
            // Recursively walk the level tree until a match is found
            // @return the level model object
            getLevelModel : function( id, collection ) {
                  var self = this, _data_ = 'no_match',
                      // @param id mandatory
                      // @param collection mandatory
                      // @param collectionSettingId optional
                      // @param localOrGlobal optional
                      _walk_ = function( id, collection, collectionSettingId, localOrGlobal ) {
                            // do we have a collection ?
                            // if not, let's use the root one
                            if ( _.isUndefined( collection ) ) {
                                  var currentSektionSettingValue = api( collectionSettingId )();
                                  var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                                  collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                            }
                            _.each( collection, function( levelData ) {
                                  // did we found a match recursively ?
                                  if ( 'no_match' != _data_ )
                                    return;
                                  if ( id === levelData.id ) {
                                        _data_ = levelData;
                                  } else {
                                        if ( _.isArray( levelData.collection ) ) {
                                              _walk_( id, levelData.collection, collectionSettingId, localOrGlobal );
                                        }
                                  }
                            });
                            return _data_;
                      };

                  // if a collection has been provided in the signature, let's walk it.
                  // Otherwise, let's walk the local and global ones until a match is found.
                  if ( ! _.isEmpty( collection ) ) {
                        _walk_( id, collection );
                  } else {
                        _.each( {
                              local : self.localSectionsSettingId(),
                              global : self.getGlobalSectionsSettingId()
                        }, function( collectionSettingId, localOrGlobal ) {
                              if ( 'no_match' === _data_ ) {
                                    _walk_( id, collection, collectionSettingId, localOrGlobal );
                              }
                        });
                  }

                  return _data_;
            },

            getLevelPositionInCollection : function( id, collection ) {
                  var self = this, _position_ = 'no_match',
                  // @param id mandatory
                  // @param collection mandatory
                  // @param collectionSettingId optional
                  // @param localOrGlobal optional
                  _walk_ = function( id, collection, collectionSettingId, localOrGlobal ) {
                        // do we have a collection ?
                        // if not, let's use the root one
                        if ( _.isUndefined( collection ) ) {
                              var currentSektionSettingValue = api( collectionSettingId )();
                              var sektionSettingValue = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                              collection = _.isArray( sektionSettingValue.collection ) ? sektionSettingValue.collection : [];
                        }
                        _.each( collection, function( levelData, _key_ ) {
                              // did we find a match recursively ?
                              if ( 'no_match' != _position_ )
                                return;
                              if ( id === levelData.id ) {
                                    _position_ = _key_;
                              } else {
                                    if ( _.isArray( levelData.collection ) ) {
                                          _walk_( id, levelData.collection, collectionSettingId, localOrGlobal );
                                    }
                              }
                        });
                  };

                  // if a collection has been provided in the signature, let's walk it.
                  // Otherwise, let's walk the local and global ones until a match is found.
                  if ( ! _.isEmpty( collection ) ) {
                        _walk_( id, collection );
                  } else {
                        _.each( {
                              local : self.localSectionsSettingId(),
                              global : self.getGlobalSectionsSettingId()
                        }, function( collectionSettingId, localOrGlobal ) {
                              if ( 'no_match' === _position_ ) {
                                    _walk_( id, collectionSettingId, localOrGlobal, collection );
                              }
                        });
                  }
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
                        level_model.id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();
                        if ( ! _.isEmpty( level_model.collection ) ) {
                              if ( ! _.isArray( level_model.collection ) ) {
                                    throw new Error( 'cloneLevel => the collection must be an array for level id : ' + level_model.id );
                              }
                              _.each( level_model.collection, function( levelData ) {
                                    levelData.id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();
                                    newIdWalker( levelData );
                              });
                        }
                        return level_model;
                  };
                  // recursively walk the provided level sub-tree until all collection ids are updated
                  return newIdWalker( deepClonedLevel );
            },





            //-------------------------------------------------------------------------------------------------
            //-- REGISTRATION HELPERS
            //-------------------------------------------------------------------------------------------------
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
                  if ( sektionsLocalizedData.registeredModules[moduleType].is_father ) {
                        api.errare( 'getDefaultItemModelFromRegisteredModuleData => Father modules should be treated specifically' );
                        return {};
                  }
                  var data = sektionsLocalizedData.registeredModules[ moduleType ].tmpl['item-inputs'],
                      // title, id are always included in the defaultItemModel
                      // title and id are legacy entries that can be used in multi-items modules to identify and name the item
                      // For non multi-items modules, those properties don't need to be saved in database
                      // @see : ::normalizeAndSanitizeSingleItemInputValues()
                      defaultItemModel = {
                            id : '',
                            title : ''
                      },
                      self = this;

                  _.each( data, function( _d_, _key_ ) {
                        switch ( _key_ ) {
                              case 'tabs' :
                                    _.each( _d_ , function( _tabData_ ) {
                                          _.each( _tabData_.inputs, function( _inputData_, _id_ ) {
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

            isMultiItemsModule : function(mod_type) {
                  return sektionsLocalizedData.registeredModules[ mod_type ] && true === sektionsLocalizedData.registeredModules[ mod_type ].is_crud;
            },


            // This method is used to re-generate new unique item ids for multi-items modules.
            // This is needed when a module is duplicated ( or its parent column or section )
            // This is also needed when a pre-build section is injected.
            // @Introduced in July 2019 when coding the accordion module
            //
            // @param data is an object or an array
            // we know we are in an item when :
            // - the item belongs to an array ( the collection of items )
            // - the item is described as an object
            // - the item has an id
            //
            // // additional checkes
            // - the item does not have a level property <= makes sure we don't regenerate ids of level
            // - the id does not start with __nimble__ ( sektionsLocalizedData.prefixForSettingsNotSaved ), which is the characteristic of a level
            maybeGenerateNewItemIdsForCrudModules : function( data ) {
                  var self = this;
                  if ( _.isArray( data ) || _.isObject( data ) ) {
                        _.each( data, function( value ) {
                              if ( _.isArray( data ) && _.isObject( value ) && value.id && !_.has( value, 'level') ) {
                                    if ( -1 === value.id.indexOf(sektionsLocalizedData.prefixForSettingsNotSaved) ) {
                                          value.id = self.guid();
                                    }
                              } else {
                                    if ( _.isArray( value ) || _.isObject( value ) ) {
                                          self.maybeGenerateNewItemIdsForCrudModules( value );
                                    }
                              }
                        });
                  }
                  return data;
            },


            // Walk the main sektion setting and populate an array of google fonts
            // This method is used when processing the 'sek-update-fonts' action to update the .fonts property
            // To be a candidate for sniffing, an input font value  should meet those criteria :
            // 1) be the value of a '{...}_css' input id
            // 2) this input must be a font modifier ( @see 'refresh_fonts' params set on parent module registration )
            // 2) the font should start with [gfont]
            // @param args { is_global_location : bool }
            // @return array
            sniffGFonts : function( args ) {
                  args = args || { is_global_location : false };
                  var self = this,
                  gfonts = [],
                  _snifff_ = function( collectionSettingId, localOrGlobal, level ) {
                        if ( _.isUndefined( level ) ) {
                              var currentSektionSettingValue = api( collectionSettingId )();
                              level = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
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
                                    _snifff_( collectionSettingId, localOrGlobal, levelData );
                              }
                        });
                  };
                  if ( args.is_global_location ) {
                        _snifff_( self.getGlobalSectionsSettingId(), 'global' );
                  } else {
                        _snifff_( self.localSectionsSettingId(), 'local' );
                  }

                  return gfonts;
            },

            // return an array of all fonts currently used in local sections, global sections and global options
            sniffAllFonts : function() {
                  var self = this,
                      Allfonts = [],
                      _snifff_ = function( collectionSettingId, localOrGlobal, level ) {
                            if ( _.isUndefined( level ) ) {
                                  var currentSektionSettingValue = api( collectionSettingId )();
                                  level = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : $.extend( true, {}, self.getDefaultSektionSettingValue( localOrGlobal ) );
                            }
                            _.each( level, function( levelData, _key_ ) {
                                  // example of input_id candidate 'font_family_css'
                                  // if ( _.isString( _key_ ) && _.isString( levelData ) && ( levelData.indexOf('[gfont]') > -1 || levelData.indexOf('[cfont]') > -1 ) && ! _.contains( Allfonts, levelData ) ) {
                                  //       Allfonts.push( levelData );
                                  // }
                                  if ( _.isString( _key_ ) && _.isString( levelData ) && ( levelData.indexOf('[gfont]') > -1 || levelData.indexOf('[cfont]') > -1 ) ) {
                                        Allfonts.push( levelData );
                                  }
                                  if ( _.isArray( levelData ) || _.isObject( levelData ) ) {
                                        _snifff_( collectionSettingId, localOrGlobal, levelData );
                                  }
                            });
                      };

                  // Loop and sniff local sections, global sections, global options
                  _.each( [
                        { id : self.localSectionsSettingId(), scope : 'local' },
                        { id : self.getGlobalSectionsSettingId(), scope : 'global' },
                        { id : sektionsLocalizedData.optNameForGlobalOptions, scope : 'global' }
                  ], function( args ) {
                        _snifff_( args.id, args.scope );
                  });
                  return Allfonts;
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
                  if ( sektionsLocalizedData.registeredModules[module_type].is_father ) {
                        api.errare( 'getInputDefaultValue => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ].tmpl;
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
                  if ( sektionsLocalizedData.registeredModules[module_type].is_father ) {
                        api.errare( 'getInputType => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ].tmpl;
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
                  if ( sektionsLocalizedData.registeredModules[module_type].is_father ) {
                        api.errare( 'getInputRegistrationParams => Father modules should be treated specifically' );
                        return;
                  }
                  if ( _.isUndefined( level ) ) {
                        level = sektionsLocalizedData.registeredModules[ module_type ].tmpl;
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
                  var self = this;
                  if ( ! sektionsLocalizedData.registeredModules ) {
                        api.errare( 'getModuleStartingValue => missing sektionsLocalizedData.registeredModules' );
                        return 'no_starting_value';
                  }
                  if ( _.isUndefined( sektionsLocalizedData.registeredModules[ module_type ] ) ) {
                        api.errare( 'getModuleStartingValue => the module type ' + module_type + ' is not registered' );
                        return 'no_starting_value';
                  }
                  var getStartingValues = function(mod_type ) {
                          return ( sektionsLocalizedData.registeredModules[ mod_type ] && sektionsLocalizedData.registeredModules[ mod_type ].starting_value ) ? sektionsLocalizedData.registeredModules[ mod_type ].starting_value : {};
                      },
                      getChildModuleStartingValues = function(childModType, optGroupName, fatherStartingValues ) {
                            var rawStartValues,
                                readyStartValues;

                            rawStartValues = fatherStartingValues[optGroupName] ? fatherStartingValues[optGroupName] : {};

                            if ( self.isMultiItemsModule(childModType) && _.isArray( rawStartValues ) ) {
                                  readyStartValues = buildMultiItemStartingValues( rawStartValues );
                            } else {
                                  readyStartValues = rawStartValues;
                            }
                            return readyStartValues;
                      },
                      buildMultiItemStartingValues = function( rawStartValues ) {
                            // Exemple of the accordion module
                            // 'children' => array(
                            //     'accord_collec' => 'czr_accordion_collection_child',
                            //     'accord_opts' => 'czr_accordion_opts_child'
                            // ),
                            // => The multi-item module czr_accordion_collection_child will populate the 'accord_collec' option group
                            // We set a 3 items starting value
                            // And we need to generate unique id before injection in the API
                            // 'starting_value' => array(
                            //  'accord_collec' => array(
                            //     array( 'text_content' => 'Lorem ipsum dolor sit amet' ),
                            //     array( 'text_content' => 'Lorem ipsum dolor sit amet' ),
                            //     array( 'text_content' => 'Lorem ipsum dolor sit amet' )
                            //  ),
                            //  'accord_opts' => array()
                            // )
                            readyStartValues = [];
                            _.each( rawStartValues, function( item ) {
                                  if ( ! _.isObject( item ) ) {
                                        api.errare( 'getModuleStartingValue => multi-item module => items should be objects for module ' + mod_type );
                                        return;
                                  }
                                  var clonedItem = $.extend( true, {}, item );
                                  clonedItem.id = api.czr_sektions.guid();
                                  readyStartValues.push( clonedItem );
                            });
                            return readyStartValues;
                      },
                      isFatherModule = function(mod_type) {
                            return sektionsLocalizedData.registeredModules[ mod_type ] && true === sektionsLocalizedData.registeredModules[ mod_type ].is_father;
                      },
                      getChildren = function(mod_type) {
                            return ( sektionsLocalizedData.registeredModules[ mod_type ] && sektionsLocalizedData.registeredModules[ mod_type ].children ) ? sektionsLocalizedData.registeredModules[ mod_type ].children : {};
                      };

                  var startingValueCandidate = {},
                      rawMaybeFatherModuleStartingValue = getStartingValues( module_type );

                  // Prepare starting value
                  // If a module is crud ( multi-items ), we need to generate a unique id for each item
                  // => implemented for https://github.com/presscustomizr/nimble-builder/issues/486
                  // If module_type is a father module, let's loop on the data structure
                  if ( isFatherModule( module_type ) ) {
                        // Structure :
                        // 'children' => array(
                        //     'accord_collec' => 'czr_accordion_collection_child',
                        //     'accord_opts' => 'czr_accordion_opts_child'
                        // ),
                        _.each( getChildren( module_type ), function( childModType, optGroupName ) {
                              var normalizedStartingValues = getChildModuleStartingValues( childModType, optGroupName, rawMaybeFatherModuleStartingValue );
                              if ( ! _.isEmpty( normalizedStartingValues ) ) {
                                    startingValueCandidate[optGroupName] = normalizedStartingValues;
                              }
                        });
                  } else {
                        if ( self.isMultiItemsModule(module_type) && _.isArray( rawMaybeFatherModuleStartingValue ) ) {
                              startingValueCandidate = buildMultiItemStartingValues( rawMaybeFatherModuleStartingValue );
                        } else {
                              startingValueCandidate = rawMaybeFatherModuleStartingValue;
                        }
                  }

                  return _.isEmpty( startingValueCandidate ) ? 'no_starting_value' : startingValueCandidate;
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
            setupSelectInput : function( selectOptions ) {
                  var input  = this,
                      item   = input.input_parent,
                      module = input.module,
                      inputRegistrationParams = api.czr_sektions.getInputRegistrationParams( input.id, input.module.module_type );
                  // use the provided selectOptions if any
                  selectOptions = _.isEmpty( selectOptions ) ? inputRegistrationParams.choices : selectOptions;

                  // allow selectOptions to be filtrable remotely when the options are not passed on registration for example
                  // @see widget are module in initialize() for example
                  var filtrable = { params : selectOptions };
                  input.module.trigger( 'nimble-set-select-input-options', filtrable );
                  selectOptions = filtrable.params;

                  if ( _.isEmpty( selectOptions ) || ! _.isObject( selectOptions ) ) {
                        api.errare( 'api.czr_sektions.setupSelectInput => missing select options for input id => ' + input.id + ' in module ' + input.module.module_type );
                        return;
                  } else {
                        switch( input.type ) {
                              case 'simpleselect' :
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
                              break;
                              case 'multiselect' :
                                    // when select is multiple, the value is an array
                                    var input_value = input();
                                    input_value = _.isString( input_value ) ? [ input_value ] : input_value;
                                    input_value = !_.isArray( input_value ) ? [] : input_value;

                                    //generates the options
                                    _.each( selectOptions , function( title, value ) {
                                          var _attributes = {
                                                    value : value,
                                                    html: title
                                              };
                                          if ( _.contains( input_value, value ) ) {
                                                $.extend( _attributes, { selected : "selected" } );
                                          }
                                          $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                    });
                                    // see how the tmpl is rendered server side in PHP with ::ac_set_input_tmpl_content()
                                    $( 'select[data-czrtype]', input.container ).czrSelect2({
                                          closeOnSelect: true,
                                          templateSelection: function czrEscapeMarkup(obj) {
                                                //trim dashes
                                                return obj.text.replace(/\u2013|\u2014/g, "");
                                          }
                                    });

                                    //handle case when all choices become unselected
                                    $( 'select[data-czrtype]', input.container ).on('change', function(){
                                          if ( 0 === $(this).find("option:selected").length ) {
                                                input([]);
                                          }
                                    });
                              break;
                              default :
                                    api.errare( '::setupSelectInput => invalid input type => ' + input.type );
                              break;
                        }
                  }
            },



            //-------------------------------------------------------------------------------------------------
            // PREPARE INPUT REGISTERED WITH has_device_switcher set to true
            //-------------------------------------------------------------------------------------------------
            // "this" is the input
            maybeSetupDeviceSwitcherForInput : function() {
                  var input = this;
                  // If this has already been done, let's stop now
                  if ( input.previewedDevice )
                    return;
                  // render the device switcher before the input title
                  var deviceSwitcherHtml = [
                        '<span class="sek-input-device-switcher">',
                          '<i data-sek-device="desktop" class="sek-switcher preview-desktop active" title="'+ sektionsLocalizedData.i18n['Settings on desktops'] +'"></i>',
                          '<i data-sek-device="tablet" class="sek-switcher preview-tablet" title="'+ sektionsLocalizedData.i18n['Settings on tablets'] +'"></i>',
                          '<i data-sek-device="mobile" class="sek-switcher preview-mobile" title="'+ sektionsLocalizedData.i18n['Settings on mobiles'] +'"></i>',
                        '</span>'
                  ].join(' ');

                  input.container.find('.customize-control-title').first().prepend( deviceSwitcherHtml );
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
                        var sectionIdToBeFocusedOn;

                        // focus on the parent section
                        // only when really clicked (not triggered)
                        // fixes https://github.com/presscustomizr/nimble-builder/issues/512
                        if ( _.isUndefined( evt.isTrigger ) && input.module && input.module.control && input.module.control.params.section ) {
                              sectionIdToBeFocusedOn = input.module.control.params.section;
                              _.delay( function() {
                                    api.previewer.send('sek-animate-to-level', { id : sectionIdToBeFocusedOn });
                              }, 600 );
                        }


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
            // @params { expand_first_control : boolean }
            scheduleModuleAccordion : function( params ) {
                  params = params || { expand_first_control : true };
                  var _section_ = this;
                  // Void if already done
                  if( true === _section_.container.data('sek-module-accordion-has-been-setup') )
                    return;

                  // Attach event on click
                  $( _section_.container ).on( 'click', '.customize-control label > .customize-control-title', function( evt ) {
                        //evt.preventDefault();
                        evt.stopPropagation();

                        // close various dialog UI
                        api.czr_sektions.levelTreeExpanded(false);
                        api.czr_sektions.templateGalleryExpanded( false );
                        api.czr_sektions.saveSectionDialogVisible(false);
                        api.czr_sektions.tmplDialogVisible(false);
                        api.czr_sektions.tmplInjectDialogVisible(false);

                        var $control = $(this).closest( '.customize-control');

                        if ( "no" === $control.attr( 'data-sek-accordion' ))
                          return;

                        _section_.container.find('.customize-control').not( $control ).each( function() {
                              if ( $(this).attr( 'data-sek-accordion' ) )
                                return;
                              $(this).attr('data-sek-expanded', "false" );
                        });
                        $control.attr('data-sek-expanded', "false" == $control.attr('data-sek-expanded') ? "true" : "false" );

                        // this event 'sek-accordion-expanded', is used to defer the instantiation of the code editor
                        // @see api.czrInputMap['code_editor']
                        // @see https://github.com/presscustomizr/nimble-builder/issues/176
                        $control.trigger( "true" == $control.attr('data-sek-expanded') ? 'sek-accordion-expanded' : 'sek-accordion-collapsed' );
                        if ( true === $control.data('module_ready_on_custom_control_event_is_setup' ) ) {
                              $control.trigger( 'sek-accordion-expanded' );
                        } else {
                              $control.one('module_ready_on_custom_control_event_is_setup', function() {
                                    $control.trigger( 'sek-accordion-expanded' );
                              });

                        }
                  });

                  _section_.container.data('sek-module-accordion-has-been-setup', true );

                  // Expand the first module if requested
                  if ( params.expand_first_control ) {
                        // we want to exclude controls for which the accordion is not scheduled
                        // introduced when implementing the module option switcher in july 2019. @see https://github.com/presscustomizr/nimble-builder/issues/135
                        var _eligibleControls = _.filter( _section_.controls(), function( _ctrl_ ) {
                              if ( _ctrl_.params && _ctrl_.params.sek_registration_params ) {
                                  return false !== _ctrl_.params.sek_registration_params.has_accordion;
                              }
                              return true;
                        });

                        var firstControl = _.first( _eligibleControls );

                        if ( _.isObject( firstControl ) && ! _.isEmpty( firstControl.id ) ) {
                              api.control( firstControl.id, function( _ctrl_ ) {
                                    // this event is triggered by the control fmk in module.isReady.done( function() {} )
                                    // we need to defer the revealing of the module content when item collection is ready, otherwise it's too early.
                                    // because the item collection can be ready after the module.isReady() has been resolved.
                                    // see also https://github.com/presscustomizr/themes-customizer-fmk/commit/1f9fb0045d12dd3af9f4fdd880210dc3183fd63a
                                    _ctrl_.container.one('items-collection-populated', function() {
                                          $(this).find('label > .customize-control-title').trigger('click');
                                    });

                                    // remotely request a module.ready()
                                    // see CZRModuleMths::initialize
                                    // => then once module is ready and all items populated, the event 'items-collection-populated' is triggered on the control, and we can reveal the module content/.

                                    // Nov 2020 => make sure the setup has been done on the control so that it reacts on a on custom event ( here 'sek-accordion-expanded' )
                                    // see fmk CZRModuleMths::initialize
                                    // event defined on module registration with api.czrModuleMap[ module.module_type ].ready_on_section_expanded
                                    if ( true === _ctrl_.container.data('module_ready_on_custom_control_event_is_setup' ) ) {
                                          _ctrl_.container.trigger( 'sek-accordion-expanded' );
                                    } else {
                                          _ctrl_.container.one('module_ready_on_custom_control_event_is_setup', function() {
                                                _ctrl_.container.trigger( 'sek-accordion-expanded' );
                                          });
                                    }
                              });
                        }
                  }
            },



            //-------------------------------------------------------------------------------------------------
            // HELPERS USED WHEN UPLOADING IMAGES FROM PRESET SECTIONS
            //-------------------------------------------------------------------------------------------------
            isPromise : function (fn) {
                  return fn && typeof fn.then === 'function' && String( $.Deferred().then ) === String( fn.then );
            },

            // @param deferreds = { '__img_url__/assets/img/tests/1.jpg' : 'dfd1', '__img_url__/assets/img/tests/2.jpg' : dfd2, ..., '__img_url__/assets/img/tests/n.jpg' : dfdn }
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


            // @param attachment_url = string : '/assets/img/41883.jpg'
            // @return a promise
            importAttachment : function( attachment_url ) {
                  // @see php wp_ajax_sek_import_attachment
                  return wp.ajax.post( 'sek_import_attachment', {
                        img_url : attachment_url,
                        nonce: api.settings.nonce.save//<= do we need to set a specific nonce to fetch the attachment
                  })
                  .fail( function( _er_ ) {
                        api.errare( 'sek_import_attachment ajax action failed for image ' +  attachment_url, _er_ );
                  });
                  // .done( function( data) {
                  //       api.infoLog('relpath and DATA ' + relpath , data );
                  // });
            },






            // recursive helper
            // used when saving a section
            cleanIds : function( levelData ) {
                  levelData.id = "";
                  var self = this;
                  _.each( levelData.collection, function( levelData ) {
                        levelData.id = "";
                        if ( _.isArray( levelData.collection ) ) {
                              self.cleanIds( levelData );
                        }
                  });
                  return levelData;
            },

            // @return { collection[] ... }
            getDefaultSektionSettingValue : function( localOrGlobal ) {
                  if ( _.isUndefined( localOrGlobal ) || !_.contains( [ 'local', 'global' ], localOrGlobal ) ) {
                        api.errare( 'getDefaultSektionSettingValue => the skope should be set to local or global');
                  }
                  return 'global' === localOrGlobal ? sektionsLocalizedData.defaultGlobalSektionSettingValue : sektionsLocalizedData.defaultLocalSektionSettingValue;
            },

            // @return void()
            // input controller instance == this
            scheduleVisibilityOfInputId : function( controlledInputId, visibilityCallBack ) {
                  var item = this.input_parent;
                  if ( !_.isFunction(visibilityCallBack) || _.isEmpty(controlledInputId) ) {
                        api.errare('::scheduleVisibilityOfInputId => error when firing for input id : ' + this.id );
                        return;
                  }
                  if ( !item.czr_Input.has( controlledInputId ) ) {
                        api.errare('::scheduleVisibilityOfInputId => missing input id : ' + controlledInputId );
                        return;
                  }
                  //Fire on init
                  item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                  //React on change
                  this.bind( function( to ) {
                        item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                  });
            },


            //-------------------------------------------------------------------------------------------------
            //-- TEMPLATE HELPERS
            // Dec 2020
            //-------------------------------------------------------------------------------------------------

            // @return boolean
            // introduced when implementing #655
            // When importing a template, if current page has NB sections, out of header and footer, display an import dialog, otherwise import now
            hasCurrentPageNBSectionsNotHeaderFooter : function() {
                var self = this,
                    _bool = false,
                    _collection,
                    localCollSetId = this.localSectionsSettingId(),
                    localColSetValue = api(localCollSetId)();

                localColSetValue = _.isObject( localColSetValue ) ? localColSetValue : {};
                _collection = $.extend( true, {}, localColSetValue );
                _collection = ! _.isEmpty( _collection.collection ) ? _collection.collection : [];
                _collection = _.isArray( _collection ) ? _collection : [];
                _.each( _collection, function( loc_data ){
                      if ( _bool )
                        return;
                      if ( _.isObject(loc_data) && 'location' == loc_data.level && loc_data.collection ) {
                            // skip if the location is a header
                            if ( !self.isHeaderLocation( loc_data.id ) && !self.isFooterLocation( loc_data.id ) ) {
                                  _bool = !_.isEmpty( loc_data.collection );
                            }
                      }
                });
                return _bool;
            },


            //-------------------------------------------------------------------------------------------------
            //-- SITE TEMPLATE HELPERS
            //-------------------------------------------------------------------------------------------------
            // localSektionsInheritsGroupSkope : function() {
            //       var localCollSetId = this.localSectionsSettingId(),
            //             localColSetValue = api(localCollSetId)();
            //       return localColSetValue && localColSetValue.__inherits_group_skope_tmpl_when_exists__;
            // },

            // @return bool
            // hasLocalSektions : function() {
            //       var self = this,
            //           _bool = false,
            //           _collection,
            //           localCollSetId = this.localSectionsSettingId(),
            //           localColSetValue = api(localCollSetId)();
  
            //       localColSetValue = _.isObject( localColSetValue ) ? localColSetValue : {};
            //       _collection = $.extend( true, {}, localColSetValue );
            //       _collection = ! _.isEmpty( _collection.collection ) ? _collection.collection : [];
            //       _collection = _.isArray( _collection ) ? _collection : [];
            //       _.each( _collection, function( loc_data ){
            //             if ( _bool )
            //               return;
            //             if ( _.isObject(loc_data) && 'location' == loc_data.level && loc_data.collection ) {
            //                   _bool = !_.isEmpty( loc_data.collection );
            //             }
            //       });
            //       // on a reset, property __inherits_group_skope_tmpl_when_exists__ is set to true server side
            //       return _bool && !( localColSetValue && localColSetValue.__inherits_group_skope_tmpl_when_exists__ );
            // },

            // Added April 2021 for #478
            // When a page has not been locally customized, property __inherits_group_skope_tmpl_when_exists__ is true ( @see sek_get_default_location_model() )
            // As soon as the main local setting id is modified, __inherits_group_skope_tmpl_when_exists__ is set to false ( see js control::updateAPISetting )
            // After a reset case, NB sets __inherits_group_skope_tmpl_when_exists__ back to true ( see js control:: resetCollectionSetting )
            // Note : If this property is set to true => NB removes the local skope post in Nimble_Collection_Setting::update()
            hasLocalSettingBeenCustomized : function() {
                  var self = this,
                      _bool = false,
                      _collection,
                      localCollSetId = this.localSectionsSettingId(),
                      localColSetValue = api(localCollSetId)();
  
                  localColSetValue = _.isObject( localColSetValue ) ? localColSetValue : {};

                  // on a reset, property __inherits_group_skope_tmpl_when_exists__ is set to true server side
                  return !( localColSetValue && localColSetValue.__inherits_group_skope_tmpl_when_exists__ );
            },

            // Js version of php helper sek_get_group_skope_for_site_tmpl()
            // April 2021 for site template
            // Why this method?
            // the site_templates key are intended to match exactly the skope ids, as generated by NB skope system
            // But there are exceptions for some skopes that have no "group skopes" and for which we've added the suffix "for_site_tmpl"
            // So when registering site template global options the suffix '_for_site_tmpl' is added to :
            // 'no group skope' scopes : 'skp__search_for_site_tmpl', 'skp__404_for_site_tmpl', 'skp__date_for_site_tmpl'
            getGroupSkopeForSiteTemplate : function() {
                  var _groupSkope = api.czr_skopeBase.getSkopeProperty( 'skope_id', 'group' ),
                        _skopeId;
                  if ( '_skope_not_set_' === _groupSkope ) {
                        var _isNoGroupSkope = function( localskopeId ) {
                              if ( FlatSkopeLocalizedData && _.isArray( FlatSkopeLocalizedData.noGroupSkopeList ) ) {
                                    localskopeId = _.isString( _skopeId ) ?  _skopeId : '_skope_not_set_';
                                    localskopeId = localskopeId.replace( 'skp__', '' );
                                    return _.contains( FlatSkopeLocalizedData.noGroupSkopeList , localskopeId );
                              }
                              api.errare( ':getGroupSkopeForSiteTemplate => missing localized data FlatSkopeLocalizedData.noGroupSkopeList' );
                              return false;
                        };

                        _skopeId = api.czr_skopeBase.getSkopeProperty( 'skope_id', 'local' );
                        if ( _isNoGroupSkope( _skopeId ) ) {
                              _groupSkope = _skopeId + '_for_site_tmpl';
                        } else {
                              api.errare('::getGroupSkopeForSiteTemplate => group skope could not be set');
                        }
                  }
                  return _groupSkope;
            },


            //-------------------------------------------------------------------------------------------------
            //-- VARIOUS
            //-------------------------------------------------------------------------------------------------
            isJsonString : function(str) {
                  try {
                        JSON.parse(str);
                  } catch (e) {
                        return false;
                  }
                  return true;
            }
      });//$.extend()
})( wp.customize, jQuery );