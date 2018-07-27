//global sektionsLocalizedData
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
            // @dfd = $.Deferred()
            // @return the state promise dfd
            generateUIforLocalSkopeOptions : function( params, dfd ) {
                  var self = this;
                  var _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + '__localSkopeOptions';
                  // Is the UI currently displayed the one that is being requested ?
                  // If so, visually remind the user that a module should be dragged
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        // api.control( _id_ ).focus({
                        //       completeCallback : function() {
                        //             var $container = api.control( _id_ ).container;
                        //             // @use button-see-mee css class declared in core in /wp-admin/css/customize-controls.css
                        //             if ( $container.hasClass( 'button-see-me') )
                        //               return;
                        //             $container.addClass('button-see-me');
                        //             _.delay( function() {
                        //                  $container.removeClass('button-see-me');
                        //             }, 800 );
                        //       }
                        // });
                        return dfd;
                  }

                  _do_register_ = function() {
                        if ( ! api.has( _id_ ) ) {
                              // Schedule the binding to synchronize the options with the main collection setting
                              // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                              // They need to be kept in order to keep track of the changes in the customizer.
                              // => that's why we check if ! api.has( ... )
                              api( _id_, function( _setting_ ) {
                                    _setting_.bind( _.debounce( function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                defaultPreviewAction : 'refresh',
                                                uiParams : params,
                                                options_type : 'general',
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( 'Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    }, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                              });//api( Id, function( _setting_ ) {})

                              // Let's add the starting values if provided when registrating the module
                              var startingModuleValue = self.getModuleStartingValue( 'sek_local_skope_options_module' ),
                                  currentSetValue = api( self.sekCollectionSettingId() )(),
                                  allSkopeOptions = $.extend( true, {}, _.isObject( currentSetValue.options ) ? currentSetValue.options : {} ),
                                  generalOptions = _.isObject( allSkopeOptions.general ) ? allSkopeOptions.general : {};

                              // this options are saved under 'options.general'
                              if ( 'no_starting_value' !== startingModuleValue ) {
                                    initialModuleValues = $.extend( startingModuleValue, generalOptions );
                              }

                              api.CZR_Helpers.register( {
                                    origin : 'nimble',
                                    level : params.level,
                                    what : 'setting',
                                    id : _id_,
                                    dirty : false,
                                    value : initialModuleValues,
                                    transport : 'postMessage',//'refresh',//// ,
                                    type : '_nimble_ui_'//will be dynamically registered but not saved in db as option// columnData.settingType
                              });
                        }

                        api.CZR_Helpers.register( {
                              origin : 'nimble',
                              level : params.level,
                              what : 'control',
                              id : _id_,
                              label : sektionsLocalizedData.i18n['General options'],
                              type : 'czr_module',//sekData.controlType,
                              module_type : 'sek_local_skope_options_module',
                              section : _id_,
                              priority : 10,
                              settings : { default : _id_ },
                              track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        }).done( function() {
                              // api.control( _id_ ).focus({
                              //     completeCallback : function() {}
                              // });
                        });
                  };

                  // Defer the registration when the parent section gets added to the api
                  api.section( _id_, function() {
                        api( self.sekCollectionSettingId(), function() {
                              _do_register_();
                        });
                  });

                  // MODULE / SECTION PICKER SECTION
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : _id_,
                        title: sektionsLocalizedData.i18n['Local options for the sections of the current page'],
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
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );