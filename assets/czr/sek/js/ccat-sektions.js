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

                        // INSTANTIATE SEK DROP
                        // + SCHEDULE RE-INSTANTIATION ON PREVIEW REFRESH
                        // + SCHEDULE API REACTION TO *drop event
                        // setup $.sekDrop for $( api.previewer.targetWindow().document ).find( '.sektion-wrapper')
                        // emitted by the module_picker or the section_picker module
                        // @params { type : 'section_picker' || 'module_picker' }
                        self.bind( 'sek-refresh-sekdrop', function( params ) {
                              var $sekDropEl = $( api.previewer.targetWindow().document ).find( '.sektion-wrapper');
                              if ( $sekDropEl.length > 0 ) {
                                    self.setupSekDrop( params.type, $sekDropEl );//<= module or section picker
                              } else {
                                    api.errare('control panel => api.czr_sektions => no .sektion-wrapper found when setting up the drop zones.');
                              }
                        });

                        // on previewer refresh
                        api.previewer.bind( 'ready', function() {
                              var $sekDropEl = $( api.previewer.targetWindow().document ).find( '.sektion-wrapper');
                              // if the module_picker or the section_picker is currently a registered ui control,
                              // => re-instantiate sekDrop on the new preview frame
                              // the registered() ui levels look like :
                              // [
                              //   { what: "control", id: "__sek___sek_draggable_sections_ui", label: "@missi18n Section Picker", type: "czr_module", module_type: "sek_section_picker_module", …}
                              //   { what: "setting", id: "__sek___sek_draggable_sections_ui", dirty: false, value: "", transport: "postMessage", … }
                              //   { what: "section", id: "__sek___sek_draggable_sections_ui", title: "@missi18n Section Picker", panel: "__sektions__", priority: 30}
                              // ]
                              if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_section_picker_module' } ) ) ) {
                                    self.setupSekDrop( 'section_picker', $sekDropEl );
                              } else if ( ! _.isUndefined( _.findWhere( self.registered(), { module_type : 'sek_module_picker_module' } ) ) ) {
                                    self.setupSekDrop( 'module_picker', $sekDropEl );
                              }
                        });

                        // React to the *-droped event
                        self.reactToDrop();


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

                        // TEST
                        // @see php wp_ajax_sek_import_attachment
                        // wp.ajax.post( 'sek_import_attachment', {
                        //       rel_path : '/assets/img/41883.jpg'
                        // }).done( function( data) {
                        //       console.log('DATA', data );
                        // }).fail( function( _er_ ) {
                        //       api.errare( 'sek_import_attachment ajax action failed', _er_ );
                        // });

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
                  // The parent panel for all ui sections + global options section
                  this.register({
                        what : 'panel',
                        id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                        title: '@missi18n Main sektions panel',
                        priority : 1000,
                        constructWith : SektionPanelConstructor,
                        track : false//don't register in the self.registered()
                  });
            },//mayBeRegisterAndSetupAddNewSektionSection()




            //@return void()
            // sektionsData is built server side :
            //array(
            //     'db_values' => sek_get_skoped_seks( $skope_id ),
            //     'setting_id' => sek_get_seks_setting_id( $skope_id )//sek___[skp__post_page_home]
            // )
            setContextualCollectionSettingIdWhenSkopeSet : function( newSkopes, previousSkopes ) {
                  var self = this;

                  // Clear all previous sektions if we're coming from a previousSkopes
                  if ( ! _.isEmpty( previousSkopes.local ) ) {
                        api.previewer.trigger('sek-pick-section');
                  }

                  // set the sekCollectionSettingId now, and update it on skope change
                  sektionsData = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local');
                  api.infoLog( '::setContextualCollectionSettingIdWhenSkopeSet => SEKTIONS DATA ? ', sektionsData );
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
            // 1) register the collection setting sek___[{$skope_id}] ( ex : sek___[skp__post_page_20] )
            // 2) validate that the setting is well formed before being changed
            // 3) schedule reactions on change ?
            // @return void()
            setupSettingToBeSaved : function() {
                  var self = this,
                      serverCollection;

                  serverCollection = api.czr_skopeBase.getSkopeProperty( 'sektions', 'local').db_values;
                  // maybe register the sektion_collection setting
                  var collectionSettingId = self.sekCollectionSettingId();// [ 'sek___' , '[', newSkopes.local, ']' ].join('');
                  if ( _.isEmpty( collectionSettingId ) ) {
                        throw new Error( 'setupSettingsToBeSaved => the collectionSettingId is invalid' );
                  }

                  // if the collection setting is not registered yet
                  // => register it and bind it
                  if ( ! api.has( collectionSettingId ) ) {
                        var __collectionSettingInstance__ = self.register({
                              what : 'setting',
                              id : collectionSettingId,
                              value : self.validateSettingValue( _.isObject( serverCollection ) ? serverCollection : self.defaultSektionSettingValue ),
                              transport : 'postMessage',//'refresh'
                              type : 'option',
                              track : false//don't register in the self.registered()
                        });

                        api( collectionSettingId, function( sektionSetInstance ) {
                              // Is the collection well formed ?
                              // @see customize-base.js
                              //sektionSetInstance.validate = self.validateSettingValue;


                              // Schedule reactions to a collection change
                              sektionSetInstance.bind( function( newSektionSettingValue, previousValue, params ) {
                                    console.log( 'newSektionSettingValue => ', newSektionSettingValue );
                              });
                        });//api( collectionSettingId, function( sektionSetInstance ){}
                  }


                  // global options for all collection setting of this skope_id
                  // loop_start, before_content, after_content, loop_end

                  // Global Options : section
                  // this.register({
                  //       what : 'section',
                  //       id : sektionsLocalizedData.optPrefixForSektionGlobalOptsSetting,//'__sektions__'
                  //       title: '@missi18n Global Options',
                  //       priority : 1000,
                  //       constructWith : SektionPanelConstructor,
                  //       track : false//don't register in the self.registered()
                  // });

                  // // => register a control
                  // // Template
                  // this.register({
                  //       what : 'control',
                  //       id : sektionsLocalizedData.sektionsPanelId,//'__sektions__'
                  //       title: '@missi18n Main sektions panel',
                  //       priority : 1000,
                  //       constructWith : SektionPanelConstructor,
                  //       track : false//don't register in the self.registered()
                  // });
            },


            // Fired :
            // 1) when instantiating the setting
            // 2) on each setting change, as an override of api.Value::validate( to ) @see customize-base.js
            //
            // A collection should be formed this way :
            // {
            //    options : {}
            //    collection : [
            //        {
            //            id : 'loop_start'
            //            level : 'location',
            //            options : {},
            //            collection : [
            //                {
            //                    id : sek_234234123245345,
            //                    level : 'section',
            //                    options : {},
            //                    collection : [
            //                         {
            //                             id : sek_45345234245245,
            //                             level : 'column',
            //                             options : {},
            //                             collection : [
            //                                {
            //                                    id : sek_234234234234435,
            //                                    level : 'module',
            //                                    module_type : 'image',
            //                                    options : {}
            //                                },
            //                                {
            //                                    id : sek_234234234234435,
            //                                    level : 'module',
            //                                    module_type : 'image',
            //                                    options : {}
            //                                },
            //                                {
            //                                    id : sek_234234234234435,
            //                                    level : 'section',
            //                                    is_nested : true,
            //                                    options : {},
            //                                    collection : [
            //                                        {
            //                                            id : sek_234234234242342,
            //                                            level : 'column',
            //                                            options : {},
            //                                            collection : [ module1, module 2, ... ]
            //                                        },
            //                                        { ... }
            //                                    ]//end of nested section collection
            //                                },
            //                             ]//end of module collection
            //                         },
            //                         {
            //                             id : sek_45345234245245,
            //                             level : 'column',
            //                             options : {},
            //                             collection : [ ... ]
            //                         },
            //                         ...
            //                    ]// end of column collection
            //                },
            //                {
            //                    id : sek_234234123245345,
            //                    level : 'section',
            //                    options : {},
            //                    collection : [ ... ]
            //                },
            //                ...
            //            ]// end of section collection
            //        }
            //        {
            //            id : 'loop_end'
            //            level : 'location',
            //            options : {},
            //            collection : [ ... ]
            //        }
            //        ...
            //    ]//end of location collection
            // }
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
                                      '@missi18n If this problem prevents you to use the Nimble builder, you might need to reset the sections for this page.',
                                      '<br>',
                                      '<span style="text-align:center;display:block">',
                                        '<button type="button" class="button" aria-label="@missi18n Reset" data-sek-reset="true">@missi18n Reset</button>',
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
                                        // A section must have a "location" level parent
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
            // 2) a real reset should delete the sektion post ( sek_post_type, with for example title sek___skp__post_page_21 ) and its database option storing its id ( for example : sek___skp__post_page_21 )
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
                              message: '@missi18n Reset complete',
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
                            // UPDATE THE MAIN SETTING
                            'sek-add-section' :{
                                  callback : function( params ) {
                                        sendToPreview = true;
                                        uiParams = {};
                                        apiParams = {
                                              action : 'sek-add-section',
                                              id : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid(),
                                              location : params.location,
                                              in_sektion : params.in_sektion,
                                              in_column : params.in_column,
                                              is_nested : ! _.isEmpty( params.in_sektion ) && ! _.isEmpty( params.in_column )
                                        };
                                        return self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        // When a section is created ( not duplicated )
                                        // Send back a msg to the panel to automatically add an initial column in the created sektion
                                        api.previewer.trigger( 'sek-add-column', {
                                              in_sektion : params.apiParams.id,
                                              autofocus : false//<=We want to focus on the section ui in this case, that's why the autofocus is set to false
                                        });
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
                                              position : params.position
                                        };
                                        return  self.updateAPISetting( apiParams );
                                  },
                                  complete : function( params ) {
                                        api.previewer.trigger('sek-edit-module', {
                                              id : params.apiParams.id,
                                              level : 'module',
                                              in_sektion : params.apiParams.in_sektion,
                                              in_column : params.apiParams.in_column
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
                                  complete : function() {
                                        api.previewer.trigger( 'sek-pick-module', {});
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
                                        sendToPreview = true;
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
                                        switch( params.content_type) {
                                              case 'module' :
                                                    api.previewer.trigger('sek-edit-module', {
                                                          level : 'module',
                                                          id : params.apiParams.droppedModuleId
                                                    });
                                              break;
                                        }
                                  }
                            },





                            // GENERATE UI ELEMENTS
                            'sek-pick-module' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : 'module'
                                  };
                                  return self.generateUI( uiParams );
                            },
                            'sek-pick-section' : function( params ) {
                                  sendToPreview = true;
                                  apiParams = {};
                                  uiParams = {
                                        action : 'sek-generate-draggable-candidates-picker-ui',
                                        content_type : 'section'
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
                              // If so, don't generate the ui again, imply focus on it
                              if ( self.isUIElementCurrentlyGenerated( _id_ ) ) {
                                    api.control( _id_ ).focus({
                                          completeCallback : function() {}
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
                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : _id_,
                                                dirty : false,
                                                value : '',
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_'// columnData.settingType
                                          });
                                    }

                                    self.register( {
                                          level : params.level,
                                          what : 'control',
                                          id : _id_,
                                          label : 'module' === params.content_type ? '@missi18n Module Picker' : '@missi18n Section Picker',
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'module' === params.content_type ? 'sek_module_picker_module' : 'sek_section_picker_module',
                                          section : _id_,
                                          priority : 10,
                                          settings : { default : _id_ }
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
                              self.register({
                                    what : 'section',
                                    id : _id_,
                                    title: 'module' === params.content_type ? '@missi18n Module Picker' : '@missi18n Section Picker',
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 30,
                                    //track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });
                        break;
















                        case 'sek-generate-module-ui' :
                              if ( _.isEmpty( params.id ) ) {
                                    dfd.reject( 'generateUI => missing id' );
                              }
                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, simply focus on it
                              if ( self.isUIElementCurrentlyGenerated( params.id ) ) {
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
                                                      self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_markup',
                                                            uiParams : _.extend( params, { action : 'sek-set-module-value' } ),
                                                            //options_type : 'spacing',
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      });
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });

                                          self.register({
                                                level : params.level,
                                                what : 'setting',
                                                id : params.id,
                                                dirty : false,
                                                value : moduleValue,
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_'// columnData.settingType
                                          });
                                    }



                                    self.register( {
                                          level : params.level,
                                          what : 'control',
                                          id : params.id,
                                          label : '@missi18n Module ' + params.id,
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
                              self.register({
                                    what : 'section',
                                    id : params.id,
                                    title: '@missi18n Content for ' + api.czrModuleMap[ moduleType ].name,
                                    panel : sektionsLocalizedData.sektionsPanelId,
                                    priority : 20,
                                    //track : false//don't register in the self.registered()
                                    //constructWith : MainSectionConstructor,
                              });

                        break;














                        case 'sek-generate-level-options-ui' :
                              // Generate the UI for level options

                              var layoutBgBorderOptionsSetId = params.id + '__layoutBgBorder_options',
                                  spacingOptionsSetId = params.id + '__spacing_options';

                              // Is the UI currently displayed the one that is being requested ?
                              // If so, don't generate the ui again, imply focus on it
                              if ( self.isUIElementCurrentlyGenerated( layoutBgBorderOptionsSetId ) || self.isUIElementCurrentlyGenerated( spacingOptionsSetId ) ) {
                                    api.control( layoutBgBorderOptionsSetId ).focus({
                                          completeCallback : function() {}
                                    });
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
                                    // REGISTER LAYOUT BACKGROUND BORDER OPTIONS
                                    // Make sure this setting is bound only once !
                                    if( ! api.has( layoutBgBorderOptionsSetId ) ) {
                                          // Schedule the binding to synchronize the options with the main collection setting
                                          // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                          // They need to be kept in order to keep track of the changes in the customizer.
                                          // => that's why we check if ! api.has( ... )
                                          api( layoutBgBorderOptionsSetId, function( _setting_ ) {
                                                _setting_.bind( _.debounce( function( to, from, args ) {
                                                      self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'layout_background_border',
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      });
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( layoutBgBorderOptionsSetId, function( _setting_ ) {})


                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : layoutBgBorderOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.lbb || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_' //sekData.settingType
                                          });
                                    }//if( ! api.has( layoutBgBorderOptionsSetId ) ) {

                                    self.register( {
                                          level : params.level,
                                          level_id : params.id,
                                          what : 'control',
                                          id : layoutBgBorderOptionsSetId,
                                          label : '@missi18n Layout Background and Border',
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_level_layout_bg_module',
                                          section : params.id,
                                          priority : 10,
                                          settings : { default : layoutBgBorderOptionsSetId }
                                    }).done( function() {
                                          api.control( layoutBgBorderOptionsSetId ).focus({
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
                                                      self.updateAPISettingAndExecutePreviewActions({
                                                            defaultPreviewAction : 'refresh_stylesheet',
                                                            uiParams : _.extend( params, { action : 'sek-set-level-options' } ),
                                                            options_type : 'spacing',
                                                            settingParams : {
                                                                  to : to,
                                                                  from : from,
                                                                  args : args
                                                            }
                                                      });
                                                }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                          });//api( layoutBgBorderOptionsSetId, function( _setting_ ) {})


                                          self.register( {
                                                level : params.level,
                                                what : 'setting',
                                                id : spacingOptionsSetId,
                                                dirty : false,
                                                value : optionDBValue.spacing || {},
                                                transport : 'postMessage',// 'refresh',
                                                type : '_no_intended_to_be_saved_' //sekData.settingType
                                          });
                                    }



                                    self.register( {
                                          level : params.level,
                                          what : 'control',
                                          id : spacingOptionsSetId,
                                          label : '@missi18n Spacing : padding and margin',
                                          type : 'czr_module',//sekData.controlType,
                                          module_type : 'sek_spacing_module',
                                          section : params.id,
                                          priority : 10,
                                          settings : { default : spacingOptionsSetId }
                                    }).done( function() {
                                          // synchronize the options with the main collection setting
                                          api.control( spacingOptionsSetId ).focus({
                                                completeCallback : function() {}
                                          });
                                    });
                              };//_do_register_

                              // Defer the registration when the parent section gets added to the api
                              api.section.when( params.id, function() {
                                    _do_register_();
                              });

                              self.register({
                                    what : 'section',
                                    id : params.id,
                                    title: '@missi18n Options for ' + params.level,
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
            //     options_type : 'layout_background_border',
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
            updateAPISettingAndExecutePreviewActions : function( params ) {
                  console.log('PARAMS in updateAPISettingAndExecutePreviewActions', params );
                  var self = this;
                  //console.log('sek-generate-level-options-ui => ARGS ?',_setting_.id, args );
                  // We don't want to store the default title and id module properties
                  var moduleValueCandidate = {};
                  _.each( params.settingParams.to, function( _val, _property ) {
                        if ( ! _.contains( ['title', 'id' ], _property ) ) {
                              moduleValueCandidate[ _property ] = _val;
                        }
                  });

                  // What to do in the preview ?
                  // The action to trigger is determined by the changed input
                  // For the options of a level, the default action is to refresh the stylesheet.
                  // But we might need to refresh the markup in some cases. Like for example when a css class is added.
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
                        self.updateAPISetting({
                              action : params.uiParams.action,
                              id : params.uiParams.id,
                              value : moduleValueCandidate,
                              in_column : params.uiParams.in_column,
                              in_sektion : params.uiParams.in_sektion,

                              // specific for level options
                              options_type : params.options_type,//'spacing', 'layout_background_border'

                        }).done( function( ) {
                              console.log('updateAPISettingAndExecutePreviewActions => updateAPISetting done');
                              // STYLESHEET => default action when modifying the level options
                              if ( true === refresh_stylesheet ) {
                                    api.previewer.send( 'sek-refresh-stylesheet', {
                                          skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
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

                  // if the changed input is a google font modifier, we want to first refresh the google font collection, and then proceed to the requested action
                  // this way we make sure that the customized value used when ajaxing will take into account when writing the google font http request link
                  if ( true === refresh_fonts ) {
                        var _getChangedFontFamily = function() {
                              if ( 'font-family' != params.settingParams.args.input_changed ) {
                                    api.errare( 'updateAPISettingAndExecutePreviewActions => Error when refreshing fonts => the input id is not font-family', params );
                                    return;
                              } else {
                                    return params.settingParams.args.input_value;
                              }
                        };
                        var newFontFamily = '';
                        try { newFontFamily = _getChangedFontFamily(); } catch( er) {
                              api.errare( 'updateAPISettingAndExecutePreviewActions => Error when refreshing fonts', er );
                        }
                        // add it only if gfont
                        if ( newFontFamily.indexOf('gfont') > -1 ) {
                              self.updateAPISetting({
                                    action : 'sek-update-fonts',
                                    font_family : newFontFamily
                              }).done( function( ) {
                                    _doUpdateWithRequestedAction();
                              });
                        } else {
                             _doUpdateWithRequestedAction();
                        }
                  } else {
                        _doUpdateWithRequestedAction();
                  }
            },//updateAPISettingAndExecutePreviewActions






            // Is the UI currently displayed the one that is being requested ?
            // If so, don't generate the ui again
            // @return bool
            isUIElementCurrentlyGenerated : function( uiElementId ) {
                  var self = this,
                      uiCandidate = _.filter( self.registered(), function( registered ) {
                            return registered.id == uiElementId && 'control' == registered.what;
                      });
                  if ( _.isEmpty( uiCandidate ) ) {
                        return false;
                  } else {
                        // we have match => don't generate the ui
                        // we should have only one uiCandidate with this very id
                        if ( uiCandidate.length > 1 ) {
                             throw new Error( 'generateUI => why is this control registered more than once ? => ' + uiElementId );
                        } else {
                              return true;
                        }
                  }
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
                            cloneId; //will be passed in resolve()

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
                                                dfd.reject( sektionsLocalizedData.i18n["You've reached the maximum number of allowed nested sections."]);
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
                                                collection : [],
                                                is_nested : true
                                          });
                                    } else {
                                          locationCandidate = self.getLevelModel( params.location, newSetValue.collection );
                                          if ( 'no_match' == locationCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                                                dfd.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                                                break;
                                          }
                                          // @see reactToCollectionSettingIdChange
                                          locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                                          locationCandidate.collection.push({
                                                id : params.id,
                                                level : 'section',
                                                collection : []
                                                //module_type : 'czr_simple_html_module',
                                                //settingType : '_no_intended_to_be_saved_',
                                                //controlType : 'czr_module',
                                                //value : []
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
                                          break;
                                    }
                                    if ( 'no_match' == resizedColumn ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => there should be a sister column' );
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
                                    if ( 'no_match' != columnCandidate ) {
                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                                          var _insertionPosition = _.isEmpty( columnCandidate.collection ) ? 0 : params.position;
                                          columnCandidate.collection.splice( _insertionPosition, 0, {
                                                id : params.id,
                                                level : 'module',
                                                module_type : params.module_type
                                          });
                                    } else {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                    }
                              break;

                              case 'sek-duplicate-module' :
                                    // an id must be provided
                                    if ( _.isEmpty( params.id ) ) {
                                          throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                                    }
                                    columnCandidate = self.getLevelModel( params.in_column, newSetValue.collection );
                                    if ( 'no_match' == columnCandidate ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                          break;
                                    }

                                    columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                    var deepClonedModule;
                                    try { deepClonedModule = self.cloneLevel( params.id ); } catch( er ) {
                                          api.errare( 'updateAPISetting => ' + params.action, er );
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
                                          case 'layout_background_border' :
                                                _candidate_.options.lbb = _valueCandidate;
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
                                    //console.log('update API Setting => sek-add-content-in-new-sektion => PARAMS', params );
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
                                                                              module_type : params.content_id
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
                                                      break;
                                                }
                                                if ( ! _.isObject( presetSectionCandidate ) || _.isEmpty( presetSectionCandidate ) ) {
                                                      api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetSectionCandidate );
                                                      break;
                                                }
                                                //console.log('SOOOOOOOOOO => ', presetSectionCandidate );
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
                                    if ( _.isEmpty( params.font_family ) ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => missing font_family' );
                                          break;
                                    }
                                    if ( params.font_family.indexOf('gfont') < 0 ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => error => must be a google font, prefixed gfont' );
                                          break;
                                    }
                                    // Get the gfonts from the level options and modules values
                                    var currentGfonts = self.sniffGFonts();
                                    if ( ! _.contains( currentGfonts, params.font_family ) ) {
                                         currentGfonts.push( params.font_family );
                                    }
                                    // update the global gfonts
                                    //console.log('currentGfonts ', currentGfonts );
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
            register : function( params ) {
                  if ( ! _.has( params, 'id' ) ){
                        api.errare( 'register => missing id ', params );
                        return;
                  }
                  // For the UI elements that we want to track, a level property is needed
                  // if ( false !== params.track && ! _.has( params, 'level' ) ){
                  //       api.errare( 'register => missing trackable level ', params );
                  //       return;
                  // }

                  var __element__ = {}, defaults;

                  switch ( params.what ) {
                        // Register only if not registered already
                        // For example, when saved as draft in a changeset, the setting is already dynamically registered server side
                        // => in this case, we only need to register the associated control
                        // @params args { id : , value : , transport : , type :  }
                        case 'setting' :
                              if ( api.has( params.id ) ) {
                                    //api.consoleLog( 'registerSetting => setting Id already registered : ' + params.id );
                                    return params;
                              }
                              defaults = $.extend( true, {}, api.Setting.prototype.defaults );
                              var settingArgs = _.extend(
                                  defaults ,
                                    {
                                          dirty : ! _.isUndefined( params.dirty ) ? params.dirty : false,
                                          value : params.value || [],
                                          transport : params.transport || 'refresh',
                                          type : params.type || 'option'
                                    }
                              );
                              // assign the value sent from the server


                              // console.log('registerDynamicModuleSettingControl => SETTING DATA ?', params.id, settingArgs);
                              var SettingConstructor = api.settingConstructor[ settingArgs.type ] || api.Setting;
                              try { api.add( new SettingConstructor( params.id, settingArgs.value, settingArgs ) ); } catch ( er ) {
                                    api.errare( 'czr_sektions::register => problem when adding a setting to the api', er );
                              }
                        break;


                        case 'panel' :
                              // Check if we have a correct section
                              if ( ! _.has( params, 'id' ) ){
                                    throw new Error( 'registerPanel => missing panel id ');
                              }

                              if ( api.section.has( params.id ) ) {
                                    //api.errare( 'registerPanel => ' + params.id + ' is already registered');
                                    break;
                              }

                              defaults = $.extend( true, {}, api.Panel.prototype.defaults );
                              var panelParams = _.extend(
                                  defaults , {
                                        id: params.id,
                                        title: params.title || params.id,
                                        priority: _.has( params, 'priority' ) ? params.priority : 0
                                  }
                              );

                              var PanelConstructor = _.isObject( params.constructWith ) ? params.constructWith : api.Panel;
                              panelParams = _.extend( { params: panelParams }, panelParams ); // Inclusion of params alias is for back-compat for custom panels that expect to augment this property.

                              try { __element__ = api.panel.add( new PanelConstructor( params.id, panelParams ) ); } catch ( er ) {
                                    api.errare( 'czr_sektions::register => problem when adding a panel to the api', er );
                              }
                        break;


                        case 'section' :
                              // MAYBE REGISTER THE SECTION
                              // Check if we have a correct section
                              if ( ! _.has( params, 'id' ) ){
                                    throw new Error( 'registerSection => missing section id ');
                              }

                              if ( api.section.has( params.id ) ) {
                                    //api.errare( 'registerSection => ' + params.id + ' is already registered');
                                    break;
                              }

                              defaults = $.extend( true, {}, api.Section.prototype.defaults );
                              var sectionParams = _.extend(
                                  defaults, {
                                        content : '',
                                        id: params.id,
                                        title: params.title,
                                        panel: params.panel,
                                        priority: params.priority,
                                        description_hidden : false,
                                        customizeAction: sektionsLocalizedData.i18n['Customizing']
                                  }
                              );

                              var SectionConstructor = ! _.isUndefined( params.constructWith ) ? params.constructWith : api.Section;
                              sectionParams = _.extend( { params: sectionParams }, sectionParams ); // Inclusion of params alias is for back-compat for custom panels that expect to augment this property.
                              try { __element__ = api.section.add( new SectionConstructor( params.id, sectionParams ) ); } catch ( er ) {
                                    api.errare( 'czr_sektions::register => problem when adding a section to the api', er );
                              }
                        break;


                        case 'control' :
                              if ( api.control.has( params.id ) ) {
                                    api.errorLog( 'registerControl => ' + params.id + ' is already registered');
                                     break;
                              }

                              //console.log('PARAMS BEFORE REGISTERING A CONTROL => ', params);

                              //@see api.settings.controls,
                              defaults = $.extend( true, {}, api.Control.prototype.defaults );
                              var controlArgs = _.extend(
                                        defaults,
                                        {
                                              content : '',
                                              label : params.label || params.id,
                                              priority : params.priority,
                                              section : params.section,
                                              settings: params.settings,
                                              type : params.type, //'czr_module',
                                              module_type : params.module_type,
                                              input_attrs : params.input_attrs,//<= can be used with the builtin "button" type control
                                              sek_registration_params : params// <= used when refreshing a level for example
                                        }
                                  ),
                                  ControlConstructor = api.controlConstructor[ controlArgs.type ] || api.Control,
                                  options;

                              options = _.extend( { params: controlArgs }, controlArgs ); // Inclusion of params alias is for back-compat for custom controls that expect to augment this property.
                              try { __element__ = api.control.add( new ControlConstructor( params.id, options ) ); } catch ( er ) {
                                    api.errare( 'czr_sektions::register => problem when adding a control to the api', er );
                              }
                        break;
                        default :
                              api.errorLog('invalid "what" when invoking the register() method');
                        break;

                  }//switch
                  __element__ = ! _.isEmpty( __element__ ) ?  __element__ : { deferred : { embedded : $.Deferred( function() { this.resolve(); }) } };

                  // POPULATE THE REGISTERED COLLECTION
                  if ( false !== params.track ) {
                        var currentlyRegistered = this.registered();
                        var newRegistered = $.extend( true, [], currentlyRegistered );
                        //Check for duplicates
                        var duplicateCandidate = _.findWhere( newRegistered, { id : params.id } );
                        if ( ! _.isEmpty( duplicateCandidate ) && _.isEqual( duplicateCandidate, params ) ) {
                              throw new Error( 'register => duplicated element in self.registered() collection ' + params.id );
                        }
                        newRegistered.push( params );
                        this.registered( newRegistered );

                        // say it
                        //this.trigger( [params.what, params.id , 'registered' ].join('__'), params );
                  }

                  return 'setting' == params.what ? params : __element__.deferred.embedded;
            },

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
                                          api[ _reg_.what ]( _reg_.id ).trigger('czr-pre-removal', _reg_ );
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
            // 1) be the value of a 'font-family' property
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
                        if ( 'font-family' == _key_ ) {
                              if ( levelData.indexOf('gfont') > -1 ) {
                                    gfonts.push( levelData );
                              }
                        }

                        if ( _.isArray( levelData ) || _.isObject( levelData ) ) {
                              self.sniffGFonts( gfonts, levelData );
                        }
                  });
                  return gfonts;
            },
      });//$.extend()
})( wp.customize, jQuery );//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // fired in ::initialize, on 'sek-refresh-sekdrop' AND on previewer('ready') each time the previewer is refreshed
            // 'sek-refresh-sekdrop' is emitted by the section and the module picker modules with param { type : 'section_picker' || 'module_picker'}
            // @param type 'section_picker' || 'module_picker'
            // @param $el = $( api.previewer.targetWindow().document ).find( '.sektion-wrapper');
            setupSekDrop : function( type, $el ) {
                  if ( $el.length < 1 ) {
                        throw new Error( 'setupSekDrop => invalid Dom element');
                  }

                  // this is the jQuery element instance on which sekDrop shall be fired
                  var instantiateSekDrop = function() {
                        if ( $(this).length < 1 ) {
                              throw new Error( 'instantiateSekDrop => invalid Dom element');
                        }
                        //console.log('instantiateSekDrop', type, $el );
                        var baseOptions = {
                              axis: [ 'vertical' ],
                              isDroppingAllowed: function() { return true; }, //self.isDroppingAllowed.bind( self ),
                              placeholderClass: 'sortable-placeholder',
                              onDragEnter : function( side, event) {
                                 // console.log('On drag enter', event, side , $(event.currentTarget));
                                  //$(event.currentTarget).closest('div[data-sek-level="section"]').trigger('mouseenter');
                                  //console.log('closest column id ?', $(event.currentTarget).closest('div[data-sek-level="column"]').data('sek-id') );
                              },
                              // onDragLeave : function( event, ui) {
                              //     console.log('On drag enter', event, ui );
                              //     $(event.currentTarget).find('[data-sek-action="pick-module"]').show();
                              // },
                              //onDragOver : function( side, event) {},
                              onDropping: function( side, event ) {
                                    event.stopPropagation();
                                    var _position = 'bottom' === side ? $(this).index() + 1 : $(this).index();
                                    //console.log('ON DROPPING', event.originalEvent.dataTransfer.getData( "module-params" ), $(self) );

                                    // console.log('onDropping params', side, event );
                                    // console.log('onDropping element => ', $(self) );
                                    api.czr_sektions.trigger( 'sek-content-dropped', {
                                          drop_target_element : $(this),
                                          location : $(this).closest('[data-sek-level="location"]').data('sek-id'),
                                          position : _position,
                                          before_section : $(this).data('sek-before-section'),
                                          after_section : $(this).data('sek-after-section'),
                                          content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                                          content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                                    });
                              }
                        };

                        var options = {};
                        switch ( type ) {
                              case 'module_picker' :
                                    options = {
                                          items: [
                                                '.sek-module-drop-zone-for-first-module',//the drop zone when there's no module or nested sektion in the column
                                                '.sek-module',// the drop zone when there is at least one module
                                                '.sek-column > .sek-module-wrapper sek-section',// the drop zone when there is at least one nested section
                                                '.sek-content-drop-zone'//between sections
                                          ].join(','),
                                          placeholderContent : function( evt ) {
                                                var $target = $( evt.currentTarget ),
                                                    html = '@missi18n Insert Here';

                                                if ( $target.length > 0 ) {
                                                    if ( 'between-sections' == $target.data('sek-location') ) {
                                                          html = '@missi18n Insert in a new section';
                                                    }
                                                }
                                                return '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                                          },

                                    };
                              break;

                              case 'section_picker' :
                                    options = {
                                          items: [
                                                '.sek-content-drop-zone'//between sections
                                          ].join(','),
                                          placeholderContent : function( evt ) {
                                                $target = $( evt.currentTarget );
                                                var html = '@missi18n Insert a new section here';
                                                return '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                                          },
                                    };
                              break;

                              default :
                                    api.errare( '::setupSekDrop => missing picker type' );
                              break;
                        }

                        var _opts_ = $.extend( true, {}, baseOptions );
                        options = _.extend( _opts_, options );
                        $(this).sekDrop( options ).attr('data-sek-droppable-type', type );
                  };//instantiateSekDrop()

                  //console.log("$( api.previewer.targetWindow().document ).find( '.sektion-wrapper')", $( api.previewer.targetWindow().document ).find( '.sektion-wrapper') );

                  if ( ! _.isUndefined( $el.data('sekDrop') ) ) {
                        $el.sekDrop( 'destroy' );
                  }

                  try {
                        instantiateSekDrop.call( $el );
                  } catch( er ) {
                        api.errare( '::setupSekDrop => Error when firing instantiateSekDrop', er );
                  }
            },//setupSekDrop()


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
                        if ( 'between-columns' === params.drop_target_element.data('sek-location') ) {
                              dropCase = 'content-in-new-column';
                        }
                        var focusOnAddedContentEditor;
                        switch( dropCase ) {
                              case 'content-in-column' :
                                    //console.log('PPPPPPPPoooorrams', params );
                                    var $closestLevelWrapper = params.drop_target_element.closest('div[data-sek-level]');
                                    if ( 1 > $closestLevelWrapper.length ) {
                                        throw new Error( 'No valid level dom element found' );
                                    }
                                    var _level = $closestLevelWrapper.data( 'sek-level' ),
                                        _id = $closestLevelWrapper.data('sek-id');

                                    if ( _.isEmpty( _level ) || _.isEmpty( _id ) ) {
                                        throw new Error( 'No valid level id found' );
                                    }

                                    api.previewer.trigger( 'sek-add-module', {
                                          level : _level,
                                          id : _id,
                                          in_column : params.drop_target_element.closest('div[data-sek-level="column"]').data( 'sek-id'),
                                          in_sektion : params.drop_target_element.closest('div[data-sek-level="section"]').data( 'sek-id'),
                                          position : params.position,
                                          content_type : params.content_type,
                                          content_id : params.content_id
                                    });
                              break;

                              case 'content-in-new-section' :
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
                  //       before_section : $(this).data('sek-before-section'),
                  //       after_section : $(this).data('sek-after-section'),
                  //       content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                  //       content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
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
                  $wrapper.on( 'change', 'input[type="number"]', function(evt) {
                        var _type_ = $(this).closest('[data-sek-spacing]').data('sek-spacing'),
                            _newInputVal = $.extend( true, {}, _.isObject( input() ) ? input() : {} );
                        _newInputVal[ _type_ ] = $(this).val();
                        input( _newInputVal );
                  });
                  $wrapper.on( 'click', '.reset-spacing-wrap', function(evt) {
                        evt.preventDefault();
                        $wrapper.find('input[type="number"]').each( function() {
                              $(this).val(0);
                        });
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

                                    if ( _value == _model['font-family'] ) {
                                          _html_ += '<option selected="selected" value="' + _value + '">' + optionTitle + '</option>';
                                    } else {
                                          _html_ += '<option value="' + _value + '">' + optionTitle + '</option>';
                                    }
                              });
                              return _html_;
                        };

                        //add the first option
                        if ( _.isNull( _model['font-family'] ) || _.isEmpty( _model['font-family'] ) ) {
                              $fontSelectElement.append( '<option value="none" selected="selected">' + '@missi18n Select a font family' + '</option>' );
                        } else {
                              $fontSelectElement.append( '<option value="none">' + '@missi18n Select a font family' + '</option>' );
                        }


                        // generate the cfont and gfont html
                        _.each( [
                              {
                                    title : '@missi18n Web Safe Fonts',
                                    type : 'cfont',
                                    list : fontCollections.cfonts
                              },
                              {
                                    title : '@missi18n Google Fonts',
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
                              console.log('FONT COLLECTION ?', fontCollections );
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

                  self.sekEditorBound = false;//this allows us to bind the unique tinyMce instance only once



                  // SET THE SYNCHRONIZED INPUT
                  // CASE 1) When user has clicked on a tiny-mce editable content block
                  // CASE 2) when user click on the edit button in the module ui
                  // @see reactToPreviewMsg
                  // Each time a message is received from the preview, the corresponding action are executed
                  // and an event {msgId}_done is triggered on the current instance
                  // This is how we can listen here to 'sek-edit-module_done'
                  // The sek-edit-module is fired when clicking on a .sek-module wrapper @see ::scheduleUiClickReactions
                  self.bind( 'sek-edit-module_done', function( params ) {

                        //console.log( 'setupTinyMceEditor => sek-edit-module_done', params );
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

                        // $(this).text( ! api.sekEditorExpanded() ? '@missi18n Edit' : '@missi18n Close Editor' );
                  });


                  // CASE 2)
                  // when the synchronized input gets changed by the user
                  // 1) make sure the editor is expanded
                  // 2) refresh the editor content with the input() one
                  api.sekEditorSynchronizedInput.bind( function( to, from ) {
                        api.sekTinyMceEditor = api.sekTinyMceEditor || tinyMCE.get( 'czr-customize-content_editor' );

                        if ( false === self.sekEditorBound ) {
                              self.bindSekEditor();
                              self.sekEditorBound = true;
                              self.trigger('sek-tiny-mce-editor-bound-and-instantiated');
                        }

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
                        //api.consoleLog('in api.sekEditorExpanded', expanded, input() );
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
                        'sek-edit-module'
                  ], function( _evt_ ) {

                        if ( 'sek-edit-module' != _evt_ ) {
                              api.previewer.bind( _evt_, function() { api.sekEditorExpanded( false ); } );
                        } else {
                              api.previewer.bind( _evt_, function( params ) {
                                    api.sekEditorExpanded( params.clicked_input_type === 'tiny_mce_editor' );
                              });
                        }
                  });



            },//setupTinyMceEditor




            bindSekEditor : function() {
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
                  api.sekTinyMceEditor.on( 'input change keyup', function( evt ) {
                        // set the input value
                        if ( api.control.has( api.sekEditorSynchronizedInput().control_id ) ) {
                              try { api.control( api.sekEditorSynchronizedInput().control_id )
                                    .trigger( 'tinyMceEditorUpdated', { input_id : api.sekEditorSynchronizedInput().input_id } ); } catch( er ) {
                                    api.errare( 'Error when triggering tinyMceEditorUpdated', er );
                              }
                        }
                  });


                  // LISTEN TO USER DRAG ACTIONS => RESIZE EDITOR
                  self.$editorDragbar.on( 'mousedown mouseup', function( evt ) {
                        if ( ! api.sekEditorExpanded() )
                          return;
                        switch( evt.type ) {
                              case 'mousedown' :
                                    $( document ).on( 'mousemove.czr-customize-content_editor', function( event ) {
                                          event.preventDefault();
                                          $( document.body ).addClass( 'czr-customize-content_editor-pane-resize' );
                                          self.$editorFrame.css( 'pointer-events', 'none' );
                                          self.czrResizeEditor( event.pageY );
                                    });
                              break;

                              case 'mouseup' :
                                    $( document ).off( 'mousemove.czr-customize-content_editor' );
                                    $( document.body ).removeClass( 'czr-customize-content_editor-pane-resize' );
                                    self.$editorFrame.css( 'pointer-events', '' );
                              break;
                        }
                  });
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
      var LevelBackgroundModuleConstructor = {
            initialize: function( id, options ) {
                  //console.log('INITIALIZING SEKTION OPTIONS', id, options );
                  var module = this;
                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRLBBInputMths || {} );
                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );
            },//initialize

            CZRLBBInputMths : {
                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in lbb module');
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
            },//CZRLBBInputMths

            // CZRSocialsItem : { },//CZRSocialsItem
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
            sek_level_layout_bg_module : {
                  mthds : LevelBackgroundModuleConstructor,
                  crud : false,
                  name : 'Layout Background Border Options',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_layout_bg_module' )
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
                  name : 'Spacing',
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
                  name : 'Module Picker',
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
                input.container.find( '[draggable]').sekDrag({
                      // $(this) is the dragged element
                      onDragStart: function( event ) {
                            //console.log('ON DRAG START', $(this), $(this).data('sek-module-type'), event );
                            event.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                            event.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                            // event.originalEvent.dataTransfer.effectAllowed = "move";
                            // event.originalEvent.dataTransfer.dropEffect = "move";

                            api.previewer.send( 'sek-drag-start' );
                            $(event.currentTarget).addClass('sek-grabbing');
                      },
                      // onDragEnter : function( event ) {
                      //       event.originalEvent.dataTransfer.dropEffect = "move";
                      // },
                      onDragEnd: function( event ) {
                            //console.log('ON DRAG END', $(this), event );
                            api.previewer.send( 'sek-drag-stop' );
                            // make sure that the sek-grabbing class ( -webkit-grabbing ) gets reset on dragEnd
                            $(event.currentTarget).removeClass('sek-grabbing');
                      }
                }).attr('data-sek-drag', true );

                // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                input.container.find('[draggable]').each( function() {
                      $(this).on( 'mousedown mouseup', function( evt ) {
                            switch( evt.type ) {
                                  case 'mousedown' :
                                        $(this).addClass('sek-grabbing');
                                  break;
                                  case 'mouseup' :
                                        $(this).removeClass('sek-grabbing');
                                  break;
                            }
                      });
                });
                api.czr_sektions.trigger( 'sek-refresh-sekdrop', { type : 'module_picker' } );
                //console.log( this.id, input_options );
            }
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
            sek_section_picker_module : {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : 'Section Picker',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_section_picker_module' )
                  )
            },
      });

      api.czrInputMap = api.czrInputMap || {};
      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            section_picker : function( input_options ) {
                  var input = this;
                  input.container.find( '[draggable]').sekDrag({
                        // $(this) is the dragged element
                        onDragStart: function( event ) {
                              //console.log('ON DRAG START', $(this), $(this).data('sek-module-type'), event );
                              event.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                              event.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                              api.previewer.send( 'sek-drag-start' );
                              $(event.currentTarget).addClass('sek-grabbing');
                        },

                        onDragEnd: function( event ) {
                              //console.log('ON DRAG END', $(this), event );
                              api.previewer.send( 'sek-drag-stop' );
                              $(event.currentTarget).removeClass('sek-grabbing');
                        }
                  }).attr('data-sek-drag', true );

                   // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                  input.container.find('[draggable]').each( function() {
                        $(this).on( 'mousedown mouseup', function( evt ) {
                              switch( evt.type ) {
                                    case 'mousedown' :
                                          $(this).addClass('sek-grabbing');
                                    break;
                                    case 'mouseup' :
                                          $(this).removeClass('sek-grabbing');
                                    break;
                              }
                        });
                  });
                  api.czr_sektions.trigger( 'sek-refresh-sekdrop', { type : 'section_picker' } );
            }
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var ImageModuleConstructor = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    //run the parent initialize
                    api.CZRDynModule.prototype.initialize.call( module, id, options );

                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRImageInputMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );

                    //SET THE CONTENT PICKER DEFAULT OPTIONS
                    //@see ::setupContentPicker()
                    module.bind( 'set_default_content_picker_options', function( defaultContentPickerOption ) {
                          defaultContentPickerOption = { defaultOption : [ {
                                'title'      : '<span style="font-weight:bold">@missi18n Set a custom url</span>',
                                'type'       : '',
                                'type_label' : '',
                                'object'     : '',
                                'id'         : '_custom_',
                                'url'        : ''
                          }]};
                          return defaultContentPickerOption;
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

            // CZRSocialsItem : { },//CZRSocialsItem
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
                  name : 'Image',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_image_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
            var TinyMceEditorModuleConstructor = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    //run the parent initialize
                    api.CZRDynModule.prototype.initialize.call( module, id, options );

                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRTextEditorInputMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );
            },//initialize

            CZRTextEditorInputMths : {
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
                  name : 'Text Editor',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_tiny_mce_editor_module' )
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
            czr_simple_html_module : {
                  //mthds : SimpleHtmlModuleConstructor,
                  crud : false,
                  name : 'Simple Html',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_html_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var FeaturedPagesConstruct = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    //run the parent initialize
                    api.CZRDynModule.prototype.initialize.call( module, id, options );

                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRFPInputsMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );

                    //SET THE CONTENT PICKER DEFAULT OPTIONS
                    //@see ::setupContentPicker()
                    module.bind( 'set_default_content_picker_options', function( defaultContentPickerOption ) {
                          defaultContentPickerOption = { defaultOption : [ {
                                'title'      : '<span style="font-weight:bold">@missi18n Set a custom url</span>',
                                'type'       : '',
                                'type_label' : '',
                                'object'     : '',
                                'id'         : '_custom_',
                                'url'        : ''
                          }]};
                          return defaultContentPickerOption;
                    });
            },//initialize

            CZRFPInputsMths : {
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
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in featured pages module');
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
            },//CZRFPInputsMths

            // CZRSocialsItem : { },//CZRSocialsItem
      };//FeaturedPagesConstruct

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
            czr_featured_pages_module : {
                  mthds : FeaturedPagesConstruct,
                  crud : true,
                  name : 'Featured Pages',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_featured_pages_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );