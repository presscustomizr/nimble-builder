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
