//global sektionsLocalizedData
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // @params = {
            //    action : 'sek-generate-site-tmpl-options-ui'
            //    level : params.level,
            //    id : params.id,
            //    in_sektion : params.in_sektion,
            //    in_column : params.in_column,
            //    options : params.options || []
            // }
            // @dfd = $.Deferred()
            // @return the state promise dfd
            generateUIforSiteTmplOptions : function( params, dfd ) {
                  var self = this,
                        // only the site_template option will be saved ( see php constant NIMBLE_OPT_NAME_FOR_SITE_TMPL_OPTIONS ) not the setting used to populate it
                      _id_ = sektionsLocalizedData.prefixForSettingsNotSaved + sektionsLocalizedData.optNameForSiteTmplOptions;//__nimble__ + nimble_site_templates

                  // Is the UI currently displayed the one that is being requested ?
                  // If so, visually remind the user that a module should be dragged
                  if ( self.isUIControlAlreadyRegistered( _id_ ) ) {
                        return dfd;
                  }

                  // Prepare the module map to register
                  var registrationParams = {};
                  if ( _.isUndefined( sektionsLocalizedData.siteTmplOptionsMap ) || ! _.isObject( sektionsLocalizedData.siteTmplOptionsMap ) ) {
                        api.errare( '::generateUIforSiteTmplOptions => missing or invalid siteTmplOptionsMap');
                        return dfd;
                  }

                  // Populate the registration params
                  _.each( sektionsLocalizedData.siteTmplOptionsMap, function( mod_type, opt_name ) {
                        switch( opt_name ) {
                              // Header and footer have been beta tested during 5 months and released in June 2019, in version 1.8.0
                              case 'site_templates' :
                                    registrationParams[ opt_name ] = {
                                          settingControlId : _id_ + '__site_templates',
                                          module_type : mod_type,
                                          controlLabel : sektionsLocalizedData.i18n['Site templates'],
                                          icon : ''//'<i class="material-icons sek-level-option-icon">text_format</i>'
                                    };
                              break;
                              default :
                                    api.errare('::generateUIforSiteTmplOptions => an option group could not be registered => ' + mod_type, opt_name );
                              break;
                        }//switch
                  });//_.each

                  // Let assign the global options to a var
                  var siteTmplOptionDBValues = sektionsLocalizedData.siteTmplOptionDBValues;

                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    var doUpdate = function( to, from, args ) {
                                          try { self.updateAPISettingAndExecutePreviewActions({
                                                isSiteTemplateOptions : true,//<= indicates that we won't update the local skope setting id
                                                defaultPreviewAction : 'refresh_preview',
                                                uiParams : params,
                                                options_type : optionType,
                                                settingParams : {
                                                      to : to,
                                                      from : from,
                                                      args : args
                                                }
                                          }); } catch( er ) {
                                                api.errare( '::generateUIforSiteTmplOptions => Error in updateAPISettingAndExecutePreviewActions', er );
                                          }
                                    };

                                    // Schedule the binding to synchronize the options with the main collection setting
                                    // Note 1 : unlike control or sections, the setting are not getting cleaned up on each ui generation.
                                    // They need to be kept in order to keep track of the changes in the customizer.
                                    // => that's why we check if ! api.has( ... )
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( _.debounce( doUpdate, self.SETTING_UPDATE_BUFFER ) );//_setting_.bind( _.debounce( function( to, from, args ) {}
                                    });//api( Id, function( _setting_ ) {})

                                    // Let's add the starting values if provided when registrating the module
                                    var startingModuleValue = self.getModuleStartingValue( optionData.module_type ),
                                        initialModuleValues = ( _.isObject( siteTmplOptionDBValues ) && ! _.isEmpty( siteTmplOptionDBValues[ optionType ] ) ) ? siteTmplOptionDBValues[ optionType ] : {};

                                    if ( 'no_starting_value' !== startingModuleValue && _.isObject( startingModuleValue ) ) {
                                          // make sure the starting values are deeped clone now, before being extended
                                          var clonedStartingModuleValue = $.extend( true, {}, startingModuleValue );
                                          initialModuleValues = $.extend( clonedStartingModuleValue, initialModuleValues );
                                    }

                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
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
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : self.SECTION_ID_FOR_SITE_TMPL,//registered in ::initialize()
                                    priority : 20,
                                    settings : { default : optionData.settingControlId },
                                    track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                              }).done( function() {
                                    // if ( true === optionData.expandAndFocusOnInit ) {
                                    //       api.control( optionData.settingControlId ).focus({
                                    //             completeCallback : function() {}
                                    //       });
                                    // }

                                    // Implement the animated arrow markup, and the initial state of the module visibility
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          // Hide the item wrapper
                                          // @see css
                                          _control_.container.attr('data-sek-expanded', "false" );
                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();
                                          // We wrap the original text content in this span.sek-ctrl-accordion-title in order to style it (underlined) independently ( without styling the icons next to it )
                                          $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );

                                          // if this level has an icon, let's prepend it to the title
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }
                                          // prepend the animated arrow
                                          $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                          // setup the initial state + initial click
                                          _control_.container.attr('data-sek-expanded', "false" );
                                          if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                $title.trigger('click');
                                          }
                                    });
                              });
                        });//_.each();
                  };//do register

                  // The parent section has already been added in ::initialize()
                  _do_register_();

                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );