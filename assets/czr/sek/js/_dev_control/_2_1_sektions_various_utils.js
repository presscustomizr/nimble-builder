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
            // @return {}
            getDefaultItemModelFromRegisteredModuleData : function( moduleId ) {
                  var data = sektionsLocalizedData.registeredModules[ moduleId ]['tmpl']['item-inputs'],
                      defaultItemModem = {},
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

            // Walk the main sektion setting and populate an array of google fonts
            // This method is used when processing the 'sek-update-fonts' action to update the .fonts property
            // To be a candidate for sniffing, a google font should meet 2 criteria :
            // 1) be the value of a 'font_family_css' property
            // 2) start with [gfont]
            // @return array
            sniffGFonts : function( gfonts, level ) {
                  var self = this;
                  gfonts = gfonts || [];

                  if ( _.isUndefined( level ) ) {
                        var currentSektionSettingValue = api( self.sekCollectionSettingId() )();
                        level = _.isObject( currentSektionSettingValue ) ? $.extend( true, {}, currentSektionSettingValue ) : self.defaultSektionSettingValue;
                  }
                  _.each( level, function( levelData, _key_ ) {
                        if ( 'font_family_css' == _key_ ) {
                              if ( levelData.indexOf('gfont') > -1 && ! _.contains( gfonts, levelData ) ) {
                                    gfonts.push( levelData );
                              }
                        }

                        if ( _.isArray( levelData ) || _.isObject( levelData ) ) {
                              self.sniffGFonts( gfonts, levelData );
                        }
                  });
                  return gfonts;
            },


            // @return a mixed type default value
            // @param input_id string
            // @param module_type string
            // @param level array || object
            getInputDefaultValue : function( input_id, module_type, level ) {
                  var self = this;
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
                  });
                  return _defaultVal_;
            }
      });//$.extend()
})( wp.customize, jQuery );