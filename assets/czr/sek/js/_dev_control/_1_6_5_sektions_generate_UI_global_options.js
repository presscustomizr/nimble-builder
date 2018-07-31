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
            generateUIforGlobalOptions : function( params, dfd ) {
                  var self = this,
                      _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + sektionsLocalizedData.optNameForGlobalOptions,
                      option_type = 'general';
                  // Is the UI currently displayed the one that is being requested ?
                  // If so, visually remind the user that a module should be dragged
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
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
                                                isGlobalOptions : true,//<= indicates that we won't update the local skope setting id
                                                defaultPreviewAction : 'refresh',
                                                uiParams : params,
                                                options_type : option_type,
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
                              var dbValues = sektionsLocalizedData.globalOptionDBValues,
                                  startingModuleValue = self.getModuleStartingValue( 'sek_global_options_module' ),
                                  initialModuleValues = ( _.isObject( dbValues ) && ! _.isEmpty( dbValues[ option_type ] ) ) ? dbValues[ option_type ] : {};

                              if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                    // make sure the starting values are deeped clone now, before being extended
                                    var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                    initialModuleValues = $.extend( clonedStartingModuleValue, initialModuleValues );
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
                              label : sektionsLocalizedData.i18n['General options applied site wide'],
                              type : 'czr_module',//sekData.controlType,
                              module_type : 'sek_global_options_module',
                              section : '__globalAndLocalOptionsSection',//registered in ::initialize()
                              priority : 20,
                              settings : { default : _id_ },
                              track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                        }).done( function() {
                              // Implement the animated arrow markup, and the initial state of the module visibility
                              api.control( _id_, function( _control_ ) {
                                    // Hide the item wrapper
                                    _control_.container.find('.czr-items-wrapper').hide();
                                    var $title = _control_.container.find('label > .customize-control-title');
                                    // if this level has an icon, let's prepend it to the title
                                     $title.addClass('sek-flex-vertical-center').prepend( '<i class="fas fa-globe sek-level-option-icon"></i>' );
                                    // prepend the animated arrow
                                    $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                    // setup the initial state + initial click
                                    _control_.container.attr('data-sek-expanded', "false" );
                                    // if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                    //       $title.trigger('click');
                                    // }
                              });
                        });
                  };

                  // Defer the registration when the parent section gets added to the api
                  // the section '__globalAndLocalOptionsSection' is registered in ::initialize()
                  api.section( '__globalAndLocalOptionsSection', function( _section_ ) {
                        api( self.sekCollectionSettingId(), function() {
                              _do_register_();
                        });
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );