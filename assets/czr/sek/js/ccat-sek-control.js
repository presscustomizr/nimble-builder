//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {

            initialize: function() {
                  var self = this;
                  if ( _.isUndefined( window.sektionsLocalizedData ) ) {
                        throw new Error( 'CZRSeksPrototype => missing localized server params sektionsLocalizedData' );
                  }
                  // this class is skope dependant
                  if ( ! _.isFunction( api.czr_activeSkopes ) ) {
                        throw new Error( 'CZRSeksPrototype => api.czr_activeSkopes' );
                  }
                  // Max possible number of columns in a section
                  self.MAX_NUMBER_OF_COLUMNS = 12;

                  // _.debounce param when updating the UI setting
                  // prevent hammering server
                  self.SETTING_UPDATE_BUFFER = 50;

                  // Define a default value for the sektion setting value, used when no server value has been sent
                  // @see php function
                  // function sek_get_default_sektions_value() {
                  //     $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
                  //     foreach( sek_get_locations() as $location ) {
                  //         $defaut_sektions_value['collection'][] = [
                  //             'id' => $location,
                  //             'level' => 'location',
                  //             'collection' => [],
                  //             'options' => []
                  //         ];
                  //     }
                  //     return $defaut_sektions_value;
                  // }
                  self.defaultSektionSettingValue = sektionsLocalizedData.defaultSektionSettingValue;

                  // Store the contextual setting prefix
                  self.sekCollectionSettingId = new api.Value( {} );

                  // Keep track of the registered ui elements dynamically registered
                  // this collection is populated in ::register(), if the track param is true
                  // this is used to know what ui elements are currently being displayed
                  self.registered = new api.Value([]);

                  api.bind( 'ready', function() {
                        // the main sektion panel
                        self.registerAndSetupDefaultPanelSectionOptions();

                        // Setup the collection setting => register the main setting and bind it
                        // schedule reaction to collection setting ids => the setup of the collection setting when the collection setting ids are set
                        //=> on skope change
                        //@see setContextualCollectionSettingIdWhenSkopeSet
                        self.sekCollectionSettingId.callbacks.add( function( collectionSettingIds, previousCollectionSettingIds ) {
                              // register the collection setting id
                              // and schedule the reaction to different collection changes : refreshModules, ...
                              try { self.setupSettingToBeSaved(); } catch( er ) {
                                    api.errare( 'Error in self.sekCollectionSettingId.callbacks => self.setupSettingsToBeSaved()' , er );
                              }
                        });

                        // populate the settingids now if skopes are set
                        if ( ! _.isEmpty( api.czr_activeSkopes().local ) ) {
                              self.setContextualCollectionSettingIdWhenSkopeSet();
                        }

                        // Set the contextual setting prefix
                        api.czr_activeSkopes.callbacks.add( function( newSkopes, previousSkopes ) {
                              self.setContextualCollectionSettingIdWhenSkopeSet( newSkopes, previousSkopes );
                        });

                        // Communicate with the preview
                        self.reactToPreviewMsg();

                        // Setup Dnd
                        self.setupDnd();


                        // setup the tinyMce editor used for the tiny_mce_editor input
                        // => one object listened to by each tiny_mce_editor input
                        self.setupTinyMceEditor();

                        // print json
                        self.schedulePrintSectionJson();

                        // Always set the previewed device back to desktop on ui change
                        // event 'sek-ui-removed' id triggered when cleaning the registered ui controls
                        // @see ::cleanRegistered()
                        self.bind( 'sek-ui-removed', function() {
                              api.previewedDevice( 'desktop' );
                        });

                        // Synchronize api.previewedDevice with the currently rendered ui
                        // ensure that the selected device tab of the spacing module is the one being previewed
                        // =>@see spacing module, in item constructor CZRSpacingItemMths
                        api.previewedDevice.bind( function( device ) {
                              var currentControls = _.filter( self.registered(), function( uiData ) {
                                    return 'control' == uiData.what;
                              });
                              _.each( currentControls || [] , function( ctrlData ) {
                                    api.control( ctrlData.id, function( _ctrl_ ) {
                                          _ctrl_.container.find('[data-sek-device="' + device + '"]').each( function() {
                                                $(this).trigger('click');
                                          });
                                    });
                              });
                        });

                        // Schedule a reset
                        $('#customize-notifications-area').on( 'click', '[data-sek-reset="true"]', function() {
                              self.resetCollectionSetting();
                        });


                        // CLEAN UI BEFORE REMOVAL
                        // 'sek-ui-pre-removal' is triggered in ::cleanRegistered
                        // @params { what : control, id : '' }
                        self.bind( 'sek-ui-pre-removal', function( params ) {
                              // CLEAN DRAG N DROP
                              if ( 'control' == params.what && -1 < params.id.indexOf( 'draggable') ) {
                                    api.control( params.id, function( _ctrl_ ) {
                                          _ctrl_.container.find( '[draggable]' ).each( function() {
                                                $(this).off( 'dragstart dragend' );
                                          });
                                    });
                              }

                              // CLEAN SELECT2
                              // => we need to destroy the select2 instance, otherwise it can stay open when switching to another ui.
                              if ( 'control' == params.what ) {
                                    api.control( params.id, function( _ctrl_ ) {
                                          _ctrl_.container.find( 'select' ).each( function() {
                                                if ( ! _.isUndefined( $(this).data('select2') ) ) {
                                                      $(this).select2('destroy');
                                                }
                                          });
                                    });
                              }
                        });

                        // TEST
                        // @see php wp_ajax_sek_import_attachment
                        // wp.ajax.post( 'sek_import_attachment', {
                        //       rel_path : '/assets/img/41883.jpg'
                        // }).done( function( data) {
                        //       console.log('DATA', data );
                        // }).fail( function( _er_ ) {
                        //       api.errare( 'sek_import_attachment ajax action failed', _er_ );
                        // });

                        // POPULATE THE REGISTERED COLLECTION
                        // 'czr-new-registered' is fired in api.CZR_Helpers.register()
                        api.bind( 'czr-new-registered', function( params ) {
                              //console.log( 'czr-new-registered => ', params );
                              // Check that we have an origin property and that make sure we populate only the registration emitted by 'nimble'
                              if ( _.isUndefined( params.origin ) ) {
                                    throw new Error( 'czr-new-registered event => missing params.origin' );
                              }
                              if ( 'nimble' !== params.origin )
                                return;

                              // when no collection is provided, we use
                              if ( false !== params.track ) {
                                    var currentlyRegistered = self.registered();
                                    var newRegistered = $.extend( true, [], currentlyRegistered );
                                    //Check for duplicates
                                    var duplicateCandidate = _.findWhere( newRegistered, { id : params.id } );
                                    if ( ! _.isEmpty( duplicateCandidate ) && _.isEqual( duplicateCandidate, params ) ) {
                                          throw new Error( 'register => duplicated element in self.registered() collection ' + params.id );
                                    }
                                    newRegistered.push( params );
                                    self.registered( newRegistered );

                                    // say it
                                    //this.trigger( [params.what, params.id , 'registered' ].join('__'), params );
                              }
                        });

                  });//api.bind( 'ready' )
            },// initialize()








            // MAYBE REGISTER THE ADD NEW PANEL
            // Fired in initialize()
            registerAndSetupDefaultPanelSectionOptions : function() {
                  var self = this;

                  // MAIN SEKTION PANEL
                  var SektionPanelConstructor = api.Panel.extend({
                        //attachEvents : function () {},
                        // Always make the panel active, event if we have no sections / control in it
                        isContextuallyActive : function () {
                          return this.active();
                        },
                        _toggleActive : function(){ return true; }
                  });

                  // Always display the module-picker when expanding the main panel
                  // the panel.expanded() Value is not the right candidate to be observed because it gets changed on too many events, when generating the various UI.
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( _mainPanel_ ) {
                        _mainPanel_.deferred.embedded.done( function() {
                              var $sidePanelTitleEl = _mainPanel_.container.find('h3.accordion-section-title'),
                                  $topPanelTitleEl = _mainPanel_.container.find('.panel-meta .accordion-section-title'),
                                  logoHtml = [ '<img class="sek-nimble-logo" alt="'+ _mainPanel_.params.title +'" src="', sektionsLocalizedData.baseUrl, '/assets/img/nimble/nimble_horizontal.svg', '"/>' ].join('');




                              if ( 0 < $sidePanelTitleEl.length ) {
                                    // Attach click event
                                    $sidePanelTitleEl.on( 'click', function( evt ) {
                                          api.previewer.trigger('sek-pick-module');
                                    });
                                    // The default title looks like this : Nimble Builder <span class="screen-reader-text">Press return or enter to open this section</span>
                                    // we want to style "Nimble Builder" only.
                                    var $sidePanelTitleElSpan = $sidePanelTitleEl.find('span');
                                    $sidePanelTitleEl
                                          .addClass('sek-side-nimble-logo-wrapper')
                                          .html( logoHtml )
                                          .append( $sidePanelTitleElSpan );
                              }

                              // default looks like
                              // <span class="preview-notice">You are customizing <strong class="panel-title">Nimble Builder</strong></span>
                              // if ( 0 < $topPanelTitleEl.length ) {
                              //       var $topPanelTitleElInner = $topPanelTitleEl.find('.panel-title');
                              //       $topPanelTitleElInner.html( logoHtml );
                              // }
                        });
                  });

                  // The parent panel for all ui sections + global options section
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'panel',
                        id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                        title: sektionsLocalizedData.i18n['Nimble Builder'],
                        priority : 1000,
                        constructWith : SektionPanelConstructor,
                        track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                  });

            },//mayBeRegisterAndSetupAddNewSektionSection()




            //@return void()
            // sektionsData is built server side :
            //array(
            //     'db_values' => sek_get_skoped_seks( $skope_id ),
            //     'setting_id' => sek_get_seks_setting_id( $skope_id )//nimble___[skp__post_page_home]
            // )
            setContextualCollectionSettingIdWhenSkopeSet : function( newSkopes, previousSkopes ) {
                  var self = this;

                  // Clear all previous sektions if the main panel is expanded and we're coming from a previousSkopes
                  if ( ! _.isEmpty( previousSkopes.local ) && api.panel( sektionsLocalizedData.sektionsPanelId ).expanded() ) {
                        //api.previewer.trigger('sek-pick-section');
                        api.previewer.trigger('sek-pick-module');
                  }

                  // set the sekCollectionSettingId now, and update it on skope change
                  sektionsData = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local');
                  if ( sektionsLocalizedData.isDevMode ) {
                        api.infoLog( '::setContextualCollectionSettingIdWhenSkopeSet => SEKTIONS DATA ? ', sektionsData );
                  }
                  if ( _.isEmpty( sektionsData ) ) {
                        api.errare('::setContextualCollectionSettingIdWhenSkopeSet() => no sektionsData');
                  }
                  if ( _.isEmpty( sektionsData.setting_id ) ) {
                        api.errare('::setContextualCollectionSettingIdWhenSkopeSet() => missing setting_id');
                  }
                  self.sekCollectionSettingId( sektionsData.setting_id );
            }
      });//$.extend()
})( wp.customize, jQuery );
//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // Fired on api 'ready', in reaction to ::setContextualCollectionSettingIdWhenSkopeSet => ::sekCollectionSettingId
            // 1) register the collection setting nimble___[{$skope_id}] ( ex : nimble___[skp__post_page_20] )
            // 2) validate that the setting is well formed before being changed
            // 3) schedule reactions on change ?
            // @return void()
            setupSettingToBeSaved : function() {
                  var self = this,
                      serverCollection;

                  serverCollection = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local').db_values;
                  // maybe register the sektion_collection setting
                  var collectionSettingId = self.sekCollectionSettingId();// [ 'nimble___' , '[', newSkopes.local, ']' ].join('');
                  if ( _.isEmpty( collectionSettingId ) ) {
                        throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                  }

                  // if the collection setting is not registered yet
                  // => register it and bind it
                  if ( ! api.has( collectionSettingId ) ) {
                        var __collectionSettingInstance__ = api.CZR_Helpers.register({
                              what : 'setting',
                              id : collectionSettingId,
                              value : self.validateSettingValue( _.isObject( serverCollection ) ? serverCollection : self.defaultSektionSettingValue ),
                              transport : 'postMessage',//'refresh'
                              type : 'option',
                              track : false,//don't register in the self.registered()
                              origin : 'nimble'
                        });

                        if ( sektionsLocalizedData.isDevMode ) {
                              api( collectionSettingId, function( sektionSetInstance ) {
                                    // Schedule reactions to a collection change
                                    sektionSetInstance.bind( function( newSektionSettingValue, previousValue, params ) {
                                          api.infoLog( 'sektionSettingValue is updated',
                                                {
                                                      newValue : newSektionSettingValue,
                                                      previousValue : previousValue,
                                                      params : params
                                                }
                                          );
                                    });
                              });//api( collectionSettingId, function( sektionSetInstance ){}
                        }
                  }


                  // global options for all collection setting of this skope_id
                  // loop_start, before_content, after_content, loop_end

                  // Global Options : section
                  // api.CZR_Helpers.register({
                  //       what : 'section',
                  //       id : sektionsLocalizedData.optPrefixForSektionGlobalOptsSetting,//'__sektions__'
                  //       title: 'Global Options',
                  //       priority : 1000,
                  //       constructWith : SektionPanelConstructor,
                  //       track : false//don't register in the self.registered()
                  // });

                  // // => register a control
                  // // Template
                  // api.CZR_Helpers.register({
                  //       what : 'control',
                  //       id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                  //       title: 'Main sektions panel',
                  //       priority : 1000,
                  //       constructWith : SektionPanelConstructor,
                  //       track : false//don't register in the self.registered()
                  // });
            },


            // Fired :
            // 1) when instantiating the setting
            // 2) on each setting change, as an override of api.Value::validate( to ) @see customize-base.js
            // @return {} or null if did not pass the checks
            validateSettingValue : function( valCandidate ) {
                  if ( ! _.isObject( valCandidate ) ) {
                        api.errare('validation error => the setting should be an object', valCandidate );
                        return null;
                  }
                  var parentLevel = {},
                      errorDetected = false,
                      levelIds = [];
                  // walk the collections tree and verify it passes the various consistency checks
                  var _errorDetected_ = function( msg ) {
                        api.errare( msg , valCandidate );
                        api.previewer.trigger('sek-notify', {
                              type : 'error',
                              duration : 30000,
                              message : [
                                    '<span style="font-size:0.95em">',
                                      '<strong>' + msg + '</strong>',
                                      '<br>',
                                      sektionsLocalizedData.i18n['If this problem locks the Nimble builder, you might try to reset the sections for this page.'],
                                      '<br>',
                                      '<span style="text-align:center;display:block">',
                                        '<button type="button" class="button" aria-label="' + sektionsLocalizedData.i18n['Reset'] + '" data-sek-reset="true">' + sektionsLocalizedData.i18n['Reset'] + '</button>',
                                      '</span>',
                                    '</span>'
                              ].join('')

                        });
                        errorDetected = true;
                  };
                  var _checkWalker_ = function( level ) {
                      if ( errorDetected ) {
                            return;
                      }
                      if ( _.isUndefined( level ) && _.isEmpty( parentLevel ) ) {
                            // we are at the root level
                            level = $.extend( true, {}, valCandidate );
                            if ( _.isUndefined( level.id ) || _.isUndefined( level.level ) ) {
                                  // - there should be no 'level' property or 'id'
                                  // - there should be a collection of registered locations
                                  // - there should be no parent level defined
                                  if ( _.isUndefined( level.collection ) ) {
                                        _errorDetected_( 'validation error => the root level is missing the collection of locations' );
                                        return;
                                  }
                                  if ( ! _.isEmpty( level.level ) || ! _.isEmpty( level.id ) ) {
                                        _errorDetected_( 'validation error => the root level should not have a "level" or an "id" property' );
                                        return;
                                  }

                                  // Walk the section collection
                                  _.each( valCandidate.collection, function( _l_ ) {
                                        // Set the parent level now
                                        parentLevel = level;
                                        // walk
                                        _checkWalker_( _l_ );
                                  });
                            }
                      } else {
                            // we have a level.
                            // - make sure we have at least the following properties : id, level

                            // ID
                            if ( _.isEmpty( level.id ) || ! _.isString( level.id )) {
                                  _errorDetected_('validation error => a ' + level.level + ' level must have a valid id' );
                                  return;
                            } else if ( _.contains( levelIds, level.id ) ) {
                                  _errorDetected_('validation error => duplicated level id : ' + level.id );
                                  return;
                            } else {
                                  levelIds.push( level.id );
                            }

                            // OPTIONS
                            // if ( _.isEmpty( level.options ) || ! _.isObject( level.options )) {
                            //       _errorDetected_('validation error => a ' + level.level + ' level must have a valid options property' );
                            //       return;
                            // }

                            // LEVEL
                            if ( _.isEmpty( level.level ) || ! _.isString( level.level ) ) {
                                  _errorDetected_('validation error => a ' + level.level + ' level must have a level property' );
                                  return;
                            } else if ( ! _.contains( [ 'location', 'section', 'column', 'module' ], level.level ) ) {
                                  _errorDetected_('validation error => the level "' + level.level + '" is not authorized' );
                                  return;
                            }

                            // - Unless we are in a module, there should be a collection property
                            // - make sure a module doesn't have a collection property
                            if ( 'module' == level.level ) {
                                  if ( ! _.isUndefined( level.collection ) ) {
                                        _errorDetected_('validation error => a module can not have a collection property' );
                                        return;
                                  }
                            } else {
                                  if ( _.isUndefined( level.collection ) ) {
                                        _errorDetected_( 'validation error => missing collection property for level => ' + level.level + ' ' + level.id );
                                        return;
                                  }
                            }

                            switch ( level.level ) {
                                  case 'location' :
                                        //console.log('parentLevel ? ', level, parentLevel);
                                        if ( ! _.isEmpty( parentLevel.level ) ) {
                                              _errorDetected_('validation error => the parent of location ' + level.id +' should have no level set' );
                                              return;
                                        }
                                  break;

                                  case 'section' :
                                        if ( level.is_nested && 'column' != parentLevel.level ) {
                                              _errorDetected_('validation error => the nested section ' + level.id +' must be child of a column' );
                                              return;
                                        }
                                        if ( ! level.is_nested && 'location' != parentLevel.level ) {
                                              _errorDetected_('validation error => the section ' + level.id +' must be child of a location' );
                                              return;
                                        }
                                  break;

                                  case 'column' :
                                        if ( 'section' != parentLevel.level ) {
                                              _errorDetected_('validation error => the column ' + level.id +' must be child of a section' );
                                              return;
                                        }
                                  break;

                                  case 'module' :
                                        if ( 'column' != parentLevel.level ) {
                                              _errorDetected_('validation error => the module ' + level.id +' must be child of a column' );
                                              return;
                                        }
                                  break;
                            }

                            // If we are not in a module, keep walking the collections
                            if ( 'module' != level.level ) {
                                  _.each( level.collection, function( _l_ ) {
                                        // Set the parent level now
                                        parentLevel = $.extend( true, {}, level );
                                        // And walk sub levels
                                        _checkWalker_( _l_ );
                                  });
                            }
                      }
                  };
                  _checkWalker_();

                  //api.infoLog('in ::validateSettingValue', valCandidate );
                  // if null is returned, the setting value is not set @see customize-base.js
                  return errorDetected ? null : valCandidate;
            },//validateSettingValue



            // triggered when clicking on [data-sek-reset="true"]
            // scheduled in ::initialize()
            // Note :
            // 1) this is not a real reset, the customizer setting is set to self.defaultSektionSettingValue
            // @see php function which defines the defaults
            // function sek_get_default_sektions_value() {
            //     $defaut_sektions_value = [ 'collection' => [], 'options' => [] ];
            //     foreach( sek_get_locations() as $location ) {
            //         $defaut_sektions_value['collection'][] = [
            //             'id' => $location,
            //             'level' => 'location',
            //             'collection' => [],
            //             'options' => []
            //         ];
            //     }
            //     return $defaut_sektions_value;
            // }
            // 2) a real reset should delete the sektion post ( nimble_post_type, with for example title nimble___skp__post_page_21 ) and its database option storing its id ( for example : nimble___skp__post_page_21 )
            resetCollectionSetting : function() {
                  var self = this;
                  if ( _.isEmpty( self.sekCollectionSettingId() ) ) {
                        throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                  }
                  // reset the setting to default
                  api( self.sekCollectionSettingId() )( self.defaultSektionSettingValue );
                  // refresh the preview
                  api.previewer.refresh();
                  // remove any previous notification
                  api.notifications.remove( 'sek-notify' );
                  // display a success msg
                  api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                        api.notifications.add( new api.Notification( 'sek-reset-done', {
                              type: 'success',
                              message: sektionsLocalizedData.i18n['Reset complete'],
                              dismissible: true
                        } ) );

                        // Removed if not dismissed after 5 seconds
                        _.delay( function() {
                              api.notifications.remove( 'sek-reset-done' );
                        }, 5000 );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // invoked on api('ready') from self::initialize()
            // update the main setting OR generate a UI in the panel
            // AND
            // always send back a confirmation to the preview, so we can fire the ajax actions
            // the message sent back is used in particular to
            // - always pass the skope_id, which otherwise would be impossible to get in ajax
            // - in a duplication case, to pass the the newly generated id of the cloned level
            reactToPreviewMsg : function() {
                  var self = this,
                      apiParams = {},
                      uiParams = {},
                      sendToPreview = true, //<= the default behaviour is to send a message to the preview when the setting has been changed
                      msgCollection = {
                            // A section can be added in various scenarios :
                            // - when clicking on the ( + ) Insert content => @see preview::scheduleUiClickReactions() => addContentButton
                            // - when adding a nested section to a column
                            // - when dragging a module in a 'between-sections' or 'in-empty-location' drop zone
                            //
                            // Note : if the target location level already has section(s), then the section is appended in ajax, at the right place
                            // Note : if the target location is empty ( is_first_section is true ), nothing is send to the preview when updating the api setting, and we refresh the location level. => this makes sure that we removes the placeholder printed in the previously empty location
                            'sek-add-section' : {
                                  callback : function( params ) {
                                        sendToPreview = ! _.isUndefined( params.send_to_preview ) ? params.send_to_preview : true;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-add-section',
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              location : params.location,
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                              before_section : params.before_section,
                                              after_section : params.after_section,
                                              is_first_section : params.is_first_section
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // When a section is created ( not duplicated )
                                        //console.log( "react to preview Msg, sek-add-section complete => ", params );
                                        if ( params.apiParams.is_first_section ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.location
                                              });
                                        }
                                        api.previewer.trigger( 'sek-pick-module', {});
                                        api.previewer.send('sek-focus-on', { id : params.apiParams.id });
                                  }
                            },


                            'sek-add-column' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              action : 'sek-add-column',
                                              in_sektion : params.in_sektion,
                                              autofocus : params.autofocus
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // When adding a section, a nested column is automatically added
                                        // We want to focus on the module picker in this case, that's why the autofocus is set to false
                                        // @see 'sek-add-section' action description
                                        if ( false !== params.apiParams.autofocus ) {
                                              api.previewer.trigger( 'sek-pick-module', {});
                                        }
                                  }
                            },
                            'sek-add-module' : {
                                  callback :function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              action : 'sek-add-module',
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              module_type : params.content_id,

                                              before_module : params.before_module,
                                              after_module : params.after_module
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger( 'sek-edit-module', {
                                              id : params.apiParams.id,
                                              level : 'module',
                                              in_sektion : params.apiParams.in_sektion,
                                              in_column : params.apiParams.in_column
                                        });
                                        // always update the root fonts property after a module addition
                                        // because there might be a google font specified in the starting value
                                        self.updateAPISetting({ action : 'sek-update-fonts' } );

                                        // Refresh the stylesheet to generate the css rules of the module
                                        api.previewer.send( 'sek-refresh-stylesheet', {
                                              skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        });
                                  }
                            },
                            'sek-remove' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                  apiParams = {
                                                        action : 'sek-remove-section',
                                                        id : params.id,
                                                        location : params.location,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column,
                                                        is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
                                                  };
                                              break;
                                              case 'column' :
                                                  apiParams = {
                                                        action : 'sek-remove-column',
                                                        id : params.id,
                                                        in_sektion : params.in_sektion
                                                  };
                                              break;
                                              case 'module' :
                                                  apiParams = {
                                                        action : 'sek-remove-module',
                                                        id : params.id,
                                                        in_sektion : params.in_sektion,
                                                        in_column : params.in_column
                                                  };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        //console.log('PARAMS IN SEK REMOVE ', params );
                                        api.previewer.trigger( 'sek-pick-module', {});
                                        // always update the root fonts property after a removal
                                        // because the removed level(s) might had registered fonts
                                        self.updateAPISetting({ action : 'sek-update-fonts' } );

                                        // When the last section of a location gets removed, make sure we refresh the location level, to print the sek-empty-location-placeholder
                                        if ( 'sek-remove-section' === params.apiParams.action ) {
                                              var locationLevel = self.getLevelModel( params.apiParams.location );
                                              if ( _.isEmpty( locationLevel.collection ) ) {
                                                    api.previewer.trigger( 'sek-refresh-level', {
                                                          level : 'location',
                                                          id :  params.apiParams.location
                                                    });
                                              }
                                        }
                                  }
                            },

                            'sek-move' : {
                                  callback  : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-move-section',
                                                          id : params.id,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column ),
                                                          newOrder : params.newOrder,
                                                          from_location : params.from_location,
                                                          to_location : params.to_location
                                                    };
                                              break;
                                              case 'column' :
                                                    apiParams = {
                                                          action : 'sek-move-column',
                                                          id : params.id,
                                                          newOrder : params.newOrder,
                                                          from_sektion : params.from_sektion,
                                                          to_sektion : params.to_sektion,
                                                    };
                                              break;
                                              case 'module' :
                                                    apiParams = {
                                                          action : 'sek-move-module',
                                                          id : params.id,
                                                          newOrder : params.newOrder,
                                                          from_column : params.from_column,
                                                          to_column : params.to_column,
                                                          from_sektion : params.from_sektion,
                                                          to_sektion : params.to_sektion,
                                                    };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.action ) {
                                              case 'sek-move-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                                    // refresh location levels if the source and target location are differents
                                                    if ( params.apiParams.from_location != params.apiParams.to_location ) {
                                                          api.previewer.trigger( 'sek-refresh-level', {
                                                                level : 'location',
                                                                id :  params.apiParams.to_location
                                                          });
                                                          api.previewer.trigger( 'sek-refresh-level', {
                                                                level : 'location',
                                                                id :  params.apiParams.from_location
                                                          });
                                                    }
                                              break;
                                              case 'sek-move-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                              case 'sek-refresh-modules-in-column' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                        }
                                  }
                            },//sek-move




                            // the level will be cloned and walked to replace all ids by new one
                            // then the level clone id will be send back to the preview for the ajax rendering ( this is done in updateAPISetting() promise() )
                            'sek-duplicate' : {
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        switch( params.level ) {
                                              case 'section' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-section',
                                                          id : params.id,
                                                          location : params.location,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column,
                                                          is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
                                                    };
                                              break;
                                              case 'column' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-column',
                                                          id : params.id,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column
                                                    };
                                              break;
                                              case 'module' :
                                                    apiParams = {
                                                          action : 'sek-duplicate-module',
                                                          id : params.id,
                                                          in_sektion : params.in_sektion,
                                                          in_column : params.in_column
                                                    };
                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.action ) {
                                              case 'sek-duplicate-section' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'section',
                                                          in_sektion : params.apiParams.id
                                                    });
                                              break;
                                              case 'sek-duplicate-column' :
                                                    api.previewer.trigger('sek-edit-options', {
                                                          id : params.apiParams.id,
                                                          level : 'column',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                              case 'sek-duplicate-module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          id : params.apiParams.id,
                                                          level : 'module',
                                                          in_sektion : params.apiParams.in_sektion,
                                                          in_column : params.apiParams.in_column
                                                    });
                                              break;
                                        }
                                        // Refresh the stylesheet to generate the css rules of the clone
                                        api.previewer.send( 'sek-refresh-stylesheet', {
                                              skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                        });
                                        // Focus on the cloned level
                                        api.previewer.send('sek-focus-on', { id : params.apiParams.id });
                                  }
                            },
                            'sek-resize-columns' : function( params ) {
                                  sendToPreview = true;
                                  uiParams = {};
                                  //console.log( 'panel => reactToPreviewMsg => ', params );
                                  apiParams = params;
                                  return self.updateAPISetting( apiParams );
                            },

                            // @params {
                            //       drop_target_element : $(this),
                            //       position : _position,
                            //       before_section : $(this).data('sek-before-section'),
                            //       after_section : $(this).data('sek-after-section'),
                            //       content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                            //       content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                            // }
                            'sek-add-content-in-new-sektion' : {
                                  callback : function( params ) {
                                        sendToPreview = ! _.isUndefined( params.send_to_preview ) ? params.send_to_preview : true;//<= when the level is refreshed when complete, we don't need to send to preview.
                                        uiParams = {};
                                        apiParams = params;
                                        apiParams.action = 'sek-add-content-in-new-sektion';
                                        apiParams.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                        switch( params.content_type) {
                                              // When a module is dropped in a section + column structure to be generated
                                              case 'module' :
                                                    apiParams.droppedModuleId = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();//we set the id here because it will be needed when ajaxing
                                              break;

                                              // When a preset section is dropped
                                              case 'preset_section' :

                                              break;
                                        }
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        switch( params.apiParams.content_type) {
                                              case 'module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          level : 'module',
                                                          id : params.apiParams.droppedModuleId
                                                    });
                                                    // always update the root fonts property after a module addition
                                                    // because there might be a google font specified in the starting value
                                                    self.updateAPISetting({ action : 'sek-update-fonts' } );

                                                    // Refresh the stylesheet to generate the css rules of the module
                                                    api.previewer.send( 'sek-refresh-stylesheet', {
                                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                    });
                                              break;
                                        }
                                        // When a section is created ( not duplicated )
                                        //console.log( "react to preview Msg, sek-add-content-in-new-sektion complete => ", params );
                                        if ( params.apiParams.is_first_section ) {
                                              api.previewer.trigger( 'sek-refresh-level', {
                                                    level : 'location',
                                                    id :  params.apiParams.location
                                              });
                                        }
                                  }
                            },





                            // GENERATE UI ELEMENTS
                            'sek-pick-module' : function( params ) {
                                  //console.log('sek-pick-module react to preview', params);
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : 'module',
                                        // <= the was_triggered param can be used to determine if we need to animate the picker control or not. @see ::generateUI() case 'sek-generate-draggable-candidates-picker-ui'
                                        // true by default, because this is the most common scenario ( when adding a section, a column ... )
                                        // but false when clicking on the + ui icon in the preview
                                        was_triggered : _.has( params, 'was_triggered' ) ? params.was_triggered : true
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-pick-section' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : 'section',
                                        // <= the was_triggered param can be used to determine if we need to animate the picker control or not. @see ::generateUI() case 'sek-generate-draggable-candidates-picker-ui'
                                        // true by default, because this is the most common scenario ( when adding a section, a column ... )
                                        // but false when clicking on the + ui icon in the preview
                                        was_triggered : _.has( params, 'was_triggered' ) ? params.was_triggered : true
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-edit-options' : function( params ) {
                                  //console.log('IN EDIT OPTIONS ', params );
                                  sendToPreview = true;
                                  apiParams = {};
                                  if ( _.isEmpty( params.id ) ) {
                                        return $.Deferred( function() {
                                              this.reject( 'missing id' );
                                        });
                                  }
                                  uiParams = {
                                        action : 'sek-generate-level-options-ui',
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-edit-module' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-module-ui',
                                        level : params.level,
                                        id : params.id,
                                        in_sektion : params.in_sektion,
                                        in_column : params.in_column,
                                        options : params.options || []
                                  };
                                  return self.generateUI( uiParams );
                            },


                            // OTHER MESSAGE TYPES
                            // @params {
                            //  type : info, error, success
                            //  message : ''
                            //  duration : in ms
                            // }
                            'sek-notify' : function( params ) {
                                  sendToPreview = false;
                                  return $.Deferred(function() {
                                        api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                              api.notifications.add( new api.Notification( 'sek-notify', {
                                                    type: params.type || 'info',
                                                    message:  params.message,
                                                    dismissible: true
                                              } ) );

                                              // Removed if not dismissed after 5 seconds
                                              _.delay( function() {
                                                    api.notifications.remove( 'sek-notify' );
                                              }, params.duration || 5000 );
                                        });
                                        this.resolve();
                                  });
                            },

                            'sek-refresh-level' : function( params ) {
                                  sendToPreview = true;
                                  return $.Deferred(function() {
                                        apiParams = {
                                              action : 'sek-refresh-level',
                                              level : params.level,
                                              id : params.id
                                        };
                                        uiParams = {};
                                        this.resolve();
                                  });
                            }
                      };//msgCollection

                  // Schedule the reactions
                  // May be send a message to the preview
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( msgId, function( params ) {
                              var _cb_;
                              if ( _.isFunction( callbackFn ) ) {
                                    _cb_ = callbackFn;
                              } else if ( _.isFunction( callbackFn.callback ) ) {
                                    _cb_ = callbackFn.callback;
                              } else {
                                   api.errare( '::reactToPreviewMsg => invalid callback for action ' + msgId );
                                   return;
                              }

                              try { _cb_( params )
                                    // the cloneId is passed when resolving the ::updateAPISetting() promise()
                                    // they are needed on level duplication to get the newly generated level id.
                                    .done( function( cloneId ) {
                                          // Send to the preview
                                          if ( sendToPreview ) {
                                                api.previewer.send(
                                                      msgId,
                                                      {
                                                            skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                                            apiParams : apiParams,
                                                            uiParams : uiParams,
                                                            cloneId : ! _.isEmpty( cloneId ) ? cloneId : false
                                                      }
                                                );
                                          } else {
                                                // if nothing was sent to the preview, trigger the '*_done' action so we can execute the 'complete' callback
                                                api.previewer.trigger( [msgId, 'done'].join('_'), { apiParams : apiParams, uiParams : uiParams } );
                                          }
                                          // say it
                                          self.trigger( [ msgId, 'done' ].join('_'), params );
                                    })
                                    .fail( function( er ) {
                                          api.errare( 'reactToPreviewMsg => error when firing ' + msgId, er );
                                          api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                                api.notifications.add( new api.Notification( 'sek-react-to-preview', {
                                                      type: 'info',
                                                      message:  er,
                                                      dismissible: true
                                                } ) );

                                                // Removed if not dismissed after 5 seconds
                                                _.delay( function() {
                                                      api.notifications.remove( 'sek-react-to-preview' );
                                                }, 5000 );
                                          });

                                    }); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg => error when receiving ' + msgId, _er_ );
                                    }
                          });
                  });


                  // Schedule actions when callback done msg is sent by the preview
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.previewer.bind( [msgId, 'done'].join('_'), function( params ) {
                              if ( _.isFunction( callbackFn.complete ) ) {
                                    try { callbackFn.complete( params ); } catch( _er_ ) {
                                          api.errare( 'reactToPreviewMsg done => error when receiving ' + [msgId, 'done'].join('_') , _er_ );
                                    }
                              }
                        });
                  });
            },//reactToPreview();

            // Fired in initialized on api(ready)
            schedulePrintSectionJson : function() {
                  var self = this;
                  var popupCenter = function ( content ) {
                        w = 400;
                        h = 300;
                        // Fixes dual-screen position                         Most browsers      Firefox
                        var dualScreenLeft = ! _.isUndefined( window.screenLeft ) ? window.screenLeft : window.screenX;
                        var dualScreenTop = ! _.isUndefined( window.screenTop ) ? window.screenTop : window.screenY;

                        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
                        var top = ((height / 2) - (h / 2)) + dualScreenTop;
                        var newWindow = window.open("about:blank", null, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
                        var doc = newWindow.document;
                        doc.open("text/html");
                        doc.write( content );
                        doc.close();
                        // Puts focus on the newWindow
                        if (window.focus) {
                            newWindow.focus();
                        }
                  };
                  var cleanIds = function( levelData ) {
                        levelData.id = "";
                        _.each( levelData.collection, function( levelData ) {
                              levelData.id = "";
                              if ( _.isArray( levelData.collection ) ) {
                                    cleanIds( levelData );
                              }
                        });
                        return levelData;
                  };

                  api.previewer.bind( 'sek-to-json', function( params ) {
                        var sectionModel = $.extend( true, {}, self.getLevelModel( params.id ) );
                        popupCenter( JSON.stringify( cleanIds( sectionModel ) ) );
                  });
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // @params = {
            //    action : 'sek-generate-module-ui' / 'sek-generate-level-options-ui'
            //    level : params.level,
            //    id : params.id,
            //    in_sektion : params.in_sektion,
            //    in_column : params.in_column,
            //    options : params.options || []
            // }
            // @return promise()
            generateUI : function( params ) {
                  var self = this,
                      dfd = $.Deferred(),
                      _do_register_;

                  if ( _.isEmpty( params.action ) ) {
                        dfd.reject( 'generateUI => missing action' );
                  }

                  // REGISTER SETTING AND CONTROL
                  switch ( params.action ) {





                        // Possible content types :
                        // 1) module
                        // 2) preset_section
                        case 'sek-generate-draggable-candidates-picker-ui' :
                              var _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + ( 'module' === params.content_type ? '_sek_draggable_modules_ui' : '_sek_draggable_sections_ui' );
                              // Is the UI currently displayed the one that is being requested ?
                              // If so, visually remind the user that a module should be dragged
                              if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                                    api.control( _id_ ).focus({
                                          completeCallback : function() {
                                                //console.log('params sek-generate-draggable-candidates-picker-ui' , params);
                                                var $container = api.control( _id_ ).container;
                                                // @use button-see-mee css class declared in core in /wp-admin/css/customize-controls.css
                                                if ( $container.hasClass( 'button-see-me') )
                                                  return;
                                                $container.addClass('button-see-me');
                                                _.delay( function() {
                                                     $container.removeClass('button-see-me');
                                                }, 800 );
                                          }
                                    });
                                    break;
                              }
                              // Clean previously generated UI elements
                              self.cleanRegistered();
                              _do_register_ = function() {
                                    if ( ! api.has( _id_ ) ) {
                                          // synchronize the module setting with the main collection setting
                                          api( _id_, function( _setting_ ) {
                                                _setting_.bind( function( to, from ) {
                                                      api.errare('MODULE / SECTION PICKER SETTING CHANGED');
                                                });
                                          });
                                          api.CZR_Helpers.register( {
                                                origin : 'nimble',
                                                level : params.level,
                                                what : 'setting',
                                                id : _id_,
                                                dirty : false,
                                                value : '',
                                                transport : 'postMessage',// 'refresh',
                                                type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                          });
                                    }

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'control',
                                          id : _id_,
                                          label : 'module' === params.content_type ? sektionsLocalizedData.i18n['Module Picker'] : sektionsLocalizedData.i18n['Section Picker'],
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'module' === params.content_type ? 'sek_module_picker_module' : 'sek_section_picker_module',
                                          section : _id_,
                                          priority : 10,
                                          settings : { default : _id_ },
                                          track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                                    }).done( function() {
                                          api.control( _id_ ).focus({
                                              completeCallback : function() {}
                                          });
                                    });
                              };

                              // Defer the registration when the parent section gets added to the api
                              api.section.when( _id_, function() {
                                    _do_register_();
                              });

                              // MODULE / SECTION PICKER SECTION
                              api.CZR_Helpers.register({
                                    origin : 'nimble',
                                    what : 'section',
                                    id : _id_,
                                    title: 'module' === params.content_type ? sektionsLocalizedData.i18n['Module Picker'] : sektionsLocalizedData.i18n['Section Picker'],
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 30,
                                    track : false,//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                                    constructWith : api.Section.extend({
                                          //attachEvents : function () {},
                                          // Always make the section active, event if we have no control in it
                                          isContextuallyActive : function () {
                                            return this.active();
                                          },
                                          _toggleActive : function(){ return true; }
                                    })
                              });
                        break;
















                        case 'sek-generate-module-ui' :
                              if ( _.isEmpty( params.id ) ) {
                                    dfd.reject( 'generateUI => missing id' );
                              }
                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, simply focus on it
                              if ( self.isUIControlAlreadyRegistered( params.id ) ) {
                                    api.control( params.id ).focus({
                                          completeCallback : function() {}
                                    });
                                    break;
                              }

                              // Clean previously generated UI elements
                              self.cleanRegistered();

                              // For modules, we need to generate a UI for the module value
                              var moduleValue = self.getLevelProperty({
                                    property : 'value',
                                    id : params.id
                              });
                              var moduleType = self.getLevelProperty({
                                    property : 'module_type',
                                    id : params.id
                              });

                              if ( _.isEmpty( moduleType ) ) {
                                    dfd.reject( 'generateUI => module => invalid module_type' );
                              }

                              _do_register_ = function() {
                                    // Make sure this setting is bound only once !
                                    if ( ! api.has( params.id ) ) {
                                          // Schedule the binding to synchronize the module setting with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( params.id, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      try { self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_markup',
                                                            uiParams : _.extend( params, { action : 'sek-set-module-value' } ),
                                                            //options_type : 'spacing',
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      }); } catch( er ) {
                                                            api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                      }
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });

                                          api.CZR_Helpers.register({
                                                origin : 'nimble',
                                                level : params.level,
                                                what : 'setting',
                                                id : params.id,
                                                dirty : false,
                                                value : moduleValue,
                                                transport : 'postMessage',// 'refresh',
                                                type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                                          });
                                    }



                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'control',
                                          id : params.id,
                                          label : sektionsLocalizedData.i18n['Customize the options for module :'] + ' ' + api.czrModuleMap[ moduleType ].name,
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : moduleType,
                                          section : params.id,
                                          priority : 10,
                                          settings : { default : params.id }
                                    }).done( function() {
                                          api.control( params.id ).focus({
                                                completeCallback : function() {}
                                          });
                                    });
                              };

                              // Defer the registration when the parent section gets added to the api
                              api.section.when( params.id, function() {
                                    _do_register_();
                              });

                              // MAIN CONTENT SECTION
                              api.CZR_Helpers.register({
                                    origin : 'nimble',
                                    what : 'section',
                                    id : params.id,
                                    title: sektionsLocalizedData.i18n['Content for'] + ' ' + api.czrModuleMap[ moduleType ].name,
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 20,
                                    //track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });

                        break;














                        case 'sek-generate-level-options-ui' :
                              // Generate the UI for level options
                              //console.log("PARAMS IN sek-generate-level-options-ui", params );
                              var sectionLayoutOptionsSetId = params.id + '__sectionLayout_options',
                                  bgBorderOptionsSetId = params.id + '__bgBorder_options',
                                  heightOptionsSetId = params.id + '__height_options',
                                  spacingOptionsSetId = params.id + '__spacing_options';

                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, simply focus on the section
                              if ( self.isUIControlAlreadyRegistered( bgBorderOptionsSetId ) || self.isUIControlAlreadyRegistered( heightOptionsSetId ) || self.isUIControlAlreadyRegistered( spacingOptionsSetId ) ) {
                                    api.section( api.control( bgBorderOptionsSetId ).section() ).expanded( true );
                                    break;
                              }

                              // Clean previously generated UI elements
                              self.cleanRegistered();

                              var controlLabel = '',
                                  optionDBValue = self.getLevelProperty({
                                        property : 'options',
                                        id : params.id
                                  });
                              optionDBValue = _.isObject( optionDBValue ) ? optionDBValue : {};

                              _do_register_ = function() {
                                    if ( 'section' === params.level ) {
                                          // REGISTER SECTION LAYOUT
                                          // Make sure this setting is bound only once !
                                          if( ! api.has( heightOptionsSetId ) ) {
                                                // Schedule the binding to synchronize the options with the main collection setting
                                                // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                                // They need to be kept in order to keep track of the changes in the customizer.
                                                // => that's why we check if ! api.has( ... )
                                                api( sectionLayoutOptionsSetId, function( _setting_ ) {
                                                      _setting_.bind( _.debounce( function( to, from, args ) {
                                                            try { self.updateAPISettingAndExecutePreviewActions({
                                                                  defaultPreviewAction : 'refresh_stylesheet',
                                                                  uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                                  options_type : 'layout',// <= this is the options sub property where we will store this setting values. @see updateAPISetting case 'sek-set-level-options'
                                                                  settingParams : {
                                                                        to : to,
                                                                        from : from,
                                                                        args : args
                                                                  }
                                                            }); } catch( er ) {
                                                                  api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                            }
                                                      }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                                });//api( heightOptionsSetId, function( _setting_ ) {})


                                                api.CZR_Helpers.register( {
                                                      origin : 'nimble',
                                                      level : params.level,
                                                      what : 'setting',
                                                      id : sectionLayoutOptionsSetId,
                                                      dirty : false,
                                                      value : optionDBValue.layout || {},
                                                      transport : 'postMessage',// 'refresh',
                                                      type : '_nimble_ui_'//will be dynamically registered but not saved in db as option //sekData.settingType
                                                });
                                          }//if( ! api.has( sectionLayoutOptionsSetId ) ) {


                                          api.CZR_Helpers.register( {
                                                origin : 'nimble',
                                                level : params.level,
                                                level_id : params.id,
                                                what : 'control',
                                                id : sectionLayoutOptionsSetId,
                                                label : sektionsLocalizedData.i18n['Layout settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                                type : 'czr_module',//sekData.controlType,
                                                module_type : 'sek_level_section_layout_module',
                                                section : params.id,
                                                priority : 0,
                                                settings : { default : sectionLayoutOptionsSetId }
                                          }).done( function() {
                                                api.control( sectionLayoutOptionsSetId ).focus({
                                                      completeCallback : function() {}
                                                });
                                          });
                                    }// if 'section' === params.level



                                    // REGISTER BACKGROUND BORDER OPTIONS
                                    // Make sure this setting is bound only once !
                                    if( ! api.has( bgBorderOptionsSetId ) ) {
                                          // Schedule the binding to synchronize the options with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( bgBorderOptionsSetId, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      try { self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'bg_border',// <= this is the options sub property where we will store this setting values. @see updateAPISetting case 'sek-set-level-options'
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      }); } catch( er ) {
                                                            api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                      }
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( bgBorderOptionsSetId, function( _setting_ ) {})


                                          api.CZR_Helpers.register( {
                                                origin : 'nimble',
                                                level : params.level,
                                                what : 'setting',
                                                id : bgBorderOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.bg_border || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_nimble_ui_'//will be dynamically registered but not saved in db as option //sekData.settingType
                                          });
                                    }//if( ! api.has( bgBorderOptionsSetId ) ) {

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          level_id : params.id,
                                          what : 'control',
                                          id : bgBorderOptionsSetId,
                                          label : sektionsLocalizedData.i18n['Background and border settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_level_bg_border_module',
                                          section : params.id,
                                          priority : 10,
                                          settings : { default : bgBorderOptionsSetId }
                                    }).done( function() {
                                          api.control( bgBorderOptionsSetId ).focus({
                                                completeCallback : function() {}
                                          });
                                    });


                                    // REGISTER SPAGING OPTIONS
                                    // Make sure this setting is bound only once !
                                    if( ! api.has( spacingOptionsSetId ) ) {
                                          // Schedule the binding to synchronize the options with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( spacingOptionsSetId, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      try { self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'spacing',// <= this is the options sub property where we will store this setting values. @see updateAPISetting case 'sek-set-level-options'
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      }); } catch( er ) {
                                                            api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                      }
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( spacingOptionsSetId, function( _setting_ ) {})


                                          api.CZR_Helpers.register( {
                                                origin : 'nimble',
                                                level : params.level,
                                                what : 'setting',
                                                id : spacingOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.spacing || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_nimble_ui_'//will be dynamically registered but not saved in db as option //sekData.settingType
                                          });
                                    }



                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'control',
                                          id : spacingOptionsSetId,
                                          label : sektionsLocalizedData.i18n['Padding and margin settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_spacing_module',
                                          section : params.id,
                                          priority : 15,
                                          settings : { default : spacingOptionsSetId }
                                    }).done( function() {
                                          // synchronize the options with the main collection setting
                                          api.control( spacingOptionsSetId ).focus({
                                                completeCallback : function() {}
                                          });
                                    });



                                    // REGISTER HEIGHT OPTIONS
                                    // Make sure this setting is bound only once !
                                    if( ! api.has( heightOptionsSetId ) ) {
                                          // Schedule the binding to synchronize the options with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( heightOptionsSetId, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      try { self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'height',// <= this is the options sub property where we will store this setting values. @see updateAPISetting case 'sek-set-level-options'
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      }); } catch( er ) {
                                                            api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                                      }
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( heightOptionsSetId, function( _setting_ ) {})


                                          api.CZR_Helpers.register( {
                                                origin : 'nimble',
                                                level : params.level,
                                                what : 'setting',
                                                id : heightOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.height || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_nimble_ui_'//will be dynamically registered but not saved in db as option //sekData.settingType
                                          });
                                    }//if( ! api.has( heightOptionsSetId ) ) {

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          level_id : params.id,
                                          what : 'control',
                                          id : heightOptionsSetId,
                                          label : sektionsLocalizedData.i18n['Height settings for the'] + ' ' + sektionsLocalizedData.i18n[params.level],
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_level_height_module',
                                          section : params.id,
                                          priority : 20,
                                          settings : { default : heightOptionsSetId }
                                    }).done( function() {
                                          api.control( heightOptionsSetId ).focus({
                                                completeCallback : function() {}
                                          });
                                    });

                              };//_do_register_





                              // Defer the registration when the parent section gets added to the api
                              api.section.when( params.id, function() {
                                    _do_register_();
                              });

                              api.CZR_Helpers.register({
                                    origin : 'nimble',
                                    what : 'section',
                                    id : params.id,
                                    title: sektionsLocalizedData.i18n['Settings for the'] + ' ' + params.level,
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 10,
                                    track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });
                        break;
                  }//switch

                  return 'pending' == dfd.state() ? dfd.resolve().promise() : dfd.promise();//<= we might want to resolve on focus.completeCallback ?
            },//generateUI()














            // @params = {
            //     uiParams : params,
            //     options_type : 'spacing',
            //     settingParams : {
            //           to : to,
            //           from : from,
            //           args : args
            //     }
            // }
            //
            // @param settingParams.args = {
            //  inputRegistrationParams : {
            //     id :,
            //     type :
            //     refresh_markup : bool
            //     refresh_stylesheet : bool
            //     refresh_fonts : bool
            //  }
            //  input_changed : input_id
            //  input_transport : 'inherit'/'postMessage',
            //  module : { items : [...]}
            //  module_id :
            //  not_preview_sent : bool
            //}
            //
            // Note 1 : this method must handle two types of modules :
            // 1) mono item modules, for which the settingParams.to is an object, a single item object
            // 2) multi-items modules, for which the settingParams.to is an array, a collection of item objects
            // How do we know that we are a in single / multi item module ?
            //
            // Note 2 : we must also handle several scenarios of module value update :
            // 1) mono-items and multi-items module => input change
            // 2) crud multi item => item added or removed => in this case some args are not passed, like params.settingParams.args.inputRegistrationParams
            updateAPISettingAndExecutePreviewActions : function( params ) {
                  //console.log('PARAMS in updateAPISettingAndExecutePreviewActions', params );
                  if ( _.isEmpty( params.settingParams ) || ! _.has( params.settingParams, 'to' ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing params.settingParams.to. The api main setting can not be updated', params );
                        return;
                  }
                  var self = this;

                  // NORMALIZE THE VALUE WE WANT TO WRITE IN THE MAIN SETTING
                  // 1) We don't want to store the default title and id module properties
                  // 2) We don't want to write in db the properties that are set to their default values
                  var rawModuleValue = params.settingParams.to,
                      moduleValueCandidate,// {} or [] if mono item of multi-item module
                      parentModuleType = null,
                      isMultiItemModule = false;

                  //console.log('module control => ', params.settingParams.args.moduleRegistrationParams.control );
                  if ( _.isEmpty( params.settingParams.args ) || ! _.has( params.settingParams.args, 'moduleRegistrationParams' ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing params.settingParams.args.moduleRegistrationParams The api main setting can not be updated', params );
                        return;
                  }

                  var _ctrl_ = params.settingParams.args.moduleRegistrationParams.control,
                      _module_id_ = params.settingParams.args.moduleRegistrationParams.id,
                      parentModuleInstance = _ctrl_.czr_Module( _module_id_ );

                  if ( ! _.isEmpty( parentModuleInstance ) ) {
                        parentModuleType = parentModuleInstance.module_type;
                        isMultiItemModule = parentModuleInstance.isMultiItem();
                  } else {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing parentModuleInstance', params );
                  }

                  //console.log('updateAPISettingAndExecutePreviewActions => ', params.settingParams, isMultiItemModule, rawModuleValue,  _.isObject( rawModuleValue ) );

                  // The new module value can be an single item object if monoitem module, or an array of item objects if multi-item crud
                  // Let's normalize it
                  if ( ! isMultiItemModule && _.isObject( rawModuleValue ) ) {
                        moduleValueCandidate = self.normalizeAndSanitizeSingleItemInputValues( rawModuleValue, parentModuleType );
                  } else {
                        moduleValueCandidate = [];
                        _.each( rawModuleValue, function( item ) {
                              moduleValueCandidate.push( self.normalizeAndSanitizeSingleItemInputValues( item, parentModuleType ) );
                        });
                  }

                  // What to do in the preview ?
                  // The action to trigger is determined by the changed input
                  // For the options of a level, the default action is to refresh the stylesheet.
                  // But we might need to refresh the markup in some cases. Like for example when a css class is added. @see the boxed-wide layout example
                  if ( _.isEmpty( params.defaultPreviewAction ) ) {
                        api.errare( 'updateAPISettingAndExecutePreviewActions => missing defaultPreviewAction in passed params. No action can be triggered to the api.previewer.', params );
                        return;
                  }
                  // Set the default value
                  var refresh_stylesheet = 'refresh_stylesheet' === params.defaultPreviewAction,//<= default action for level options
                      refresh_markup = 'refresh_markup' === params.defaultPreviewAction,//<= default action for module options
                      refresh_fonts = 'refresh_fonts' === params.defaultPreviewAction;

                  // Maybe set the input based value
                  // Note : the inputRegistrationParams are passed in the args only when an module input is changed
                  // Example : For a crud module, when an item is added, there are no inputRegistrationParams, so we fallback on the default 'refresh_markup'
                  if ( ! _.isEmpty( params.settingParams.args.inputRegistrationParams ) ) {
                        if ( ! _.isUndefined( params.settingParams.args.inputRegistrationParams.refresh_stylesheet ) ) {
                              refresh_stylesheet = Boolean( params.settingParams.args.inputRegistrationParams.refresh_stylesheet );
                        }
                        if ( ! _.isUndefined( params.settingParams.args.inputRegistrationParams.refresh_markup ) ) {
                              refresh_markup = Boolean( params.settingParams.args.inputRegistrationParams.refresh_markup );
                        }
                        if ( ! _.isUndefined( params.settingParams.args.inputRegistrationParams.refresh_fonts ) ) {
                              refresh_fonts = Boolean( params.settingParams.args.inputRegistrationParams.refresh_fonts );
                        }
                  }

                  var _doUpdateWithRequestedAction = function() {
                        return self.updateAPISetting({
                              action : params.uiParams.action,
                              id : params.uiParams.id,
                              value : moduleValueCandidate,
                              in_column : params.uiParams.in_column,
                              in_sektion : params.uiParams.in_sektion,

                              // specific for level options
                              options_type : params.options_type,//'layout', 'spacing', 'bg_border', 'height'

                              settingParams : params.settingParams
                        }).done( function( ) {
                              // STYLESHEET => default action when modifying the level options
                              if ( true === refresh_stylesheet ) {
                                    api.previewer.send( 'sek-refresh-stylesheet', {
                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                          apiParams : {
                                                action : 'sek-refresh-stylesheet',
                                                id : params.uiParams.id,
                                                level : params.uiParams.level
                                          },
                                    });
                              }

                              // MARKUP
                              if ( true === refresh_markup ) {
                                    api.previewer.send( 'sek-refresh-level', {
                                          apiParams : {
                                                action : 'sek-refresh-level',
                                                id : params.uiParams.id,
                                                level : params.uiParams.level
                                          },
                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                                    });
                              }
                        });//self.updateAPISetting()
                  };//_doUpdateWithRequestedAction

                  // if the changed input is a google font modifier ( <=> font_family_css input)
                  // => we want to first refresh the google font collection, and then proceed to the requested action
                  // this way we make sure that the customized value used when ajaxing will take into account when writing the google font http request link
                  if ( true === refresh_fonts ) {
                        var _getChangedFontFamily = function() {
                              if ( 'font_family_css' != params.settingParams.args.input_changed ) {
                                    api.errare( 'updateAPISettingAndExecutePreviewActions => Error when refreshing fonts => the input id is not font_family_css', params );
                                    return;
                              } else {
                                    return params.settingParams.args.input_value;
                              }
                        };
                        var newFontFamily = '';
                        try { newFontFamily = _getChangedFontFamily(); } catch( er) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => Error when refreshing fonts', er );
                              return;
                        }
                        if ( ! _.isString( newFontFamily ) ) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => font-family must be a string', er );
                              return;
                        }
                        // add it only if gfont
                        if ( newFontFamily.indexOf('gfont') > -1 ) {
                              self.updateAPISetting({
                                    action : 'sek-update-fonts',
                                    font_family : newFontFamily
                              }).done( function( ) {
                                    _doUpdateWithRequestedAction().then( function() {
                                          // always refresh again after
                                          // Why ?
                                          // Because the first refresh was done before actually setting the new font family, so based on a previous set of fonts
                                          // which leads to have potentially an additional google fonts that we don't need after the first refresh
                                          // that's why this second refresh is required. It wont trigger any preview ajax actions. Simply refresh the root fonts property of the main api setting.
                                          self.updateAPISetting({ action : 'sek-update-fonts' } );
                                    });
                              });
                        } else {
                             _doUpdateWithRequestedAction();
                        }
                  } else {
                        _doUpdateWithRequestedAction();
                  }
            },//updateAPISettingAndExecutePreviewActions












            // @return a normalized and sanitized item value
            normalizeAndSanitizeSingleItemInputValues : function( _item_, parentModuleType ) {
                  var itemNormalized = {},
                      itemNormalizedAndSanitized = {},
                      inputDefaultValue = null,
                      inputType = null,
                      sanitizedVal,
                      self = this;

                  //console.log('normalizeAndSanitizeSingleItemInputValues => ', _item_, parentModuleType );

                  // NORMALIZE
                  // title, id and module_type don't need to be saved in database
                  // title and id are legacy entries that can be used in multi-items modules to identify and name the item
                  // @see ::getDefaultItemModelFromRegisteredModuleData()
                  _.each( _item_, function( _val, input_id ) {
                        if ( _.contains( ['title', 'id' ], input_id ) )
                          return;

                        if ( null !== parentModuleType ) {
                              inputDefaultValue = self.getInputDefaultValue( input_id, parentModuleType );
                              if ( 'no_default_value_specified' === inputDefaultValue ) {
                                    api.infoLog( '::updateAPISettingAndExecutePreviewActions => missing default value for input ' + input_id + ' in module ' + parentModuleType );
                              }
                        }
                        if ( _val === inputDefaultValue ) {
                              return;
                        } else {
                              itemNormalized[ input_id ] = _val;
                        }
                  });

                  // SANITIZE
                  _.each( itemNormalized, function( _val, input_id ) {
                        // @see extend_api_base.js
                        // @see sektions::_7_0_sektions_add_inputs_to_api.js
                        switch( self.getInputType( input_id, parentModuleType ) ) {
                              case 'text' :
                              case 'textarea' :
                              case 'check' :
                              case 'gutencheck' :
                              case 'select' :
                              case 'radio' :
                              case 'number' :
                              case 'upload' :
                              case 'upload_url' :
                              case 'color' :
                              case 'wp_color_alpha' :
                              case 'wp_color' :
                              case 'content_picker' :
                              case 'tiny_mce_editor' :
                              case 'password' :
                              case 'range' :
                              case 'range_slider' :
                              case 'hidden' :
                              case 'h_alignment' :
                              case 'h_text_alignment' :

                              case 'spacing' :
                              case 'bg_position' :
                              case 'v_alignment' :
                              case 'font_size' :
                              case 'line_height' :
                              case 'font_picker' :
                                  sanitizedVal = _val;
                              break;
                              default :
                                  sanitizedVal = _val;
                              break;
                        }

                        itemNormalizedAndSanitized[ input_id ] = sanitizedVal;
                  });
                  return itemNormalizedAndSanitized;
            },











            // Is the UI currently displayed the one that is being requested ?
            // If so, don't generate the ui again
            // @return bool
            isUIControlAlreadyRegistered : function( uiElementId ) {
                  var self = this,
                      uiCandidate = _.filter( self.registered(), function( registered ) {
                            return registered.id == uiElementId && 'control' === registered.what;
                      }),
                      controlIsAlreadyRegistered = false;

                  // If the control is not been tracked in our self.registered(), let's check if it is registered in the api
                  // Typically, the module / section picker will match that case, because we don't keep track of it ( so it's not cleaned )
                  if ( _.isEmpty( uiCandidate ) ) {
                        controlIsAlreadyRegistered = api.control.has( uiElementId );
                  } else {
                        controlIsAlreadyRegistered = true;
                        // we should have only one uiCandidate with this very id
                        if ( uiCandidate.length > 1 ) {
                              api.errare( 'generateUI => why is this control registered more than once ? => ' + uiElementId );
                        }
                  }
                  return controlIsAlreadyRegistered;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData, serverControlParams
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // user action => this utility must be used to set the main setting value
            // params = {
            //    action : 'sek-add-section', 'sek-add-column', 'sek-add-module',...
            //    in_sektion
            //    in_column
            // }
            updateAPISetting : function( params ) {
                  var self = this,
                      dfd = $.Deferred();

                  // Update the sektion collection
                  api( self.sekCollectionSettingId(), function( sektionSetInstance ) {
                        // sektionSetInstance() = {
                        //    collection : [
                        //       'loop_start' :  { level : location,  collection : [ 'sek124' : { collection : [], level : section, options : {} }], options : {}},
                        //       'loop_end' : { level : location, collection : [], options : {}}
                        //        ...
                        //    ],
                        //    options : {}
                        //
                        // }
                        var currentSetValue = sektionSetInstance(),
                            newSetValue = _.isObject( currentSetValue ) ? $.extend( true, {}, currentSetValue ) : self.defaultSektionSettingValue,
                            locationCandidate,
                            sektionCandidate,
                            columnCandidate,
                            moduleCandidate,
                            // move variables
                            originalCollection,
                            reorderedCollection,
                            //duplication variable
                            cloneId, //will be passed in resolve()
                            startingModuleValue;// will be populated by the optional starting value specificied on module registration

                        // make sure we have a collection array to populate
                        newSetValue.collection = _.isArray( newSetValue.collection ) ? newSetValue.collection : self.defaultSektionSettingValue.collection;

                        switch( params.action ) {
                              //-------------------------------------------------------------------------------------------------
                              //-- SEKTION
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-section' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    if ( _.isEmpty( params.location ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                                    }
                                    // Is this a nested sektion ?
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          // can we add this nested sektion ?
                                          // if the parent sektion of the column has is_nested = true, then we can't
                                          var parentSektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                          if ( 'no_match' == parentSektionCandidate ) {
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                                                break;
                                          }
                                          if ( true === parentSektionCandidate.is_nested ) {
                                                dfd.reject( sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ]);
                                                break;
                                          }
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.push({
                                                id : params.id,
                                                level : 'section',
                                                collection : [{
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : []
                                                }],
                                                is_nested : true
                                          });
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          _.each( locationCandidate.collection, function( secModel, index ) {
                                                if ( params.before_section === secModel.id ) {
                                                      position = index;
                                                }
                                                if ( params.after_section === secModel.id ) {
                                                      position = index + 1;
                                                }
                                          });

                                          // @see reactToCollectionSettingIdChange
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          // insert the section in the collection at the right place
                                          locationCandidate.collection.splice( position, 0, {
                                                id : params.id,
                                                level : 'section',
                                                collection : [{
                                                      id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                      level : 'column',
                                                      collection : []
                                                }]
                                          });
                                    }
                              break;


                              case 'sek-duplicate-section' :
                                    //console.log('PARAMS IN sek-duplicate-section', params );
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    if ( _.isEmpty( params.location ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing location' );
                                    }
                                    var deepClonedSektion;
                                    try { deepClonedSektion = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }

                                    var _position_ = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    //console.log('_position_ ', _position_ );
                                    // Is this a nested sektion ?
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' == columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                                break;
                                          }

                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );


                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          // @see reactToCollectionSettingIdChange
                                          locationCandidate.collection.splice( parseInt( _position_ + 1, 10 ), 0, deepClonedSektion );

                                    }
                                    cloneId = deepClonedSektion.id;//will be passed in resolve()
                              break;

                              // in the case of a nested sektion, we have to remove it from a column
                              // otherwise from the root sektion collection
                              case 'sek-remove-section' :
                                    //console.log('PARAMS IN sek-remove-sektion', params );
                                    if ( true === params.is_nested ) {
                                          columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                          if ( 'no_match' != columnCandidate ) {
                                                columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                                columnCandidate.collection = _.filter( columnCandidate.collection, function( col ) {
                                                      return col.id != params.id;
                                                });
                                          } else {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          }
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          locationCandidate.collection = _.filter( locationCandidate.collection, function( sek ) {
                                                return sek.id != params.id;
                                          });
                                    }
                              break;

                              case 'sek-move-section' :
                                    //console.log('PARAMS in sek-move-section', params );
                                    var toLocationCandidate = self.getLevelModel( params.to_location, newSetValue.collection ),
                                        movedSektionCandidate,
                                        copyOfMovedSektionCandidate;

                                    if ( _.isEmpty( toLocationCandidate ) || 'no_match' == toLocationCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target location' );
                                    }

                                    // MOVED CROSS LOCATIONS
                                    // - make a copy of the moved sektion
                                    // - remove the moved sektion from the source location
                                    if ( params.from_location != params.to_location ) {
                                          // Remove the moved sektion from the source location
                                          var fromLocationCandidate = self.getLevelModel( params.from_location, newSetValue.collection );
                                          if ( _.isEmpty( fromLocationCandidate ) || 'no_match' == fromLocationCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source location' );
                                          }

                                          fromLocationCandidate.collection =  _.isArray( fromLocationCandidate.collection ) ? fromLocationCandidate.collection : [];
                                          // Make a copy of the sektion candidate now, before removing it
                                          movedSektionCandidate = self.getLevelModel( params.id, fromLocationCandidate.collection );
                                          copyOfMovedSektionCandidate = $.extend( true, {}, movedSektionCandidate );
                                          // remove the sektion from its previous sektion
                                          fromLocationCandidate.collection = _.filter( fromLocationCandidate.collection, function( sektion ) {
                                                return sektion.id != params.id;
                                          });
                                    }

                                    // UPDATE THE TARGET LOCATION
                                    toLocationCandidate.collection =  _.isArray( toLocationCandidate.collection ) ? toLocationCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toLocationCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          // in the case of a cross location movement, we need to add the moved sektion to the target location
                                          if ( params.from_location != params.to_location && _id_ == copyOfMovedSektionCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedSektionCandidate );
                                          } else {
                                                sektionCandidate = self.getLevelModel( _id_, originalCollection );
                                                if ( _.isEmpty( sektionCandidate ) || 'no_match' == sektionCandidate ) {
                                                      throw new Error( 'updateAPISetting => move section => missing section candidate' );
                                                }
                                                reorderedCollection.push( sektionCandidate );
                                          }
                                    });
                                    toLocationCandidate.collection = reorderedCollection;

                              break;











                              //-------------------------------------------------------------------------------------------------
                              //-- COLUMN
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-column' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == sektionCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    // can we add another column ?
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          dfd.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    // RESET ALL COLUMNS WIDTH
                                    _.each( sektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });
                                    sektionCandidate.collection.push({
                                          id :  params.id,
                                          level : 'column',
                                          collection : []
                                    });
                              break;


                              case 'sek-remove-column' :
                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' != sektionCandidate ) {
                                          // can we remove the column ?
                                          if ( 1 === _.size( sektionCandidate.collection ) ) {
                                                dfd.reject( sektionsLocalizedData.i18n["A section must have at least one column."]);
                                                break;
                                          }
                                          sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                          sektionCandidate.collection = _.filter( sektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          // RESET ALL COLUMNS WIDTH
                                          _.each( sektionCandidate.collection, function( colModel ) {
                                                colModel.width = '';
                                          });
                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                    }

                              break;

                              case 'sek-duplicate-column' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }

                                    sektionCandidate = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    if ( 'no_match' == sektionCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }

                                    sektionCandidate.collection =  _.isArray( sektionCandidate.collection ) ? sektionCandidate.collection : [];
                                    // can we add another column ?
                                    if ( ( self.MAX_NUMBER_OF_COLUMNS - 1 ) < _.size( sektionCandidate.collection ) ) {
                                          dfd.reject( sektionsLocalizedData.i18n["You've reached the maximum number of columns allowed in this section."]);
                                          break;
                                    }

                                    var deepClonedColumn;
                                    try { deepClonedColumn = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          break;
                                    }
                                    var _position = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedColumn.id;//will be passed in resolve()
                                    sektionCandidate.collection.splice( parseInt( _position + 1, 10 ), 0, deepClonedColumn );
                                    // RESET ALL COLUMNS WIDTH
                                    _.each( sektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });
                              break;



                              case 'sek-resize-columns' :
                                    if ( params.col_number < 2 )
                                      break;

                                    var resizedColumn = self.getLevelModel( params.resized_column, newSetValue.collection ),
                                        sistercolumn = self.getLevelModel( params.sister_column, newSetValue.collection );

                                    //console.log( 'updateAPISetting => ' + params.action + ' => ', params );

                                    // SET RESIZED COLUMN WIDTH
                                    if ( 'no_match' == resizedColumn ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no resized column matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no resized column matched');
                                          break;
                                    }

                                    resizedColumn.width = parseFloat( params.resizedColumnWidthInPercent );


                                    // SET OTHER COLUMNS WIDTH
                                    var parentSektion = self.getLevelModel( params.in_sektion, newSetValue.collection );
                                    var otherColumns = _.filter( parentSektion.collection, function( _col_ ) {
                                              return _col_.id != resizedColumn.id && _col_.id != sistercolumn.id;
                                        });
                                    var otherColumnsWidth = parseFloat( resizedColumn.width.toFixed(3) );

                                    if ( ! _.isEmpty( otherColumns ) ) {
                                         _.each( otherColumns, function( colModel ) {
                                                currentColWidth = parseFloat( colModel.width * 1 );
                                                if ( ! _.has( colModel, 'width') || ! _.isNumber( currentColWidth * 1 ) || _.isEmpty( currentColWidth + '' ) || 1 > currentColWidth ) {
                                                      colModel.width = parseFloat( ( 100 / params.col_number ).toFixed(3) );
                                                }
                                                // sum up all other column's width, excluding the resized and sister one.
                                                otherColumnsWidth = parseFloat( ( otherColumnsWidth  +  colModel.width ).toFixed(3) );
                                          });
                                    }


                                    // SET SISTER COLUMN WIDTH

                                    // sum up all other column's width, excluding the resized and sister one.
                                    // console.log( "resizedColumn.width", resizedColumn.width  );
                                    // console.log( "otherColumns", otherColumns );

                                    // then calculate the sistercolumn so we are sure that we feel the entire space of the sektion
                                    sistercolumn.width = parseFloat( ( 100 - otherColumnsWidth ).toFixed(3) );

                                    // console.log('otherColumnsWidth', otherColumnsWidth );
                                    // console.log("sistercolumn.width", sistercolumn.width );
                                    // console.log( "sistercolumn.width + otherColumnsWidth" , Number( sistercolumn.width ) + Number( otherColumnsWidth ) );
                                    //console.log('COLLECTION AFTER UPDATE ', parentSektion.collection );
                              break;




                              case 'sek-move-column' :
                                    var toSektionCandidate = self.getLevelModel( params.to_sektion, newSetValue.collection ),
                                        movedColumnCandidate,
                                        copyOfMovedColumnCandidate;

                                    if ( _.isEmpty( toSektionCandidate ) || 'no_match' == toSektionCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target sektion' );
                                    }

                                    if ( params.from_sektion != params.to_sektion ) {
                                          // Remove the moved column from the source sektion
                                          var fromSektionCandidate = self.getLevelModel( params.from_sektion, newSetValue.collection );
                                          if ( _.isEmpty( fromSektionCandidate ) || 'no_match' == fromSektionCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                                          }

                                          fromSektionCandidate.collection =  _.isArray( fromSektionCandidate.collection ) ? fromSektionCandidate.collection : [];
                                          // Make a copy of the column candidate now, before removing it
                                          movedColumnCandidate = self.getLevelModel( params.id, fromSektionCandidate.collection );
                                          copyOfMovedColumnCandidate = $.extend( true, {}, movedColumnCandidate );
                                          // remove the column from its previous sektion
                                          fromSektionCandidate.collection = _.filter( fromSektionCandidate.collection, function( column ) {
                                                return column.id != params.id;
                                          });
                                          // Reset the column's width in the target sektion
                                          _.each( fromSektionCandidate.collection, function( colModel ) {
                                                colModel.width = '';
                                          });
                                    }

                                    // update the target sektion
                                    toSektionCandidate.collection =  _.isArray( toSektionCandidate.collection ) ? toSektionCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toSektionCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          // in the case of a cross sektion movement, we need to add the moved column to the target sektion
                                          if ( params.from_sektion != params.to_sektion && _id_ == copyOfMovedColumnCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedColumnCandidate );
                                          } else {
                                                columnCandidate = self.getLevelModel( _id_, originalCollection );
                                                if ( _.isEmpty( columnCandidate ) || 'no_match' == columnCandidate ) {
                                                      throw new Error( 'updateAPISetting => moveColumn => missing columnCandidate' );
                                                }
                                                reorderedCollection.push( columnCandidate );
                                          }
                                    });
                                    toSektionCandidate.collection = reorderedCollection;

                                    // Reset the column's width in the target sektion
                                    _.each( toSektionCandidate.collection, function( colModel ) {
                                          colModel.width = '';
                                    });

                              break;












                              //-------------------------------------------------------------------------------------------------
                              //-- MODULE
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-add-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    // a module_type must be provided
                                    if ( _.isEmpty( params.module_type ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing module_type' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' === columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    var position = 0;
                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                    // get the position of the before or after module
                                    _.each( columnCandidate.collection, function( moduleModel, index ) {
                                          if ( params.before_module === moduleModel.id ) {
                                                position = index;
                                          }
                                          if ( params.after_module === moduleModel.id ) {
                                                position = index + 1;
                                          }
                                    });

                                    var _moduleParams = {
                                          id : params.id,
                                          level : 'module',
                                          module_type : params.module_type
                                    };
                                    // Let's add the starting value if provided when registrating the module
                                    startingModuleValue = self.getModuleStartingValue( params.module_type );
                                    if ( 'no_starting_value' !== startingModuleValue ) {
                                          _moduleParams.value = startingModuleValue;
                                    }

                                    columnCandidate.collection.splice( position, 0, _moduleParams );
                              break;

                              case 'sek-duplicate-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          break;
                                    }

                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    var deepClonedModule;
                                    try { deepClonedModule = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => error when cloning the level');
                                          break;
                                    }
                                    var insertInposition = self.getLevelPositionInCollection( params.id, newSetValue.collection );
                                    cloneId = deepClonedModule.id;//will be passed in resolve()
                                    columnCandidate.collection.splice( parseInt( insertInposition + 1, 10 ), 0, deepClonedModule );

                              break;

                              case 'sek-remove-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' != columnCandidate ) {
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          columnCandidate.collection = _.filter( columnCandidate.collection, function( module ) {
                                                return module.id != params.id;
                                          });

                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                    }
                              break;

                              case 'sek-move-module' :
                                    var toColumnCandidate,
                                        movedModuleCandidate,
                                        copyOfMovedModuleCandidate;

                                    // loop on the sektions to find the toColumnCandidate
                                    // _.each( newSetValue.collection, function( _sektion_ ) {
                                    //       _.each( _sektion_.collection, function( _column_ ) {
                                    //             if ( _column_.id == params.to_column ) {
                                    //                  toColumnCandidate = _column_;
                                    //             }
                                    //       });
                                    // });
                                    toColumnCandidate = self.getLevelModel( params.to_column, newSetValue.collection );

                                    if ( _.isEmpty( toColumnCandidate ) || 'no_match' == toColumnCandidate ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing target column' );
                                    }

                                    // If the module has been moved to another column
                                    // => remove the moved module from the source column
                                    if ( params.from_column != params.to_column ) {
                                          var fromColumnCandidate;
                                          fromColumnCandidate = self.getLevelModel( params.from_column, newSetValue.collection );

                                          if ( _.isEmpty( fromColumnCandidate ) || 'no_match' == fromColumnCandidate ) {
                                                throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                                          }

                                          fromColumnCandidate.collection =  _.isArray( fromColumnCandidate.collection ) ? fromColumnCandidate.collection : [];
                                          // Make a copy of the module candidate now, before removing it
                                          movedModuleCandidate = self.getLevelModel( params.id, newSetValue.collection );
                                          copyOfMovedModuleCandidate = $.extend( true, {}, movedModuleCandidate );
                                          // remove the module from its previous column
                                          fromColumnCandidate.collection = _.filter( fromColumnCandidate.collection, function( module ) {
                                                return module.id != params.id;
                                          });
                                    }// if params.from_column != params.to_column

                                    // update the target column
                                    toColumnCandidate.collection =  _.isArray( toColumnCandidate.collection ) ? toColumnCandidate.collection : [];
                                    originalCollection = $.extend( true, [], toColumnCandidate.collection );
                                    reorderedCollection = [];
                                    _.each( params.newOrder, function( _id_ ) {
                                          if ( params.from_column != params.to_column && _id_ == copyOfMovedModuleCandidate.id ) {
                                                reorderedCollection.push( copyOfMovedModuleCandidate );
                                          } else {
                                                moduleCandidate = self.getLevelModel( _id_, newSetValue.collection );
                                                if ( _.isEmpty( moduleCandidate ) || 'no_match' == moduleCandidate ) {
                                                      throw new Error( 'updateAPISetting => ' + params.action + ' => missing moduleCandidate' );
                                                }
                                                reorderedCollection.push( moduleCandidate );
                                          }
                                    });
                                    // Check if we have duplicates ?
                                    if ( reorderedCollection.length != _.uniq( reorderedCollection ).length ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => there are duplicated modules in column : ' + toColumnCandidate.id );
                                    } else {
                                          toColumnCandidate.collection = reorderedCollection;
                                    }
                              break;


                              case 'sek-set-module-value' :
                                    moduleCandidate = self.getLevelModel( params.id, newSetValue.collection );
                                    var _value_ = {};
                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _value_[ _key_ ] = _val_;
                                    });
                                    if ( 'no_match' == moduleCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no module matched', params );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => error no module matched');
                                          break;
                                    }
                                    moduleCandidate.value = _value_;
                              break;






                              //-------------------------------------------------------------------------------------------------
                              //-- LEVEL OPTIONS
                              //-------------------------------------------------------------------------------------------------
                              case 'sek-set-level-options' :
                                    var _candidate_ = self.getLevelModel( params.id, newSetValue.collection ),
                                        _valueCandidate = {};
                                    if ( 'no_match'=== _candidate_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent sektion matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no parent sektion matched');
                                          break;
                                    }
                                    _candidate_.options = _candidate_.options || {};

                                    // consider only the non empty settings for db
                                    // booleans should bypass this check
                                    _.each( params.value || {}, function( _val_, _key_ ) {
                                          // Note : _.isEmpty( 5 ) returns true when checking an integer,
                                          // that's why we need to cast the _val_ to a string when using _.isEmpty()
                                          if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                                            return;
                                          _valueCandidate[ _key_ ] = _val_;
                                    });
                                    if ( _.isEmpty( params.options_type ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                                    }
                                    switch( params.options_type ) {
                                          case 'layout' :
                                                _candidate_.options.layout = _valueCandidate;
                                          break;
                                          case 'bg_border' :
                                                _candidate_.options.bg_border = _valueCandidate;
                                          break;
                                          case 'height' :
                                                _candidate_.options.height = _valueCandidate;
                                          break;
                                          case 'spacing' :
                                                _candidate_.options.spacing = _valueCandidate;
                                          break;
                                    }
                              break;













                              //-------------------------------------------------------------------------------------------------
                              //-- CONTENT IN NEW SEKTION
                              //-------------------------------------------------------------------------------------------------
                              // @params {
                              //   drop_target_element : $(this),
                              //   position : _position,// <= top or bottom
                              //   before_section : $(this).data('sek-before-section'),
                              //   after_section : $(this).data('sek-after-section'),
                              //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
                              //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                              // }
                              case 'sek-add-content-in-new-sektion' :
                                    // console.log('update API Setting => sek-add-content-in-new-sektion => PARAMS', params );
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    // get the position of the before or after section
                                    var position = 0;
                                    locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                    if ( 'no_match' == locationCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                          dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                          break;
                                    }
                                    locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                    _.each( locationCandidate.collection, function( secModel, index ) {
                                          if ( params.before_section === secModel.id ) {
                                                position = index;
                                          }
                                          if ( params.after_section === secModel.id ) {
                                                position = index + 1;
                                          }
                                    });

                                    switch( params.content_type) {
                                          // When a module is dropped in a section + column structure to be generated
                                          case 'module' :
                                                // Let's add the starting value if provided when registrating the module
                                                // Note : params.content_id is the module_type
                                                startingModuleValue = self.getModuleStartingValue( params.content_id );

                                                // insert the section in the collection at the right place
                                                locationCandidate.collection.splice( position, 0, {
                                                      id : params.id,
                                                      level : 'section',
                                                      collection : [
                                                            {
                                                                  id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                                                  level : 'column',
                                                                  collection : [
                                                                        {
                                                                              id : params.droppedModuleId,
                                                                              level : 'module',
                                                                              module_type : params.content_id,
                                                                              value : 'no_starting_value' !== startingModuleValue ? startingModuleValue : null
                                                                        }
                                                                  ]
                                                            }
                                                      ]
                                                });
                                          break;

                                          // When a preset section is dropped
                                          case 'preset_section' :
                                                // insert the section in the collection at the right place
                                                var presetSectionCandidate;
                                                try { presetSectionCandidate = self.getPresetSectionCollection({
                                                            presetSectionType : params.content_id,
                                                            section_id : params.id//<= we need to use the section id already generated, and passed for ajax action @see ::reactToPreviewMsg, case "sek-add-section"
                                                      });
                                                } catch( _er_ ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()', _er_ );
                                                      dfd.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollection()');
                                                      break;
                                                }
                                                if ( ! _.isObject( presetSectionCandidate ) || _.isEmpty( presetSectionCandidate ) ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetSectionCandidate );
                                                      dfd.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                                      break;
                                                }
                                                locationCandidate.collection.splice( position, 0, presetSectionCandidate );
                                          break;
                                    }//switch( params.content_type)
                              break;



                              //-------------------------------------------------------------------------------------------------
                              //-- POPULATE GOOGLE FONTS
                              //-------------------------------------------------------------------------------------------------
                              //@params {
                              //       action : 'sek-update-fonts',
                              //       font_family : newFontFamily,
                              // }
                              case 'sek-update-fonts' :
                                    //console.log('PARAMS in sek-add-fonts', params );
                                    // Get the gfonts from the level options and modules values
                                    var currentGfonts = self.sniffGFonts();
                                    if ( ! _.isEmpty( params.font_family ) && _.isString( params.font_family ) && ! _.contains( currentGfonts, params.font_family ) ) {
                                          if ( params.font_family.indexOf('gfont') < 0 ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont');
                                                break;
                                          }
                                          currentGfonts.push( params.font_family );
                                    }
                                    // update the global gfonts collection
                                    // this is then used server side in Sek_Dyn_CSS_Handler::sek_get_gfont_print_candidates to build the Google Fonts request
                                    newSetValue.fonts = currentGfonts;
                              break;
                        }// switch







                        // if we did not already rejected the request, let's check if the setting object has actually been modified
                        // at this point it should have been.
                        if ( 'pending' == dfd.state() ) {
                              //console.log('ALORS ?', currentSetValue, newSetValue );
                              if ( _.isEqual( currentSetValue, newSetValue ) ) {
                                    dfd.reject( 'updateAPISetting => the new setting value is unchanged when firing action : ' + params.action );
                              } else {
                                    if ( null !== self.validateSettingValue( newSetValue ) ) {
                                          sektionSetInstance( newSetValue, params );
                                          dfd.resolve( cloneId );// the cloneId is only needed in the duplication scenarii
                                    } else {
                                          dfd.reject( 'updateAPISetting => the new setting value did not pass the validation checks for action ' + params.action );
                                    }

                                    //console.log('COLLECTION SETTING UPDATED => ', self.sekCollectionSettingId(), api( self.sekCollectionSettingId() )() );

                              }
                        }
                  });//api( self.sekCollectionSettingId(), function( sektionSetInstance ) {}
                  return dfd.promise();
            },//updateAPISetting


            // @return a JSON parsed string,
            // + guid() ids for each levels
            // ready for insertion
            //
            // @sectionParams : {
            //       presetSectionType : params.content_id,
            //       section_id : params.id
            // }
            // Why is the section_id provided ?
            // Because this id has been generated ::reactToPreviewMsg, case "sek-add-section", and is the identifier that we'll need when ajaxing ( $_POST['id'])
            getPresetSectionCollection : function( sectionParams ) {
                  var self = this,
                      presetSection,
                      allPresets = $.extend( true, {}, sektionsLocalizedData.presetSections );

                  if ( ! _.isObject( allPresets ) || _.isEmpty( allPresets ) ) {
                        throw new Error( 'getPresetSectionCollection => Invalid sektionsLocalizedData.presetSections');
                  }
                  if ( _.isEmpty( allPresets[ sectionParams.presetSectionType ] ) ) {
                        throw new Error( 'getPresetSectionCollection => ' + sectionParams.presetSectionType + ' has not been found in sektionsLocalizedData.presetSections');
                  }
                  var presetCandidate = allPresets[ sectionParams.presetSectionType ];
                  // Ensure we have a string that's JSON.parse-able
                  if ( typeof presetCandidate !== 'string' || presetCandidate[0] !== '{' ) {
                        throw new Error( 'getPresetSectionCollection => ' + sectionParams.presetSectionType + ' is not JSON.parse-able');
                  }
                  presetCandidate = JSON.parse( presetCandidate );
                  var setIds = function( collection ) {
                        _.each( collection, function( levelData ) {
                              levelData.id = sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid();
                              if ( _.isArray( levelData.collection ) ) {
                                    setIds( levelData.collection );
                              }
                        });
                        return collection;
                  };

                  // set the section id provided.
                  presetCandidate.id = sectionParams.section_id;

                  // the other level's id have to be generated
                  presetCandidate.collection = setIds( presetCandidate.collection );
                  return presetCandidate;
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            //@return void()
            //clean all registered control, section, panel tracked ids
            //preserve the settings
            //typically fired before updating the ui. @see ::generateUI()
            cleanRegistered : function() {
                  var self = this,
                      registered = $.extend( true, [], self.registered() || [] );

                  registered = _.filter( registered, function( _reg_ ) {
                        if ( 'setting' !== _reg_.what ) {
                              if ( api[ _reg_.what ].has( _reg_.id ) ) {
                                    // fire an event before removal, can be used to clean some jQuery plugin instance for example
                                    if (  _.isFunction( api[ _reg_.what ]( _reg_.id ).trigger ) ) {//<= Section and Panel constructor are not extended with the Event class, that's why we check if this method exists
                                           self.trigger( 'sek-ui-pre-removal', { what : _reg_.what, id : _reg_.id } );
                                    }
                                    $.when( api[ _reg_.what ]( _reg_.id ).container.remove() ).done( function() {
                                          // remove control, section, panel
                                          api[ _reg_.what ].remove( _reg_.id );
                                          // useful event, used to destroy the $ drop plugin instance for the section / module picker
                                          self.trigger( 'sek-ui-removed', { what : _reg_.what, id : _reg_.id } );
                                    });
                              }
                        }
                        return _reg_.what === 'setting';
                  });
                  self.registered( registered );
            }

      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
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
                        api.errare( 'getInputDefaultValue => missing sektionsLocalizedData.registeredModules' );
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
            }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
