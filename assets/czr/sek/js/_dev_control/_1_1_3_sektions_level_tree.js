//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::setupTopBar(), at api.bind( 'ready', function() {})
            setupLevelTree : function() {
                  var self = this;
                  self.levelTree = new api.Value([]);
                  self.levelTree.bind( function( _collection ) {
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
                        var $el = $(evt.target);
                        api.previewer.send('sek-animate-to-level', { id : $el.data('nimb-id') });
                        api.previewer.send('sek-display-level-ui', { id : $el.data('nimb-id') });
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
            },


            // @return boolean
            hasLocalHeaderFooter : function() {
                  if ( ! api.has( self.localSectionsSettingId() ) )
                    return;
                  //console.log( 'self.localSectionsSettingId() ? ', api( self.localSectionsSettingId() )() );
            },

            // This method updated the levelTree observable api.Value()
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

                  // Store it now
                  self.levelTree( _.union( _global_col, _local_col ) );

                  //console.log('hasLocalHeaderFooter ??', api( self.localSectionsSettingId() )() );
            },


            // print the tree
            renderOrRefreshTree : function() {
                  var self = this;
                  if( $('#nimble-level-tree').length < 1 ) {
                        $( '#customize-preview' ).after( $('<div/>', { id : 'nimble-level-tree' } ) );
                  }
                  $('#nimble-level-tree').html( self.getLevelTreeHtml( self.levelTree() ) );
            },

            // recursive helper
            // return an html string describing the contextually printed sections
            getLevelTreeHtml : function( _col, level ) {
                  var self = this;
                  if ( !_.isArray( _col ) || _.isEmpty( _col ) ) {
                        api.errare('::buildLevelTree => invalid collection param', _col );
                        return _html;
                  }
                  var remove_icon_html = '<i class="material-icons sek-remove-level" title="">delete_forever</i>';
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

                        if ( 'module' !== _level_param.level && ( _.isUndefined( _level_param.collection ) || !_.isArray( _level_param.collection ) || _.isEmpty( _level_param.collection ) ) )
                          return;
                        remove_icon_html = 'location' !== _level_param.level ? remove_icon_html : '';
                        _html = _html + '<li data-nimb-level="'+_level_param.level+'" data-nimb-id="'+_level_param.id+'">';
                        _html = _html + [  _level_param.level , ' id :', _level_param.id, ' ', remove_icon_html ].join('');
                        if ( _.isArray( _level_param.collection ) && ! _.isEmpty( _level_param.collection ) ) {
                              _html = _html + self.getLevelTreeHtml( _level_param.collection, level );
                        }
                        _html = _html + '</li>';
                  });
                  _html = _html + '</ul>';
                  return _html;
            }
      });//$.extend()
})( wp.customize, jQuery );
