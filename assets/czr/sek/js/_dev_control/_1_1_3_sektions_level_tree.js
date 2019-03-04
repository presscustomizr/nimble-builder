//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::setupTopBar(), at api.bind( 'ready', function() {})
            setupLevelTree : function() {
                  var self = this;
                  self.levelTree = new api.Value([]);
                  self.levelTree.bind( function() {
                        //console.log('Level Tree changed => ', _collection);
                        // Refresh when the collection is being modified from the tree
                        if ( self.levelTreeExpanded() ) {
                              self.renderOrRefreshTree();
                        }
                  });


                  // SETUP AND REACT TO LEVEL TREE EXPANSION
                  self.levelTreeExpanded = new api.Value(false);
                  self.levelTreeExpanded.bind( function(expanded) {
                        $('body').toggleClass( 'sek-level-tree-expanded', expanded );
                        if ( expanded ) {
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


                  // API READY
                  api.previewer.bind('ready', function() {
                        // LEVEL TREE
                        // on each skope change
                        // - set the level tree
                        // - bind the local and global settings so that they refresh the level tree when changed
                        self.localSectionsSettingId.callbacks.add( function() {
                              // Set the initial levelTreeValue when settings are registered
                              // api.when( self.getGlobalSectionsSettingId(), self.localSectionsSettingId(), function( _global_, _local_ ) {
                              //       self.setLevelTreeValue();
                              // });

                              // Bind the global and local settings if not bound yet
                              _.each( [ self.getGlobalSectionsSettingId(), self.localSectionsSettingId() ], function( setId ){
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
                  $('body').on('click', '#nimble-level-tree [data-nimb-level]', function(evt) {
                        evt.preventDefault();
                        evt.stopPropagation();
                        var $el = $(evt.target),
                            $closestLevel = $el.closest('[data-nimb-level]');
                        api.previewer.send('sek-animate-to-level', { id : $closestLevel.data('nimb-id') });
                        api.previewer.send('sek-display-level-ui', { id : $closestLevel.data('nimb-id') });
                  });

                  $('body').on('click', '#nimble-level-tree .sek-remove-level', function(evt) {
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
                  $('body').on('click', '.sek-close-level-tree' , function(evt) {
                        evt.preventDefault();
                        self.levelTreeExpanded(false);
                  });
            },


            // @return boolean
            hasLocalHeaderFooter : function() {
                  if ( ! api.has( self.localSectionsSettingId() ) )
                    return;
                  //console.log( 'self.localSectionsSettingId() ? ', api( self.localSectionsSettingId() )() );
            },

            // This method updates the levelTree observable api.Value()
            setLevelTreeValue : function() {
                  var self = this,
                      globalId = self.getGlobalSectionsSettingId(),
                      localId = self.localSectionsSettingId();

                  _global_col = api(globalId)();
                  _global_col = ( _.isObject( _global_col ) && ! _.isEmpty( _global_col.collection ) ) ? _global_col.collection : [];
                  _global_col = _.isArray( _global_col ) ? _global_col : [];

                  _local_col = api(localId)();
                  _local_col = ( _.isObject( _local_col ) && ! _.isEmpty( _local_col.collection ) ) ? _local_col.collection : [];
                  _local_col = _.isArray( _local_col ) ? _local_col : [];

                  var raw_col = _.union( _global_col, _local_col ),
                      local_header_footer_value,
                      global_header_footer_value,
                      has_local_header_footer = false,
                      has_global_header_footer = false,
                      localSeks = api( self.localSectionsSettingId() )(),
                      globalSeks = api( self.getGlobalSectionsSettingId() )(),
                      rawGlobalOptions = api( sektionsLocalizedData.optNameForGlobalOptions )();

                  // do we have a header-footer set, local or global ?

                  // LOCAL
                  if ( localSeks.local_options && localSeks.local_options.local_header_footer ) {
                        local_header_footer_value = localSeks.local_options.local_header_footer['header-footer'];
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
                  if ( rawGlobalOptions.global_header_footer && !has_local_header_footer && 'theme' !== local_header_footer_value) {
                        global_header_footer_value = rawGlobalOptions.global_header_footer['header-footer'];
                        has_global_header_footer = 'nimble_global' === global_header_footer_value || 'nimble_global' === local_header_footer_value;
                  }

                  var filteredCollection = $.extend( true, [], raw_col ),
                      header_loc,
                      footer_loc;

                  filteredCollection = _.filter( filteredCollection, function( loc, key ) {
                      return !_.contains( ['nimble_global_header', 'nimble_global_footer', 'nimble_local_header', 'nimble_local_footer'], loc.id );
                  });
                  if ( has_local_header_footer ) {
                        header_loc = _.findWhere(raw_col, {id:'nimble_local_header'});
                        footer_loc = _.findWhere(raw_col, {id:'nimble_local_footer'});
                        filteredCollection.unshift(header_loc);
                        filteredCollection.push(header_loc);
                  } else if ( has_global_header_footer ) {
                        header_loc = _.findWhere(raw_col, {id:'nimble_global_header'});
                        footer_loc = _.findWhere(raw_col, {id:'nimble_global_footer'});
                        filteredCollection.unshift(header_loc);
                        filteredCollection.push(footer_loc);
                  }
                  //console.log('ALORS COLLECITONS ?', raw_col, filteredCollection );

                  // Store it now
                  self.levelTree( filteredCollection );
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
                              api.errare( 'Error when parsing the the nimble-level-tree template', er );
                              return false;
                        }
                        $( '#customize-preview' ).after( $( _tmpl ) );
                  }
                  $('#nimble-level-tree').find('.sek-tree-wrap').html( self.getLevelTreeHtml( self.levelTree() ) );
            },

            // recursive helper
            // return an html string describing the contextually printed sections
            getLevelTreeHtml : function( _col, level ) {
                  var self = this,
                      levelType,
                      levelName,
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

                        // Shall we skip this level ?
                        // Yes if the level is a location AND the location has no section AND that this location is not a header or a footer
                        if ( 'location' === levelType && !_.contains( ['nimble_global_header', 'nimble_global_footer', 'nimble_local_header', 'nimble_local_footer'], _level_param.id ) && ( ! _.isArray( _level_param.collection ) || _.isEmpty( _level_param.collection ) ) ) {
                              skipLevel = true;
                        } else {
                              skipLevel = false;
                        }

                        if ( !skipLevel ) {
                              // cache the levelType var
                              levelType = _level_param.level;
                              levelName = levelType;
                              //try to get the i18n level name, fall back on the level type
                              if ( sektionsLocalizedData.i18n[levelType] ) {
                                    levelName = sektionsLocalizedData.i18n[levelType];
                              }
                              if ( true === _level_param.is_nested ) {
                                    levelName = sektionsLocalizedData.i18n['nested section'];
                              }
                              //console.log('_level_param ??', _level_param );
                              // if ( 'module' !== levelType && ( _.isUndefined( _level_param.collection ) || !_.isArray( _level_param.collection ) || _.isEmpty( _level_param.collection ) ) )
                              //   return;
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
                  var _icon = {};
                  _.each( sektionsLocalizedData.moduleCollection, function( modData ) {
                        if ( !_.isEmpty( _icon ) )
                          return;
                        if ( modType === modData['content-id'] ) {
                              _icon = {
                                    svg : modData.icon ? sektionsLocalizedData.moduleIconPath + modData.icon : '',
                                    font : modData.font_icon ? modData.font_icon : ''
                              };
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