/**
 * @https://github.com/StackHive/DragDropInterface
 * @https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API
 * @https://html.spec.whatwg.org/multipage/dnd.html#dnd
 * @https://caniuse.com/#feat=dragndrop
 */
// EVENTS

// drag  => handler : ondrag  Fired when an element or text selection is being dragged.
// dragend => handler : ondragend Fired when a drag operation is being ended (for example, by releasing a mouse button or hitting the escape key). (See Finishing a Drag.)
// dragenter => handler : ondragenter Fired when a dragged element or text selection enters a valid drop target. (See Specifying Drop Targets.)
// dragexit  => handler : ondragexit  Fired when an element is no longer the drag operation's immediate selection target.
// dragleave => handler : ondragleave Fired when a dragged element or text selection leaves a valid drop target.
// dragover  => handler : ondragover  Fired when an element or text selection is being dragged over a valid drop target (every few hundred milliseconds).
// dragstart => handler : ondragstart Fired when the user starts dragging an element or text selection. (See Starting a Drag Operation.)
// drop  => handler : ondrop  Fired when an element or text selection is dropped on a valid drop target. (See Performing a Drop.)

// Drop targets can be rendered statically when the preview is rendered or dynamically on dragstart ( sent to preview with 'sek-drag-start')
// Typically, an empty column will be populated with a zek-drop-zone element statically in the preview.
// The other drop zones are rendered dynamically in ::schedulePanelMsgReactions case 'sek-drag-start'
//
// droppable targets are defined server side in sektionsLocalizedData.dropSelectors :
// '.sek-drop-zone' <= to pass the ::dnd_canDrop() test, a droppable target should have this css class
// 'body' <= body will not be eligible for drop, but setting the body as drop zone allows us to fire dragenter / dragover actions, like toggling the "approaching" or "close" css class to real drop zone
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            //-------------------------------------------------------------------------------------------------
            //-- SETUP DnD
            //-------------------------------------------------------------------------------------------------
            //Fired in ::initialize()
            // INSTANTIATE Dnd ZONES IF SUPPORTED BY THE BROWSER
            // + SCHEDULE DROP ZONES RE-INSTANTIATION ON PREVIEW REFRESH
            // + SCHEDULE API REACTION TO *drop event
            // setup $.sekDrop for $( api.previewer.targetWindow().document ).find( '.sektion-wrapper')
            setupDnd : function() {
                  var self = this;
                  // emitted by the module_picker or the section_picker module
                  // @params { type : 'section' || 'module', input_container : input.container }
                  self.bind( 'sek-refresh-dragzones', function( params ) {
                        if ( 'draggable' in document.createElement('span') ) {
                              self.setupNimbleDragZones( params.input_container );//<= module or section picker
                        } else {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  sektionsLocalizedData.i18n['This browser does not support drag and drop. You might need to update your browser or use another one.'],
                                          dismissible: true
                                    } ) );

                                    // Removed if not dismissed after 5 seconds
                                    _.delay( function() {
                                          api.notifications.remove( 'drag-drop-support' );
                                    }, 10000 );
                              });
                        }
                  });

                  // on previewer refresh
                  api.previewer.bind( 'ready', function() {
                        try { self.setupNimbleDropZones();//<= module or section picker
                        } catch( er ) {
                              api.errare( '::setupDnd => error on self.setupNimbleDropZones()', er );
                        }
                        // if the module_picker or the section_picker is currently a registered ui control,
                        // => re-instantiate sekDrop on the new preview frame
                        // the registered() ui levels look like :
                        // [
                        //   { what: "control", id: "__nimble___sek_draggable_sections_ui", label: "Section Picker", type: "czr_module", module_type: "sek_section_picker_module", …}
                        //   { what: "setting", id: "__nimble___sek_draggable_sections_ui", dirty: false, value: "", transport: "postMessage", … }
                        //   { what: "section", id: "__nimble___sek_draggable_sections_ui", title: "Section Picker", panel: "__sektions__", priority: 30}
                        // ]
                        if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_section_picker_module' } ) ) ) {
                              self.rootPanelFocus();
                        } else if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_module_picker_module' } ) ) ) {
                              self.rootPanelFocus();
                        }
                  });

                  // React to the *-droped event
                  self.reactToDrop();
            },

            //-------------------------------------------------------------------------------------------------
            //--DRAG ZONES SETUP
            //-------------------------------------------------------------------------------------------------
            // fired in ::initialize, on 'sek-refresh-nimbleDragDropZones
            // 'sek-refresh-nimbleDragDropZones' is emitted by the section and the module picker modules with param { type : 'section_picker' || 'module_picker'}
            setupNimbleDragZones : function( $draggableWrapper ) {
                  var self = this;
                  //console.log('instantiate', type );
                  // $(this) is the dragged element
                  var _onStart = function( evt ) {
                        evt.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                        evt.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                        // evt.originalEvent.dataTransfer.effectAllowed = "move";
                        // evt.originalEvent.dataTransfer.dropEffect = "move";
                        // Notify if not supported : https://caniuse.com/#feat=dragndrop
                        try {
                              evt.originalEvent.dataTransfer.setData( 'browserSupport', 'browserSupport' );
                              evt.originalEvent.dataTransfer.setData( 'browserSupport', 'browserSupport' );
                              evt.originalEvent.dataTransfer.clearData( 'browserSupport' );
                        } catch ( er ) {
                              api.panel( sektionsLocalizedData.sektionsPanelId, function( __main_panel__ ) {
                                    api.notifications.add( new api.Notification( 'drag-drop-support', {
                                          type: 'error',
                                          message:  sektionsLocalizedData.i18n['This browser does not support drag and drop. You might need to update your browser or use another one.'],
                                          dismissible: true
                                    } ) );

                                    // Removed if not dismissed after 5 seconds
                                    _.delay( function() {
                                          api.notifications.remove( 'drag-drop-support' );
                                    }, 10000 );
                              });
                        }
                        // Set the dragged type property now : module or preset_section
                        self.dnd_draggedType = $(this).data('sek-content-type');
                        $('body').addClass('sek-dragging');
                        api.previewer.send( 'sek-drag-start', { type : self.dnd_draggedType } );//fires the rendering of the dropzones
                  };

                  var _onEnd = function( evt ) {
                        $('body').removeClass('sek-dragging');
                        api.previewer.send( 'sek-drag-stop' );
                  };

                  // Schedule
                  $draggableWrapper.find( '[draggable]' ).each( function() {
                        $(this).on( 'dragstart', function( evt ) {
                                    _onStart.call( $(this), evt );
                              })
                              .on( 'dragend', function( evt ) {
                                    _onEnd.call( $(this), evt );
                              });
                  });
            },//setupNimbleZones()












            //-------------------------------------------------------------------------------------------------
            //--DRAG ZONES SETUP
            //-------------------------------------------------------------------------------------------------
            // Scheduled on previewer('ready') each time the previewer is refreshed
            setupNimbleDropZones : function() {
                  var self = this;
                  this.$dropZones = this.dnd_getDropZonesElements();
                  this.preDropElement = $( '<div>', {
                        class: sektionsLocalizedData.preDropElementClass,
                        html : ''//will be set dynamically
                  });
                  if ( this.$dropZones.length < 1 ) {
                        throw new Error( '::setupNimbleDropZones => invalid Dom element');
                  }

                  this.$dropZones.each( function() {
                        var $zone = $(this);
                        // Make sure we don't delegate an event twice for a given element
                        if ( true === $zone.data('zone-droppable-setup') )
                            return;

                        self.enterOverTimer = null;
                        // Delegated to allow reactions on future modules / sections
                        $zone
                              //.on( 'dragenter dragover', sektionsLocalizedData.dropSelectors,  )
                              .on( 'dragenter dragover', sektionsLocalizedData.dropSelectors, function( evt ) {
                                    //console.log( self.enterOverTimer, self.dnd_canDrop( $(this) ) );
                                    if ( _.isNull( self.enterOverTimer ) ) {
                                          self.enterOverTimer = true;
                                          _.delay(function() {
                                                // If the mouse did not move, reset the time and do nothing
                                                // this will prevent a drop zone to "dance", aka expand collapse, when stoping the mouse close to it
                                                if ( self.currentMousePosition && ( ( self.currentMousePosition + '' ) == ( evt.clientY + '' + evt.clientX + '') ) ) {
                                                      self.enterOverTimer = null;
                                                      return;
                                                }
                                                self.currentMousePosition = evt.clientY + '' + evt.clientX + '';
                                                self.dnd_toggleDragApproachClassesToDropZones( evt );
                                          }, 100 );
                                    }

                                    if ( ! self.dnd_canDrop( $(this) ) )
                                      return;

                                    evt.stopPropagation();
                                    self.dnd_OnEnterOver( $(this), evt );
                              })
                              .on( 'dragleave drop', sektionsLocalizedData.dropSelectors, function( evt ) {
                                    switch( evt.type ) {
                                          case 'dragleave' :
                                                if ( ! self.dnd_isOveringDropTarget( $(this), evt  ) ) {
                                                      self.dnd_cleanOnLeaveDrop( $(this), evt );
                                                }
                                          break;
                                          case 'drop' :
                                                // Reset the this.$cachedDropZoneCandidates now
                                                this.$cachedDropZoneCandidates = null;//has been declared on enter over

                                                if ( ! self.dnd_canDrop( $(this) ) )
                                                  return;
                                                evt.preventDefault();//@see https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#drop
                                                self.dnd_onDrop( $(this), evt );
                                                self.dnd_cleanOnLeaveDrop( $(this), evt );
                                                // this event will fire another cleaner
                                                // also sent on dragend
                                                api.previewer.send( 'sek-drag-stop' );
                                          break;
                                    }
                              })
                              .data( 'zone-droppable-setup', true );// flag the zone. Will be removed on 'destroy'

                });//this.dropZones.each()
            },//setupNimbleDropZones()




            //-------------------------------------------------------------------------------------------------
            //-- DnD Helpers
            //-------------------------------------------------------------------------------------------------
            // Fired on 'dragenter dragover'
            // toggles the "approaching" and "close" css classes when conditions are met.
            //
            // Because this function can be potentially heavy if there are a lot of drop zones, this is fired with a timer
            //
            // Note : this is fired before checking if the target is eligible for drop. This way we can calculate an approach, as soon as we start hovering the 'body' ( which is part the drop selector list )
            dnd_toggleDragApproachClassesToDropZones : function( evt ) {
                  var self = this;
                  this.$dropZones = this.$dropZones || this.dnd_getDropZonesElements();
                  this.$cachedDropZoneCandidates = _.isEmpty( this.$cachedDropZoneCandidates ) ? this.$dropZones.find('.sek-drop-zone') : this.$cachedDropZoneCandidates;// Will be reset on drop

                  this.$dropZones.find('.sek-drop-zone').each( function() {
                        var yPos = evt.clientY,
                            xPos = evt.clientX,
                            isApproachingThreshold = 120,
                            isCloseThreshold = 60,
                            isVeryCloseThreshold = 40;

                        var dzoneRect = $(this)[0].getBoundingClientRect(),
                            mouseToBottom = Math.abs( yPos - dzoneRect.bottom ),
                            mouseToTop = Math.abs( dzoneRect.top - yPos ),
                            mouseToRight = xPos - dzoneRect.right,
                            mouseToLeft = dzoneRect.left - xPos,
                            isVeryCloseVertically = ( mouseToBottom < isVeryCloseThreshold ) || ( mouseToTop < isVeryCloseThreshold ),
                            isVeryCloseHorizontally =  ( mouseToRight > 0 && mouseToRight < isVeryCloseThreshold ) || ( mouseToLeft > 0 && mouseToLeft < isVeryCloseThreshold ),
                            isCloseVertically = ( mouseToBottom < isCloseThreshold ) || ( mouseToTop < isCloseThreshold ),
                            isCloseHorizontally =  ( mouseToRight > 0 && mouseToRight < isCloseThreshold ) || ( mouseToLeft > 0 && mouseToLeft < isCloseThreshold ),
                            isInHorizontally = xPos <= dzoneRect.right && dzoneRect.left <= xPos,
                            isInVertically = yPos <= dzoneRect.top && dzoneRect.bottom <= yPos,
                            isApproachingVertically = ( mouseToBottom < isApproachingThreshold ) || ( mouseToTop < isApproachingThreshold ),
                            isApproachingHorizontally = ( mouseToRight > 0 && mouseToRight < isApproachingThreshold ) || ( mouseToLeft > 0 && mouseToLeft < isApproachingThreshold );

                        // var html = "isApproachingHorizontally : " + isApproachingHorizontally + ' | isCloseHorizontally : ' + isCloseHorizontally + ' | isInHorizontally : ' + isInHorizontally;
                        // html += ' | xPos : ' + xPos + ' | zoneRect.right : ' + dzoneRect.right;
                        // html += "isApproachingVertically : " + isApproachingVertically + ' | isCloseVertically : ' + isCloseVertically + ' | isInVertically : ' + isInVertically;
                        // html += ' | yPos : ' + yPos + ' | zoneRect.top : ' + dzoneRect.top;
                        // $(this).html( '<span style="font-size:10px">' + html + '</span>');

                        // var html = '';
                        // html += ' | mouseToBottom : ' + mouseToBottom + ' | mouseToTop : ' + mouseToTop;
                        // html += "isApproachingVertically : " + isApproachingVertically + ' | isCloseVertically : ' + isCloseVertically + ' | isInVertically : ' + isInVertically;
                        // $(this).html( '<span style="font-size:12px">' + html + '</span>');

                        if ( ( isVeryCloseVertically || isInVertically ) && ( isVeryCloseHorizontally || isInHorizontally ) ) {
                              $(this).addClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-close');
                              $(this).removeClass( 'sek-drag-is-approaching' );
                        } else if ( ( isCloseVertically || isInVertically ) && ( isCloseHorizontally || isInHorizontally ) ) {
                              $(this).addClass( 'sek-drag-is-close');
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-approaching' );
                        } else if ( ( isApproachingVertically || isInVertically ) && ( isApproachingHorizontally || isInHorizontally ) ) {
                              $(this).addClass( 'sek-drag-is-approaching');
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-close' );
                        } else {
                              $(this).removeClass( 'sek-drag-is-very-close');
                              $(this).removeClass( 'sek-drag-is-close' );
                              $(this).removeClass( 'sek-drag-is-approaching' );
                        }
                  });//$('.sek-drop-zones').each()

                  // Reset the timer
                  self.enterOverTimer = null;
            },

            // @return string
            dnd_getPreDropElementContent : function( evt ) {
                  var $target = $( evt.currentTarget ),
                      html,
                      preDropContent;

                  switch( this.dnd_draggedType ) {
                        case 'module' :
                              html = sektionsLocalizedData.i18n['Insert here'];
                              if ( $target.length > 0 ) {
                                  if ( 'between-sections' === $target.data('sek-location') || 'in-empty-location' === $target.data('sek-location') ) {
                                        html = sektionsLocalizedData.i18n['Insert in a new section'];
                                  }
                              }
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        case 'preset_section' :
                              html = sektionsLocalizedData.i18n['Insert a new section here'];
                              preDropContent = '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                        break;

                        default :
                              api.errare( '::dnd_getPreDropElementContent => invalid content type provided');
                        break;
                  }
                  return preDropContent;
            },

            // Scheduled on previewer('ready') each time the previewer is refreshed
            dnd_getDropZonesElements : function() {
                  return $( api.previewer.targetWindow().document );
            },

            // @return boolean
            // Note : the class "sek-content-preset_section-drop-zone" is dynamically generated in preview::schedulePanelMsgReactions() sek-drag-start case
            dnd_canDrop : function( $dropTarget ) {
                  //console.log("$dropTarget.hasClass('sek-drop-zone') ?", $dropTarget, $dropTarget.hasClass('sek-drop-zone') );
                  var isSectionDropZone = $dropTarget && $dropTarget.length > 0 && $dropTarget.hasClass( 'sek-content-preset_section-drop-zone' );
                  return $dropTarget.hasClass('sek-drop-zone') && ( ( 'preset_section' === this.dnd_draggedType && isSectionDropZone ) || ( 'module' === this.dnd_draggedType && ! isSectionDropZone ) );
            },

            // @return void()
            dnd_OnEnterOver : function( $dropTarget, evt ) {
                  evt.preventDefault();//@see :https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#droptargets
                  // Bail here if we are in the currently drag entered element
                  if ( true !== $dropTarget.data( 'is-drag-entered' ) ) {
                        // Flag now
                        $dropTarget.data( 'is-drag-entered', true );
                        $dropTarget.addClass( 'sek-active-drop-zone' );
                        // Flag the dropEl parent element
                        this.$dropZones.addClass( 'sek-is-dragging' );
                  }

                  try { this.dnd_mayBePrintPreDropElement( $dropTarget, evt ); } catch( er ) {
                        api.errare('Error when trying to insert the preDrop content', er );
                  }
            },

            // @return void()
            dnd_cleanOnLeaveDrop : function( $dropTarget, evt ) {
                  var self = this;
                  this.$dropZones = this.$dropZones || this.dnd_getDropZonesElements();
                  this.preDropElement.remove();
                  this.$dropZones.removeClass( 'sek-is-dragging' );

                  $( sektionsLocalizedData.dropSelectors, this.$dropZones ).each( function() {
                        self.dnd_cleanSingleDropTarget( $(this) );
                  });
            },

            // @return void()
            dnd_cleanSingleDropTarget : function( $dropTarget ) {
                  if ( _.isEmpty( $dropTarget ) || $dropTarget.length < 1 )
                    return;
                  $dropTarget.data( 'is-drag-entered', false );
                  $dropTarget.data( 'preDrop-position', false );
                  $dropTarget.removeClass( 'sek-active-drop-zone' );
                  $dropTarget.find('.sek-drop-zone').removeClass('sek-drag-is-close');
                  $dropTarget.find('.sek-drop-zone').removeClass('sek-drag-is-approaching');
            },


            // @return string after or before
            dnd_getPosition : function( $dropTarget, evt ) {
                  var targetRect = $dropTarget[0].getBoundingClientRect(),
                      targetHeight = targetRect.height;

                  // if the preDrop is already printed, we have to take it into account when calc. the target height
                  if ( 'before' === $dropTarget.data( 'preDrop-position' ) ) {
                        targetHeight = targetHeight + this.preDropElement.outerHeight();
                  } else if ( 'after' === $dropTarget.data( 'preDrop-position' ) ) {
                        targetHeight = targetHeight - this.preDropElement.outerHeight();
                  }

                  return evt.originalEvent.clientY - targetRect.top - ( targetHeight / 2 ) > 0  ? 'after' : 'before';
            },

            // @return void()
            dnd_mayBePrintPreDropElement : function( $dropTarget, evt ) {
                  var self = this,
                      previousPosition = $dropTarget.data( 'preDrop-position' ),
                      newPosition = this.dnd_getPosition( $dropTarget, evt  );

                  if ( previousPosition === newPosition )
                    return;

                  if ( true === self.isPrintingPreDrop ) {
                        return;
                  }

                  self.isPrintingPreDrop = true;

                  // make sure we clean the previous wrapper of the pre drop element
                  this.dnd_cleanSingleDropTarget( this.$currentPreDropTarget );
                  var inNewSection = 'between-sections' === $dropTarget.data('sek-location') || 'in-empty-location' === $dropTarget.data('sek-location');
                  $.when( self.preDropElement.remove() ).done( function(){
                        $dropTarget[ 'before' === newPosition ? 'prepend' : 'append' ]( self.preDropElement )
                              .find( '.' + sektionsLocalizedData.preDropElementClass ).html( self.dnd_getPreDropElementContent( evt ) );
                        // Flag the preDrop element with class to apply a specific style if inserted in a new sektion of in a column
                        $dropTarget.find( '.' + sektionsLocalizedData.preDropElementClass ).toggleClass('in-new-sektion', inNewSection );
                        $dropTarget.data( 'preDrop-position', newPosition );

                        self.isPrintingPreDrop = false;
                        self.$currentPreDropTarget = $dropTarget;
                  });
            },

            //@return void()
            dnd_isOveringDropTarget : function( $dropTarget, evt ) {
                  var targetRect = $dropTarget[0].getBoundingClientRect(),
                      mouseX = evt.clientX,
                      mouseY = evt.clientY,
                      tLeft = targetRect.left,
                      tRight = targetRect.right,
                      tTop = targetRect.top,
                      tBottom = targetRect.bottom,
                      isXin = mouseX >= tLeft && ( tRight - tLeft ) >= ( mouseX - tLeft),
                      isYin = mouseY >= tTop && ( tBottom - tTop ) >= ( mouseY - tTop);
                  return isXin && isYin;
            },

            //@return void()
            dnd_onDrop: function( $dropTarget, evt ) {
                  evt.stopPropagation();
                  var _position = 'after' === this.dnd_getPosition( $dropTarget, evt ) ? $dropTarget.index() + 1 : $dropTarget.index();
                  // console.log('onDropping params', position, evt );
                  // console.log('onDropping element => ', $dropTarget.data('drop-zone-before-section'), $dropTarget );
                  api.czr_sektions.trigger( 'sek-content-dropped', {
                        drop_target_element : $dropTarget,
                        location : $dropTarget.closest('[data-sek-level="location"]').data('sek-id'),
                        // when inserted between modules
                        before_module : $dropTarget.data('drop-zone-before-module-or-nested-section'),
                        after_module : $dropTarget.data('drop-zone-after-module-or-nested-section'),

                        // When inserted between sections
                        before_section : $dropTarget.data('drop-zone-before-section'),
                        after_section : $dropTarget.data('drop-zone-after-section'),

                        content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                        content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" )
                  });
            },














            //-------------------------------------------------------------------------------------------------
            //-- SCHEDULE REACTIONS TO 'sek-content-dropped'
            //-------------------------------------------------------------------------------------------------
            // invoked on api('ready') from self::initialize()
            reactToDrop : function() {
                  var self = this;
                  // @param {
                  //    drop_target_element : $(el) in which the content has been dropped
                  //    position : 'bottom' or 'top' compared to the drop-zone
                  //    content_type : single module, empty layout, preset module template
                  // }
                  var _do_ = function( params ) {
                        if ( ! _.isObject( params ) ) {
                              throw new Error( 'Invalid params provided' );
                        }
                        if ( params.drop_target_element.length < 1 ) {
                              throw new Error( 'Invalid drop_target_element' );
                        }

                        var dropCase = 'content-in-column';
                        if ( 'between-sections' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-new-section';
                        }
                        if ( 'in-empty-location' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-empty-location';
                        }
                        if ( 'between-columns' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-new-column';
                        }
                        var focusOnAddedContentEditor;
                        switch( dropCase ) {
                              case 'content-in-column' :
                                    var $closestLevelWrapper = params.drop_target_element.closest('div[data-sek-level]');
                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'No valid level dom element found' );
                                    }
                                    var _level = $closestLevelWrapper.data( 'sek-level' ),
                                        _id = $closestLevelWrapper.data('sek-id');

                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'No valid level id found' );
                                    }

                                    //console.log(' reactToDrop => drop module in column', params );
                                    api.previewer.trigger( 'sek-add-module', {
                                          level : _level,
                                          id : _id,
                                          in_column : params.drop_target_element.closest('div[data-sek-level="column"]').data( 'sek-id'),
                                          in_sektion : params.drop_target_element.closest('div[data-sek-level="section"]').data( 'sek-id'),

                                          before_module : params.before_module,
                                          after_module : params.after_module,

                                          content_type : params.content_type,
                                          content_id : params.content_id
                                    });
                              break;

                              case 'content-in-new-section' :
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;

                              case 'content-in-empty-location' :
                                    params.is_first_section = true;
                                    params.send_to_preview = false;
                                    api.previewer.trigger( 'sek-add-content-in-new-sektion', params );
                              break;

                              case 'content-in-new-column' :

                              break;
                        }
                  };

                  // @see module picker or section picker modules
                  // api.czr_sektions.trigger( 'sek-content-dropped', {
                  //       drop_target_element : $(this),
                  //       position : _position,
                  //       before_section : $(this).data('drop-zone-before-section'),
                  //       after_section : $(this).data('drop-zone-after-section'),
                  //       content_type : evt.originalEvent.dataTransfer.getData( "sek-content-type" ),
                  //       content_id : evt.originalEvent.dataTransfer.getData( "sek-content-id" )
                  // });
                  this.bind( 'sek-content-dropped', function( params ) {
                        //console.log('sek-content-dropped', params );
                        try { _do_( params ); } catch( er ) {
                              api.errare( 'error when reactToDrop', er );
                        }
                  });
            }//reactToDrop
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
( function ( api, $, _ ) {
      // all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            spacing : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-spacing-wrapper', input.container );
                  // Listen to user actions on the inputs and set the input value
                  $wrapper.on( 'input', 'input[type="number"]', function(evt) {
                        var _type_ = $(this).closest('[data-sek-spacing]').data('sek-spacing'),
                            _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} ),
                            _rawVal = $(this).val();

                        // Validates
                        // @fixes https://github.com/presscustomizr/nimble-builder/issues/26
                        if ( ( _.isString( _rawVal ) && ! _.isEmpty( _rawVal ) ) || _.isNumber( _rawVal ) ) {
                              _newInputVal[ _type_ ] = _rawVal;
                        } else {
                              // this allow users to reset a given padding / margin instead of reseting them all at once with the "reset all spacing" option
                              _newInputVal = _.omit( _type_, _newInputVal );
                        }

                        input( _newInputVal );
                  });
                  // Schedule a reset action
                  // Note : this has to be done by device
                  $wrapper.on( 'click', '.reset-spacing-wrap', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('input[type="number"]').each( function() {
                              $(this).val('');
                        });
                        // [] is the default value
                        // we could have get it with api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_spacing_module' )
                        // @see php spacing module registration
                        input( [] );
                  });

                  // Synchronize on init
                  if ( _.isObject( input() ) ) {
                        _.each( input(), function( _val_, _key_ ) {
                              $( '[data-sek-spacing="' + _key_ +'"]', $wrapper ).find( 'input[type="number"]' ).val( _val_ );
                        });
                  }
            },
            bg_position : function( input_options ) {
                  var input = this;
                  // Listen to user actions on the inputs and set the input value
                  $('.sek-bg-pos-wrapper', input.container ).on( 'change', 'input[type="radio"]', function(evt) {
                        input( $(this).val() );
                  });

                  // Synchronize on init
                  if ( ! _.isEmpty( input() ) ) {
                        input.container.find('input[value="'+ input() +'"]').attr('checked', true).trigger('click');
                  }
            },
            v_alignment : function( input_options ) {
                  var input = this,
                      $wrapper = $('.sek-v-align-wrapper', input.container );
                  // on init
                  $wrapper.find( 'div[data-sek-align="' + input() +'"]' ).addClass('selected');

                  // on click
                  $wrapper.on( 'click', '[data-sek-align]', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('.selected').removeClass('selected');
                        $.when( $(this).addClass('selected') ).done( function() {
                              input( $(this).data('sek-align') );
                        });
                  });
            },
            font_size : function( obj ) {
                  var input      = this,
                      $wrapper = $('.sek-font-size-wrapper', input.container ),
                      unit = 'px';

                  $wrapper.find( 'input[type="number"]').on('change', function() {
                        input( $(this).val() + unit );
                  }).stepper();

            },

            line_height : function( obj ) {
                  var input      = this,
                      $wrapper = $('.sek-line-height-wrapper', input.container ),
                      unit = 'px';

                  $wrapper.find( 'input[type="number"]').on('change', function() {
                        input( $(this).val() + unit );
                  }).stepper();
            },













            // FONT PICKER
            font_picker : function( input_options ) {
                  var input = this,
                      item = input.input_parent;

                  var _getFontCollections = function() {
                        var dfd = $.Deferred();
                        if ( ! _.isEmpty( input.sek_fontCollections ) ) {
                              dfd.resolve( input.sek_fontCollections );
                        } else {
                              // This utility handles a cached version of the font_list once fetched the first time
                              // @see api.CZR_Helpers.czr_cachedTmpl
                              api.CZR_Helpers.getModuleTmpl( {
                                    tmpl : 'font_list',
                                    module_type: 'font_picker_input',
                                    module_id : input.module.id
                              } ).done( function( _serverTmpl_ ) {
                                    // Ensure we have a string that's JSON.parse-able
                                    if ( typeof _serverTmpl_ !== 'string' || _serverTmpl_[0] !== '{' ) {
                                          throw new Error( 'font_picker => server list is not JSON.parse-able');
                                    }
                                    input.sek_fontCollections = JSON.parse( _serverTmpl_ );
                                    dfd.resolve( input.sek_fontCollections );
                              }).fail( function( _r_ ) {
                                    dfd.reject( _r_ );
                              });
                        }
                        return dfd.promise();
                  };
                  var _preprocessSelect2ForFontFamily = function() {
                        /*
                        * Override select2 Results Adapter in order to select on highlight
                        * deferred needed cause the selects needs to be instantiated when this override is complete
                        * selec2.amd.require is asynchronous
                        */
                        var selectFocusResults = $.Deferred();
                        if ( 'undefined' !== typeof $.fn.select2 && 'undefined' !== typeof $.fn.select2.amd && 'function' === typeof $.fn.select2.amd.require ) {
                              $.fn.select2.amd.require(['select2/results', 'select2/utils'], function (Result, Utils) {
                                    var ResultsAdapter = function($element, options, dataAdapter) {
                                      ResultsAdapter.__super__.constructor.call(this, $element, options, dataAdapter);
                                    };
                                    Utils.Extend(ResultsAdapter, Result);
                                    ResultsAdapter.prototype.bind = function (container, $container) {
                                      var _self = this;
                                      container.on('results:focus', function (params) {
                                        if ( params.element.attr('aria-selected') != 'true') {
                                          _self.trigger('select', {
                                              data: params.data
                                          });
                                        }
                                      });
                                      ResultsAdapter.__super__.bind.call(this, container, $container);
                                    };
                                    selectFocusResults.resolve( ResultsAdapter );
                              });
                        }
                        else {
                              selectFocusResults.resolve( false );
                        }

                        return selectFocusResults.promise();

                  };//_preprocessSelect2ForFontFamily

                  // @return void();
                  // Instantiates a select2 select input
                  // http://ivaynberg.github.io/select2/#documentation
                  var _setupSelectForFontFamilySelector = function( customResultsAdapter, fontCollections ) {
                        var _model = item(),
                            _googleFontsFilteredBySubset = function() {
                                  var subset = item.czr_Input('subset')(),
                                      filtered = _.filter( fontCollections.gfonts, function( data ) {
                                            return data.subsets && _.contains( data.subsets, subset );
                                      });

                                  if ( ! _.isUndefined( subset ) && ! _.isNull( subset ) && 'all-subsets' != subset ) {
                                        return filtered;
                                  } else {
                                        return fontCollections.gfonts;
                                  }

                            },
                            $fontSelectElement = $( 'select[data-czrtype="' + input.id + '"]', input.container );

                        // generates the options
                        // @param type = cfont or gfont
                        var _generateFontOptions = function( fontList, type ) {
                              var _html_ = '';
                              _.each( fontList , function( font_data ) {
                                    var _value = font_data.name,
                                        optionTitle = _.isString( _value ) ? _value.replace(/[+|:]/g, ' ' ) : _value,
                                        _setFontTypePrefix = function( val, type ) {
                                              return _.isString( val ) ? [ '[', type, ']', val ].join('') : '';//<= Example : [gfont]Aclonica:regular
                                        };

                                    _value = _setFontTypePrefix( _value, type );

                                    if ( _value == input() ) {
                                          _html_ += '<option selected="selected" value="' + _value + '">' + optionTitle + '</option>';
                                    } else {
                                          _html_ += '<option value="' + _value + '">' + optionTitle + '</option>';
                                    }
                              });
                              return _html_;
                        };

                        //add the first option
                        if ( _.isNull( input() ) || _.isEmpty( input() ) ) {
                              $fontSelectElement.append( '<option value="none" selected="selected">' + sektionsLocalizedData.i18n['Select a font family'] + '</option>' );
                        } else {
                              $fontSelectElement.append( '<option value="none">' + sektionsLocalizedData.i18n['Select a font family'] + '</option>' );
                        }


                        // generate the cfont and gfont html
                        _.each( [
                              {
                                    title : sektionsLocalizedData.i18n['Web Safe Fonts'],
                                    type : 'cfont',
                                    list : fontCollections.cfonts
                              },
                              {
                                    title : sektionsLocalizedData.i18n['Google Fonts'],
                                    type : 'gfont',
                                    list : fontCollections.gfonts//_googleFontsFilteredBySubset()
                              }
                        ], function( fontData ) {
                              var $optGroup = $('<optgroup>', { label : fontData.title , html : _generateFontOptions( fontData.list, fontData.type ) });
                              $fontSelectElement.append( $optGroup );
                        });

                        var _fonts_select2_params = {
                                //minimumResultsForSearch: -1, //no search box needed
                            //templateResult: paintFontOptionElement,
                            //templateSelection: paintFontOptionElement,
                            escapeMarkup: function(m) { return m; },
                        };
                        /*
                        * Maybe use custom adapter
                        */
                        if ( customResultsAdapter ) {
                              $.extend( _fonts_select2_params, {
                                    resultsAdapter: customResultsAdapter,
                                    closeOnSelect: false,
                              } );
                        }

                        //http://ivaynberg.github.io/select2/#documentation
                        //FONTS
                        $fontSelectElement.select2( _fonts_select2_params );
                        $( '.select2-selection__rendered', input.container ).css( getInlineFontStyle( input() ) );

                  };//_setupSelectForFontFamilySelector

                  // @return {} used to set $.css()
                  // @param font {string}.
                  // Example : Aclonica:regular
                  // Example : Helvetica Neue, Helvetica, Arial, sans-serif
                  var getInlineFontStyle = function( _fontFamily_ ){
                        // the font is set to 'none' when "Select a font family" option is picked
                        if ( ! _.isString( _fontFamily_ ) || _.isEmpty( _fontFamily_ ) )
                          return {};

                        //always make sure we remove the prefix.
                        _fontFamily_ = _fontFamily_.replace('[gfont]', '').replace('[cfont]', '');

                        var module = this,
                            split = _fontFamily_.split(':'), font_family, font_weight, font_style;

                        font_family       = getFontFamilyName( _fontFamily_ );

                        font_weight       = split[1] ? split[1].replace( /[^0-9.]+/g , '') : 400; //removes all characters
                        font_weight       = _.isNumber( font_weight ) ? font_weight : 400;
                        font_style        = ( split[1] && -1 != split[1].indexOf('italic') ) ? 'italic' : '';


                        return {
                              'font-family' : 'none' == font_family ? 'inherit' : font_family.replace(/[+|:]/g, ' '),//removes special characters
                              'font-weight' : font_weight || 400,
                              'font-style'  : font_style || 'normal'
                        };
                  };

                  // @return the font family name only from a pre Google formated
                  // Example : input is Inknut+Antiqua:regular
                  // Should return Inknut Antiqua
                  var getFontFamilyName = function( rawFontFamily ) {
                        if ( ! _.isString( rawFontFamily ) || _.isEmpty( rawFontFamily ) )
                            return rawFontFamily;

                        rawFontFamily = rawFontFamily.replace('[gfont]', '').replace('[cfont]', '');
                        var split         = rawFontFamily.split(':');
                        return _.isString( split[0] ) ? split[0].replace(/[+|:]/g, ' ') : '';//replaces special characters ( + ) by space
                  };



                  // defer the loading of the fonts when the font tab gets switched to
                  // then fetch the google fonts from the server
                  // and instantiate the select input when done
                  // @see this.trigger( 'tab-switch', { id : tabIdSwitchedTo } ); in Item::initialize()
                  item.bind( 'tab-switch', function( params ) {
                        // try { var isGFontTab = 'sek-google-font-tab' = item.container.find('[data-tab-id="' + params.id + '"]').data('sek-device'); } catch( er ) {
                        //       api.errare( 'spacing input => error when binding the tab switch event', er );
                        // }
                        //console.log( 'ALORS ????', item.container.find('[data-tab-id="' + params.id + '"]').data('sek-google-font-tab'), input.module );
                        // $.when( _getFontCollections() ).done( function( fontCollections ) {
                        //       console.log('FONT COLLECTION ?', fontCollections );
                        // }).fail( function( _r_ ) {
                        //       api.errare( 'font_picker => fail response =>', _r_ );
                        // });
                        $.when( _getFontCollections() ).done( function( fontCollections ) {
                              //console.log('FONT COLLECTION ?', fontCollections );
                              _preprocessSelect2ForFontFamily().done( function( customResultsAdapter ) {
                                    _setupSelectForFontFamilySelector( customResultsAdapter, fontCollections );
                              });
                        }).fail( function( _r_ ) {
                              api.errare( 'font_picker => fail response =>', _r_ );
                        });

                  });
            }//font_picker()
      });//$.extend( api.czrInputMap, {})


})( wp.customize, jQuery, _ );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired from ::initialize()
            setupTinyMceEditor: function() {
                  var self = this;
                  // OBSERVABLE VALUES
                  api.sekEditorExpanded   = new api.Value( false );
                  api.sekEditorSynchronizedInput = new api.Value();

                  self.editorEventsListenerSetup = false;//this status will help us ensure that we bind the shared tinyMce instance only once

                  // Cache the instance and attach
                  var mayBeAwakeTinyMceEditor = function() {
                        api.sekTinyMceEditor = api.sekTinyMceEditor || tinyMCE.get( 'czr-customize-content_editor' );

                        if ( false === self.editorEventsListenerSetup ) {
                              self.attachEventsToEditor();
                              self.editorEventsListenerSetup = true;
                              self.trigger('sek-tiny-mce-editor-bound-and-instantiated');
                        }
                  };


                  // SET THE SYNCHRONIZED INPUT
                  // CASE 1) When user has clicked on a tiny-mce editable content block
                  // CASE 2) when user click on the edit button in the module ui
                  // @see reactToPreviewMsg
                  // Each time a message is received from the preview, the corresponding action are executed
                  // and an event {msgId}_done is triggered on the current instance
                  // This is how we can listen here to 'sek-edit-module_done'
                  // The sek-edit-module is fired when clicking on a .sek-module wrapper @see ::scheduleUiClickReactions
                  self.bind( 'sek-edit-module_done', function( params ) {
                        if ( 'tiny_mce_editor' != params.clicked_input_type )
                          return;

                        // Set a new sync input
                        api.sekEditorSynchronizedInput({
                              control_id : params.id,
                              input_id : params.clicked_input_id
                        });

                        api.sekEditorExpanded( true );
                        api.sekTinyMceEditor.focus();
                  });

                  // CASE 1)
                  // Toggle the editor visibility
                  // Change the button text
                  // set the clicked input id as the new one
                  $('#customize-theme-controls').on('click', '[data-czr-action="open-tinymce-editor"]', function() {
                        //console.log( '[data-czr-action="toggle-tinymce-editor"]', $(this) , api.sekEditorSynchronizedInput() );
                        // Get the control and the input id from the clicked element
                        // => then updated the synchronized input with them
                        var control_id = $(this).data('czr-control-id'),
                            input_id = $(this).data('czr-input-id');
                        if ( _.isEmpty( control_id ) || _.isEmpty( input_id ) ) {
                              api.errare('toggle-tinymce-editor => missing input or control id');
                              return;
                        }
                        var currentEditorSyncData = $.extend( true, {}, api.sekEditorSynchronizedInput() ),
                            newEditorSyncData = _.extend( currentEditorSyncData, {
                                  input_id : input_id,
                                  control_id : control_id
                            });
                        api.sekEditorSynchronizedInput( newEditorSyncData );
                        api.sekEditorExpanded( true );
                        api.sekTinyMceEditor.focus();
                  });


                  // CASE 2)
                  // when the synchronized input gets changed by the user
                  // 1) make sure the editor is expanded
                  // 2) refresh the editor content with the input() one
                  api.sekEditorSynchronizedInput.bind( function( to, from ) {
                        mayBeAwakeTinyMceEditor();
                        //api.sekEditorExpanded( true );
                        //console.log('MODULE VALUE ?', self.getLevelProperty( { property : 'value', id : to.control_id } ) );
                        // When initializing the module, its customized value might not be set yet
                        var _currentModuleValue_ = self.getLevelProperty( { property : 'value', id : to.control_id } ),
                            _currentInputContent_ = ( _.isObject( _currentModuleValue_ ) && ! _.isEmpty( _currentModuleValue_[ to.input_id ] ) ) ? _currentModuleValue_[ to.input_id ] : '';

                        try { api.sekTinyMceEditor.setContent( _currentInputContent_ ); } catch( er ) {
                              api.errare( 'Error when setting the tiny mce editor content in setupTinyMceEditor', er );
                        }
                        api.sekTinyMceEditor.focus();
                  });//api.sekEditorSynchronizedInput.bind( function( to, from )








                  // REACT TO EDITOR VISIBILITY
                  api.sekEditorExpanded.bind( function ( expanded ) {
                        mayBeAwakeTinyMceEditor();
                        //api.infoLog('in api.sekEditorExpanded', expanded );
                        if ( expanded ) {
                            api.sekTinyMceEditor.focus();
                        }
                        $(document.body).toggleClass( 'czr-customize-content_editor-pane-open', expanded);

                        /*
                        * Ensure only the latest input is bound
                        */
                        // if ( api.sekTinyMceEditor.locker && api.sekTinyMceEditor.locker !== input ) {
                        //       //api.sekEditorExpanded.set( false );
                        //       api.sekTinyMceEditor.locker = null;
                        // } if ( ! api.sekTinyMceEditor.locker || api.sekTinyMceEditor.locker === input ) {
                        //       $(document.body).toggleClass('czr-customize-content_editor-pane-open', expanded);
                        //       api.sekTinyMceEditor.locker = input;
                        // }

                        $( window )[ expanded ? 'on' : 'off' ]('resize', function() {
                                if ( ! api.sekEditorExpanded() )
                                  return;
                                _.delay( function() {
                                      self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                                }, 50 );

                        });

                        if ( expanded ) {
                              self.czrResizeEditor( window.innerHeight - self.$editorPane.height() );
                        } else {
                              //resize reset
                              //self.container.closest( 'ul.accordion-section-content' ).css( 'padding-bottom', '' );
                              self.$preview.css( 'bottom', '' );
                              self.$collapseSidebar.css( 'bottom', '' );
                        }
                  });




                  // COLLAPSING THE EDITOR
                  // or on click on the icon located on top of the editor
                  $('#czr-customize-content_editor-pane' ).on('click', '[data-czr-action="close-tinymce-editor"]', function() {
                        api.sekEditorExpanded( false );
                  });

                  // on click anywhere but on the 'Edit' ( 'open-tinymce-editor' action ) button
                  $('#customize-controls' ).on('click', function( evt ) {
                        if ( 'open-tinymce-editor' == $( evt.target ).data( 'czr-action') )
                          return;

                        api.sekEditorExpanded( false );
                  });

                  // Pressing the escape key collapses the editor
                  // both in the customizer panel and the editor frame
                  $(document).on( 'keydown', _.throttle( function( evt ) {
                        if ( 27 === evt.keyCode ) {
                              api.sekEditorExpanded( false );
                        }
                  }, 50 ));

                  self.bind('sek-tiny-mce-editor-bound-and-instantiated', function() {
                        var iframeDoc = $( api.sekTinyMceEditor.iframeElement ).contents().get(0);
                        $( iframeDoc ).on('keydown', _.throttle( function( evt ) {
                              if ( 27 === evt.keyCode ) {
                                    api.sekEditorExpanded( false );
                              }
                        }, 50 ));
                  });

                  _.each( [
                        'sek-click-on-inactive-zone',
                        'sek-add-section',
                        'sek-add-column',
                        'sek-add-module',
                        'sek-remove',
                        'sek-move',
                        'sek-duplicate',
                        'sek-resize-columns',
                        'sek-add-content-in-new-sektion',
                        'sek-pick-module',
                        'sek-edit-options',
                        'sek-edit-module',
                        'sek-notify'
                  ], function( _evt_ ) {
                        if ( 'sek-edit-module' != _evt_ ) {
                              api.previewer.bind( _evt_, function() { api.sekEditorExpanded( false ); } );
                        } else {
                              api.previewer.bind( _evt_, function( params ) {
                                    api.sekEditorExpanded(  params.module_type === 'czr_tiny_mce_editor_module' );
                              });
                        }
                  });
            },//setupTinyMceEditor




            attachEventsToEditor : function() {
                  var self = this;
                  // Cache some dom elements
                  self.$editorTextArea = $( '#czr-customize-content_editor' );
                  self.$editorPane = $( '#czr-customize-content_editor-pane' );
                  self.$editorDragbar = $( '#czr-customize-content_editor-dragbar' );
                  self.$editorFrame  = $( '#czr-customize-content_editor_ifr' );
                  self.$mceTools     = $( '#wp-czr-customize-content_editor-tools' );
                  self.$mceToolbar   = self.$editorPane.find( '.mce-toolbar-grp' );
                  self.$mceStatusbar = self.$editorPane.find( '.mce-statusbar' );

                  self.$preview = $( '#customize-preview' );
                  self.$collapseSidebar = $( '.collapse-sidebar' );

                  // REACT TO EDITOR CHANGES
                  // bind on / off event actions
                  // Problem to solve : we need to attach event to both the visual and the text editor tab ( html editor ), which have different selectors
                  // If we bind only the visual editor, changes made to the simple textual html editor won't be taken into account
                  // VISUAL EDITOR
                  api.sekTinyMceEditor.on( 'input change keyup', function( evt ) {
                        //console.log('api.sekTinyMceEditor on input change keyup', evt.type, api.sekTinyMceEditor.getContent() );
                        // set the input value
                        if ( api.control.has( api.sekEditorSynchronizedInput().control_id ) ) {
                              try { api.control( api.sekEditorSynchronizedInput().control_id )
                                    .trigger( 'tinyMceEditorUpdated', {
                                          input_id : api.sekEditorSynchronizedInput().input_id,
                                          html_content : api.sekTinyMceEditor.getContent(),
                                          modified_editor_element : api.sekTinyMceEditor
                                    });
                              } catch( er ) {
                                    api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                              }
                        }
                  });

                  // TEXT EDITOR
                  self.$editorTextArea.on( 'input', function( evt ) {
                        //console.log('self.$editorTextArea EVENT ', evt.type, self.$editorTextArea.val() );
                        try { api.control( api.sekEditorSynchronizedInput().control_id )
                              .trigger( 'tinyMceEditorUpdated', {
                                    input_id : api.sekEditorSynchronizedInput().input_id,
                                    html_content : self.$editorTextArea.val(),
                                    modified_editor_element : self.$editorTextArea
                              });
                        } catch( er ) {
                              api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                        }
                  });



                  // LISTEN TO USER DRAG ACTIONS => RESIZE EDITOR
                  // self.$editorDragbar.on( 'mousedown mouseup', function( evt ) {
                  //       if ( ! api.sekEditorExpanded() )
                  //         return;
                  //       switch( evt.type ) {
                  //             case 'mousedown' :
                  //                   $( document ).on( 'mousemove.czr-customize-content_editor', function( event ) {
                  //                         event.preventDefault();
                  //                         $( document.body ).addClass( 'czr-customize-content_editor-pane-resize' );
                  //                         self.$editorFrame.css( 'pointer-events', 'none' );
                  //                         self.czrResizeEditor( event.pageY );
                  //                   });
                  //             break;

                  //             case 'mouseup' :
                  //                   $( document ).off( 'mousemove.czr-customize-content_editor' );
                  //                   $( document.body ).removeClass( 'czr-customize-content_editor-pane-resize' );
                  //                   self.$editorFrame.css( 'pointer-events', '' );
                  //             break;
                  //       }
                  // });
            },





            czrResizeEditor : function( position ) {
              var self = this,
                  //$sectionContent = input.container.closest( 'ul.accordion-section-content' ),
                  windowHeight = window.innerHeight,
                  windowWidth = window.innerWidth,
                  minScroll = 40,
                  maxScroll = 1,
                  mobileWidth = 782,
                  collapseMinSpacing = 56,
                  collapseBottomOutsideEditor = 8,
                  collapseBottomInsideEditor = 4,
                  args = {},
                  resizeHeight;

              if ( ! api.sekEditorExpanded() ) {
                return;
              }

              if ( ! _.isNaN( position ) ) {
                    resizeHeight = windowHeight - position;
              }

              args.height = resizeHeight;
              args.components = self.$mceTools.outerHeight() + self.$mceToolbar.outerHeight() + self.$mceStatusbar.outerHeight();

              if ( resizeHeight < minScroll ) {
                    args.height = minScroll;
              }

              if ( resizeHeight > windowHeight - maxScroll ) {
                    args.height = windowHeight - maxScroll;
              }

              if ( windowHeight < self.$editorPane.outerHeight() ) {
                    args.height = windowHeight;
              }

              self.$preview.css( 'bottom', args.height );
              self.$editorPane.css( 'height', args.height );
              self.$editorFrame.css( 'height', args.height - args.components );
              self.$collapseSidebar.css(
                    'bottom',
                    collapseMinSpacing > windowHeight - args.height ? self.$mceStatusbar.outerHeight() + collapseBottomInsideEditor : args.height + collapseBottomOutsideEditor
              );

              //$sectionContent.css( 'padding-bottom',  windowWidth <= mobileWidth ? args.height : '' );
      }
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      // Skope
      $.extend( CZRSeksPrototype, api.Events );
      var CZR_SeksConstructor   = api.Class.extend( CZRSeksPrototype );

      // Schedule skope instantiation on api ready
      // api.bind( 'ready' , function() {
      //       api.czr_skopeBase   = new api.CZR_SeksConstructor();
      // });
      try { api.czr_sektions = new CZR_SeksConstructor(); } catch( er ) {
            api.errare( 'api.czr_sektions => problem on instantiation', er );
      }
})( wp.customize, jQuery );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  //console.log('INITIALIZING SEKTION OPTIONS', id, options );
                  var module = this;

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize


            CZRInputMths : {
                  setupSelect : function() {
                          var input  = this,
                                item   = input.input_parent,
                                module = input.module;

                          if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                api.errare( 'Missing select options for input id => ' + input.id + ' in module ' + module.module_type );
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
                                      } else if ( 'px' === value ) {
                                            $.extend( _attributes, { selected : "selected" } );
                                      }
                                      $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                });
                                $( 'select[data-czrtype]', input.container ).selecter();
                          }
                  },
            },//CZRInputMths

            CZRItemConstructor : {
                  //overrides the parent ready
                  ready : function() {
                        var item = this;
                        //wait for the input collection to be populated,
                        //and then set the input visibility dependencies
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()

                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },


                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        // input controller instance == this
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              //Fire on init
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              //React on change
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'bg-image' :
                                          _.each( [ 'bg-position', 'bg-attachment', 'bg-scale', 'bg-apply-overlay', 'bg-color-overlay', 'bg-opacity-overlay' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'bg-color-overlay' :
                                                            case 'bg-opacity-overlay' :
                                                                  bool = ! _.isEmpty( input() + '' ) && api.CZR_Helpers.isChecked( item.czr_Input('bg-apply-overlay')() );
                                                            break;
                                                            default :
                                                                  bool = ! _.isEmpty( input() + '' );
                                                            break;
                                                      }
                                                      return bool;
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'bg-apply-overlay' :
                                          _.each( [ 'bg-color-overlay', 'bg-opacity-overlay' ] , function(_inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return ! _.isEmpty( item.czr_Input('bg-image')() + '' ) && api.CZR_Helpers.isChecked( input() );
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };


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
            sek_level_bg_border_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_bg_border_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_bg_border_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRInputMths : {
                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in module ' + module.module_type );
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
                                        } else if ( 'px' === value ) {
                                              $.extend( _attributes, { selected : "selected" } );
                                        }
                                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                  });
                                  $( 'select[data-czrtype]', input.container ).selecter();
                            }
                    },
            },//CZRInputMths

            CZRItemConstructor : {
                  //overrides the parent ready
                  ready : function() {
                        var item = this;
                        //wait for the input collection to be populated,
                        //and then set the input visibility dependencies
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()

                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },


                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        // input controller instance == this
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              //Fire on init
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              //React on change
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'height-type' :
                                          scheduleVisibilityOfInputId.call( input, 'custom-height', function() {
                                                return 'custom' === input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
      };


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
            sek_level_height_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_height_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_height_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputMths || {} );
                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  //module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            CZRInputMths : {
                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in module ' + module.module_type );
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
                                        } else if ( 'px' === value ) {
                                              $.extend( _attributes, { selected : "selected" } );
                                        }
                                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                  });
                                  $( 'select[data-czrtype]', input.container ).selecter();
                            }
                    },
            },//CZRInputMths
      };


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
            sek_level_section_layout_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_section_layout_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_section_layout_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var SpacingModuleConstructor = {
            initialize: function( id, options ) {
                    var module = this;
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRSpacingInputMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    module.itemConstructor = api.CZRItem.extend( module.CZRSpacingItemMths || {} );
                    //run the parent initialize
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRSpacingInputMths : {
                    // initialize : function( name, options ) {
                    //       var input = this;
                    //       api.CZRInput.prototype.initialize.call( input, name, options );
                    // },

                    setupSelect : function() {
                             var input              = this,
                                  item               = input.input_parent,
                                  module             = input.module;
                           //generates the options
                            _.each( sektionsLocalizedData.selectOptions.spacingUnits , function( title, value ) {
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
                    },
            },//CZRSpacingInputMths

            CZRSpacingItemMths : {
                    initialize : function( id, options ) {
                          api.CZRItem.prototype.initialize.call( this, id, options );
                          var item = this;
                          // Listen to tab switch event
                          // @params { id : (string) }
                          item.bind( 'tab-switch', function( params ) {
                                device = 'desktop';
                                try { device = item.container.find('[data-tab-id="' + params.id + '"]').data('sek-device'); } catch( er ) {
                                      api.errare( 'spacing input => error when binding the tab switch event', er );
                                }
                                try { api.previewedDevice( device ); } catch( er ) {
                                      api.errare( 'spacing input => error when setting the device on tab switch', er );
                                }
                          });
                    }
            },//CZRSpacingItemMths
      };


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
            sek_spacing_module : {
                  mthds : SpacingModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_spacing_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_spacing_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
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
            sek_module_picker_module : {
                  //mthds : ModulePickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_module_picker_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel :  _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_module_picker_module' )
                  )
            },
      });

      api.czrInputMap = api.czrInputMap || {};

      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            module_picker : function( input_options ) {
                var input = this;
                // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                // input.container.find('[draggable]').each( function() {
                //       $(this).on( 'mousedown mouseup', function( evt ) {
                //             switch( evt.type ) {
                //                   case 'mousedown' :
                //                         //$(this).addClass('sek-grabbing');
                //                   break;
                //                   case 'mouseup' :
                //                         //$(this).removeClass('sek-grabbing');
                //                   break;
                //             }
                //       });
                // });
                api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'module', input_container : input.container } );
                //console.log( this.id, input_options );
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var ImageModuleConstructor = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    // EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRImageInputMths || {} );
                    // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                    // run the parent initialize
                    // Note : must be always invoked always after the input / item class extension
                    // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                    api.CZRDynModule.prototype.initialize.call( module, id, options );

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
            },//initialize

            CZRImageInputMths : {
                    // initialize : function( name, options ) {
                    //       var input = this;
                    //       api.CZRInput.prototype.initialize.call( input, name, options );
                    // },

                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in image module');
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
                                        } else if ( 'px' === value ) {
                                              $.extend( _attributes, { selected : "selected" } );
                                        }
                                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                  });
                                  $( 'select[data-czrtype]', input.container ).selecter();
                            }
                    }
            },//CZRImageInputMths

            // _isChecked : function( v ) {
            //       return 0 !== v && '0' !== v && false !== v && 'off' !== v;
            // },
            //////////////////////////////////////////////////////////
            /// ITEM CONSTRUCTOR
            //////////////////////////////////////////
            CZRItemConstructor : {
                  //overrides the parent ready
                  ready : function() {
                        var item = this;
                        //wait for the input collection to be populated,
                        //and then set the input visibility dependencies
                        item.inputCollection.bind( function( col ) {
                              if( _.isEmpty( col ) )
                                return;
                              try { item.setInputVisibilityDeps(); } catch( er ) {
                                    api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                              }
                        });//item.inputCollection.bind()

                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );
                  },


                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;
                        // input controller instance == this
                        var scheduleVisibilityOfInputId = function( controlledInputId, visibilityCallBack ) {
                              //Fire on init
                              item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              //React on change
                              this.bind( function( to ) {
                                    item.czr_Input( controlledInputId ).visible( visibilityCallBack() );
                              });
                        };
                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'link-to' :
                                          _.each( [ 'link-pick-url', 'link-custom-url', 'link-target' ] , function( _inputId_ ) {
                                                try { scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'link-custom-url' :
                                                                  bool = 'url' == input() && '_custom_' == item.czr_Input('link-pick-url')().id;
                                                            break;
                                                            default :
                                                                  bool = 'url' == input();
                                                            break;
                                                      }
                                                      return bool;
                                                }); } catch( er ) {
                                                      api.errare( 'Image module => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                    case 'link-pick-url' :
                                          scheduleVisibilityOfInputId.call( input, 'link-custom-url', function() {
                                                return '_custom_' == input().id && 'url' == item.czr_Input('link-to')();
                                          });
                                    break;
                              }
                        });
                  }
            },//CZRItemConstructor

      };//ImageModuleConstructor

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
            czr_image_module : {
                  mthds : ImageModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_image_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_module' )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var TinyMceEditorModuleConstructor = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRTextEditorInputMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );

                    // run the parent initialize
                    // Note : must be always invoked always after the input / item class extension
                    // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRTextEditorInputMths : {
                    initialize : function( name, options ) {
                          var input = this;
                          // Expand the editor when ready
                          if ( 'tiny_mce_editor' == input.type ) {
                                input.isReady.then( function() {
                                      input.container.find('[data-czr-action="open-tinymce-editor"]').trigger('click');
                                });
                          }
                          api.CZRInput.prototype.initialize.call( input, name, options );
                    },

                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in image module');
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
                                        } else if ( 'px' === value ) {
                                              $.extend( _attributes, { selected : "selected" } );
                                        }
                                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                  });
                                  $( 'select[data-czrtype]', input.container ).selecter();
                            }
                    }
            },//CZRTextEditorInputMths

            // CZRSocialsItem : { },//CZRSocialsItem
      };//TinyMceEditorModuleConstructor


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
            czr_tiny_mce_editor_module : {
                  mthds : TinyMceEditorModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_tiny_mce_editor_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_tiny_mce_editor_module' )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var ModuleConstructor = {
            // initialize: function( id, options ) {
            //         //console.log('INITIALIZING IMAGE MODULE', id, options );
            //         var module = this;

            //         // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
            //         //module.inputConstructor = api.CZRInput.extend( module.CCZRInputMths || {} );
            //         // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
            //         // module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );
            //
            //         //run the parent initialize
            //         // Note : must be always invoked always after the input / item class extension
                        // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
            //         api.CZRDynModule.prototype.initialize.call( module, id, options );

            // },//initialize

            // CZRInputMths : {},//CZRInputMths

            // CZRItemMethods : { },//CZRItemMethods
      };//ModuleConstructor


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
                  mthds : ModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_heading_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_heading_module' )
            },
      });
})( wp.customize , jQuery, _ );