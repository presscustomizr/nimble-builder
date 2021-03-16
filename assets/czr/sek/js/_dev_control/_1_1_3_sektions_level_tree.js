//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::setupTopBar(), at api.bind( 'ready', function() {})
            setupLevelTree : function() {
                  var self = this, stringifiedLTVal;
                  self.levelTree = new api.Value([]);
                  self.levelTree.bind( function( val ) {
                        // Refresh when the collection is being modified from the tree
                        if ( self.levelTreeExpanded() ) {
                              self.renderOrRefreshTree();
                        }
                  });

                  // March 2021 => highlight level tree button in blue if NB levels already inserted.
                  var maybeHighlightCtrlButton = function( val ) {
                        try { stringifiedLTVal = JSON.stringify( val ); } catch(er) {
                              api.errorLog('::setupLevelTree => error when JSON.stringify Level Tree');
                        }
                        if ( !_.isString( stringifiedLTVal ) )
                              return;
                        // look for a NB level id starting looking like __nimble__986b1c3921fe
                        if ( -1 !== stringifiedLTVal.indexOf('__nimble__') ) {
                              $('.sek-level-tree button', self.topBarId).css('color', '#46d2ff' );
                        } else {
                              $('.sek-level-tree button', self.topBarId).css('color', '' );
                        }
                  };
                  self.levelTree.bind( _.debounce( function(val) {
                        maybeHighlightCtrlButton( val );
                  }, 1000 ));
                  // Initial Button state based on the current tree value
                  $('#customize-preview').one('nimble-top-bar-rendered', function() {
                        maybeHighlightCtrlButton( self.setLevelTreeValue() );
                  });

                  // SETUP AND REACT TO LEVEL TREE EXPANSION
                  self.levelTreeExpanded = new api.Value(false);
                  self.levelTreeExpanded.bind( function(expanded) {
                        self.cachedElements.$body.toggleClass( 'sek-level-tree-expanded', expanded );
                        if ( expanded ) {
                              // Close template gallery, template saver, section saver
                              self.templateGalleryExpanded(false);
                              self.tmplDialogVisible(false);
                              if ( self.saveSectionDialogVisible ) {
                                    self.saveSectionDialogVisible(false);
                              }

                              // Set the level tree now
                              self.setLevelTreeValue();

                              // Make sure we the tree is set first
                              if ( _.isEmpty( self.levelTree() ) ) {
                                    api.previewer.trigger('sek-notify', {
                                          type : 'info',
                                          duration : 10000,
                                          message : [
                                                '<span style="font-size:0.95em">',
                                                  '<strong>' + sektionsLocalizedData.i18n['No sections to navigate'] + '</strong>',
                                                '</span>'
                                          ].join('')
                                    });
                                    // self disable
                                    self.levelTreeExpanded(false);
                                    return;
                              }
                              $('#customize-preview iframe').css('z-index', 1);
                              self.renderOrRefreshTree();
                        } else if ( $('#nimble-level-tree').length > 0 ) {
                              _.delay( function() {
                                    $('#nimble-level-tree').remove();
                                    $('#customize-preview iframe').css('z-index', '');
                              }, 300 );
                        }
                  });

                  // REFRESH THE TREE WHEN THE ACTIVE LOCATIONS CHANGE
                  // @see ::initialize to understand how active locations are updated
                  self.activeLocations.bind(function() {
                        if ( !_.isEmpty( self.levelTree() ) ) {
                              self.renderOrRefreshTree();
                        }
                  });

                  // API READY
                  api.previewer.bind('ready', function() {
                        // LEVEL TREE
                        // on each skope change
                        // - set the level tree
                        // - bind the local and global settings so that they refresh the level tree when changed
                        self.localSectionsSettingId.callbacks.add( function() {
                              self.levelTreeExpanded(false);
                              // Bind the global and local settings if not bound yet
                              _.each( [ self.getGlobalSectionsSettingId(), self.localSectionsSettingId(), sektionsLocalizedData.optNameForGlobalOptions ], function( setId ){
                                    if ( api(setId)._isBoundForNimbleLevelTree )
                                      return;

                                    api(setId).bind( function(to) {
                                          self.setLevelTreeValue();
                                    });
                                    api(setId)._isBoundForNimbleLevelTree = true;
                              });
                        });
                  });



                  // SETUP CLICK EVENTS IN THE TREE
                  self.cachedElements.$body.on('click', '#nimble-level-tree [data-nimb-level]', function(evt) {
                        evt.preventDefault();
                        evt.stopPropagation();
                        var $el = $(evt.target),
                            $closestLevel = $el.closest('[data-nimb-level]');
                        api.previewer.send('sek-animate-to-level', { id : $closestLevel.data('nimb-id') });
                        api.previewer.send('sek-clean-level-uis');
                        // Display the level ui in the preview
                        // and expand the level options in the customizer control panel
                        _.delay( function() {
                              api.previewer.send('sek-display-level-ui', { id : $closestLevel.data('nimb-id') });

                              var _id = $closestLevel.data('nimb-id'),
                                  _level = $closestLevel.data('nimb-level');

                              if ( 'column' === _level || 'section' === _level ) {
                                    api.previewer.trigger('sek-edit-options', { id : _id, level : _level });
                              } else if ( 'module' === _level ) {
                                    api.previewer.trigger('sek-edit-module', { id : _id, level : _level });
                              }
                        }, 100 );
                  });

                  self.cachedElements.$body.on('click', '#nimble-level-tree .sek-remove-level', function(evt) {
                        evt.preventDefault();
                        evt.stopPropagation();
                        var $el = $(evt.target).closest('[data-nimb-level]');
                        api.previewer.trigger('sek-remove', {
                              level : $el.data('nimb-level'),
                              id : $el.data('nimb-id'),
                              location : $el.closest('[data-nimb-level="location"]').data('nimb-id'),
                              in_sektion : $el.closest('[data-nimb-level="section"]').data('nimb-id'),
                              in_column : $el.closest('[data-nimb-level="column"]').data('nimb-id')
                        });
                        $el.fadeOut('slow');
                        // Refresh
                        self.renderOrRefreshTree();
                  });

                  // Collapse tree ( also possible by clicking on the tree icon in the top Nimble bar )
                  self.cachedElements.$body.on('click', '.sek-close-level-tree' , function(evt) {
                        evt.preventDefault();
                        self.levelTreeExpanded(false);
                  });
            },

            // This method updates the levelTree observable api.Value()
            setLevelTreeValue : function() {
                  var self = this,
                      globalCollSetId = self.getGlobalSectionsSettingId(),
                      localCollSetId = self.localSectionsSettingId(),
                      globalOptionSetId = sektionsLocalizedData.optNameForGlobalOptions,
                      globalColSetValue, localColSetValue,
                      globalCollection, localCollection,
                      rawGlobalOptionsValue,
                      missingDependantSettingId = false;

                  // Check if all dependant settings are registered
                  // we won't go further if any of the 3 setting id's is not yet registered
                  _.each( [globalCollSetId, localCollSetId, globalOptionSetId ], function( setId ) {
                        if ( !api.has(setId) ) {
                              missingDependantSettingId = setId;
                              return;
                        }
                  });

                  if ( false !== missingDependantSettingId ) {
                        api.errare( '::setLevelTreeValue => a setting id is not registered ');
                        return;
                  }

                  // Normalizes the setting values
                  globalColSetValue = api(globalCollSetId)();
                  globalCollection = _.isObject( globalColSetValue ) ? $.extend( true, {}, globalColSetValue ) : {};
                  globalCollection = ! _.isEmpty( globalCollection.collection )? globalCollection.collection : [];
                  globalCollection = _.isArray( globalCollection ) ? globalCollection : [];

                  localColSetValue = api(localCollSetId)();
                  localColSetValue = _.isObject( localColSetValue ) ? localColSetValue : {};
                  localCollection = $.extend( true, {}, localColSetValue );
                  localCollection = ! _.isEmpty( localCollection.collection ) ? localCollection.collection : [];
                  localCollection = _.isArray( localCollection ) ? localCollection : [];

                  var raw_col = _.union( globalCollection, localCollection ),
                      local_header_footer_value,
                      global_header_footer_value,
                      has_local_header_footer = false,
                      has_global_header_footer = false;

                  rawGlobalOptionsValue = api( globalOptionSetId )();
                  rawGlobalOptionsValue = _.isObject( rawGlobalOptionsValue ) ? rawGlobalOptionsValue : {};

                  // HEADER-FOOTER => do we have a header-footer set, local or global ?
                  // LOCAL
                  if ( localColSetValue.local_options && localColSetValue.local_options.local_header_footer ) {
                        local_header_footer_value = localColSetValue.local_options.local_header_footer['header-footer'];
                        has_local_header_footer = 'nimble_local' === local_header_footer_value;
                  }

                  // GLOBAL
                  // there can be a global header footer if
                  // 1) local is not set to 'nimble_local' or 'theme'
                  // and
                  // 2) the global option is set to 'nimble_global'
                  //
                  // OR when
                  // 1) local is set to 'nimble_global'
                  if ( rawGlobalOptionsValue.global_header_footer && !has_local_header_footer && 'theme' !== local_header_footer_value) {
                        global_header_footer_value = rawGlobalOptionsValue.global_header_footer['header-footer'];
                        has_global_header_footer = 'nimble_global' === global_header_footer_value || 'nimble_global' === local_header_footer_value;
                  }

                  var filteredCollection = $.extend( true, [], raw_col ),
                      header_loc,
                      footer_loc;

                  filteredCollection = _.filter( filteredCollection, function( loc, key ) {
                      return !_.contains( ['nimble_global_header', 'nimble_global_footer', 'nimble_local_header', 'nimble_local_footer'], loc.id );
                  });

                  // RE-ORGANIZE LOCATIONS SO THAT WE HAVE
                  // - header
                  // - content loc #1
                  // - content loc #2
                  // - ...
                  // - footer
                  var wrapContentLocationWithHeaderFoooterLocations = function( scope ) {
                        header_loc = _.findWhere(raw_col, {id:'nimble_' + scope + '_header'});
                        footer_loc = _.findWhere(raw_col, {id:'nimble_' + scope + '_footer'});
                        filteredCollection.unshift(header_loc);
                        filteredCollection.push(footer_loc);
                  };
                  if ( has_local_header_footer ) {
                        wrapContentLocationWithHeaderFoooterLocations('local');
                  } else if ( has_global_header_footer ) {
                        wrapContentLocationWithHeaderFoooterLocations('global');
                  }

                  // RE-ORDER LOCATIONS IN THE SAME ORDER AS THEY ARE IN THE DOM
                  // @see ::initialize to understand how active locations are updated
                  var contextuallyActiveLocactions = self.activeLocations(),
                      orderedCollection = [],
                      candidate;
                  if ( !_.isEmpty(contextuallyActiveLocactions) ) {
                        _.each( contextuallyActiveLocactions, function( loc ) {
                              candidate = _.findWhere(filteredCollection, {id:loc});
                              if( !_.isUndefined(candidate) ) {
                                    orderedCollection.push(candidate);
                              }
                        });
                  } else {
                        orderedCollection = filteredCollection;
                  }

                  // Store it now
                  self.levelTree( orderedCollection );
                  return orderedCollection;
            },


            // print the tree
            renderOrRefreshTree : function() {
                  var self = this,
                      _tmpl;
                  if( $('#nimble-level-tree').length < 1 ) {
                        // RENDER
                        try {
                              _tmpl =  wp.template( 'nimble-level-tree' )( {} );
                        } catch( er ) {
                              api.errare( 'Error when parsing the nimble-level-tree template', er );
                              return false;
                        }
                        $( '#customize-preview' ).after( $( _tmpl ) );
                  }
                  $('#nimble-level-tree').find('.sek-tree-wrap').html( self.getLevelTreeHtml() );
            },

            // recursive helper
            // return an html string describing the contextually printed sections
            getLevelTreeHtml : function( _col, level ) {
                  var self = this;
                  _col = _col || self.levelTree();

                  var levelType,
                      levelName,
                      _html,
                      skipLevel = false;

                  if ( !_.isArray( _col ) || _.isEmpty( _col ) ) {
                        api.errare('::buildLevelTree => invalid collection param', _col );
                        return _html;
                  }
                  var remove_icon_html = '<i class="material-icons sek-remove-level" title="'+ sektionsLocalizedData.i18n['Remove this element'] +'">delete_forever</i>';
                  _html = '<ul>';
                  _.each( _col, function( _level_param ) {
                        if ( _.isUndefined( _level_param.level ) ){
                              api.errare('::buildLevelTree => missing level property', _level_param );
                              return;
                        }
                        if ( _.isUndefined( _level_param.id ) ){
                              api.errare('::buildLevelTree => missing id property', _level_param );
                              return;
                        }

                        // Set some vars now
                        levelType = _level_param.level;
                        levelName = levelType;

                        // if the level is a location, is this location contextually active ?
                        // @see ::initialize to understand how active locations are updated
                        if ( 'location' === levelType ) {
                              skipLevel = !_.contains( self.activeLocations(), _level_param.id );
                        }

                        if ( !skipLevel ) {
                              //try to get the i18n level name, fall back on the level type
                              if ( sektionsLocalizedData.i18n[levelType] ) {
                                    levelName = sektionsLocalizedData.i18n[levelType];
                              }
                              if ( true === _level_param.is_nested ) {
                                    levelName = sektionsLocalizedData.i18n['nested section'];
                              }

                              remove_icon_html = 'location' !== levelType ? remove_icon_html : '';
                              _html += '<li data-nimb-level="'+levelType+'" data-nimb-id="'+_level_param.id+'">';

                                _html += '<div class="sek-level-infos"><div class="sek-inner-level-infos">';
                                  // add module type and icon
                                  if ( 'module' === levelType ) {
                                        _html += [
                                              self.getTreeModuleIcon( _level_param.module_type ),
                                              self.getTreeModuleTitle( _level_param.module_type )
                                        ].join(' ');
                                  }
                                  // add the rest of the html, common to all elements
                                  _html += [
                                        ' ',
                                        levelName,
                                        '( id :',
                                        _level_param.id,
                                        ')',
                                        remove_icon_html
                                  ].join(' ');
                                _html += '</div></div>';

                                if ( _.isArray( _level_param.collection ) && ! _.isEmpty( _level_param.collection ) ) {
                                      _html += self.getLevelTreeHtml( _level_param.collection, level );
                                }
                              _html += '</li>';
                        }//if ( !skipLevel )
                  });//_.each

                  _html += '</ul>';

                  return _html;
            },

            // the module icons can be
            // an svg file like Nimble__divider_icon.svg => in this case we build and return the full url
            // or a font_icon like '<i class="fab fa-wordpress-simple"></i>'
            getTreeModuleIcon : function( modType ) {
                  var _icon = {}, icon_img_src;
                  _.each( sektionsLocalizedData.moduleCollection, function( modData ) {
                        if ( !_.isEmpty( _icon ) )
                          return;
                        if ( modType === modData['content-id'] ) {
                              if ( !_.isEmpty( modData.icon ) ) {
                                    if ( 'http' === modData.icon.substring(0, 4) ) {
                                          icon_img_src = modData.icon;
                                    } else {
                                          icon_img_src = sektionsLocalizedData.moduleIconPath + modData.icon;
                                    }
                                    _icon = {
                                          svg : modData.icon ? icon_img_src : '',
                                          font : modData.font_icon ? modData.font_icon : ''
                                    };
                              }
                        }
                  });
                  if ( !_.isEmpty( _icon.svg ) ) {
                        return '<img class="sek-svg-mod-icon" src="' + _icon.svg + '"/>';
                  } else if ( !_.isEmpty( _icon.font ) ) {
                        return _icon.font;
                  }
            },

            getTreeModuleTitle : function( modType ) {
                  var _title = {};
                  _.each( sektionsLocalizedData.moduleCollection, function( modData ) {
                        if ( !_.isEmpty( _title ) )
                          return;
                        if ( modType === modData['content-id'] ) {
                              _title = modData.title;
                        }
                  });
                  return _title;
            }
      });//$.extend()
})( wp.customize, jQuery );
