
(function (api, $, _) {
      api.CZR_Helpers = api.CZR_Helpers || {};
      api.CZR_Helpers = $.extend( api.CZR_Helpers, {
            // @params {}. Example :
            // origin : 'nimble',
            // what : 'section',
            // id : params.id,
            // title: sektionsLocalizedData.i18n['Content for'] + ' ' + moduleName,
            // panel : sektionsLocalizedData.sektionsPanelId,
            // priority : 1000,
            // track : false//don't register in the self.registered()
            // constructWith : MainSectionConstructor,
            register : function( params ) {
                  if ( ! _.has( params, 'id' ) ) {
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
                                    break;
                              }
                              defaults = $.extend( true, {}, api.Setting.prototype.defaults );
                              var settingArgs = _.extend(
                                  defaults ,
                                    {
                                          dirty : ! _.isUndefined( params.dirty ) ? params.dirty : false,
                                          value : _.isUndefined( params.value ) ? null : params.value,
                                          transport : params.transport || 'refresh',
                                          type : params.type || 'option'
                                    }
                              );
                              // assign the value sent from the server

                              var SettingConstructor = api.settingConstructor[ settingArgs.type ] || api.Setting;

                              // extend with specific additional options provided on registration
                              if ( _.isObject( params.options ) ) {
                                    settingArgs  = _.extend( settingArgs , params.options );
                              }

                              try { api.add( new SettingConstructor( params.id, settingArgs.value, settingArgs ) ); } catch ( er ) {
                                    api.errare( 'api.CZR_Helpers::register => problem when adding a setting to the api', er );
                              }
                        break;


                        case 'panel' :
                              // Check if we have a correct section
                              if ( ! _.has( params, 'id' ) ){
                                    throw new Error( 'registerPanel => missing panel id ');
                              }

                              if ( api.panel.has( params.id ) ) {
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

                              // extend with specific additional options provided on registration
                              if ( _.isObject( params.options ) ) {
                                    panelParams  = _.extend( panelParams , params.options );
                              }
                              panelParams = _.extend( { params: panelParams }, panelParams ); // Inclusion of params alias is for back-compat for custom panels that expect to augment this property.

                              try { __element__ = api.panel.add( new PanelConstructor( params.id, panelParams ) ); } catch ( er ) {
                                    api.errare( 'api.CZR_Helpers::register => problem when adding a panel to the api', er );
                              }
                        break;


                        case 'section' :
                              // MAYBE REGISTER THE SECTION
                              // Check if we have a correct section
                              if ( ! _.has( params, 'id' ) ){
                                    throw new Error( 'registerSection => missing section id ');
                              }

                              if ( api.section.has( params.id ) ) {
                                    //api.infoLog( 'registerSection => ' + params.id + ' is already registered');
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
                                        customizeAction: serverControlParams.i18n['Customizing']
                                  }
                              );

                              var SectionConstructor = api.Section;
                              if ( ! _.isUndefined( params.constructWith ) ) {
                                    SectionConstructor = params.constructWith;
                              } else if ( ! _.isEmpty( params.type ) && api.sectionConstructor[ params.type ] ) {
                                    SectionConstructor = api.sectionConstructor[ params.type ];
                              }

                              // extend with specific additional options provided on registration
                              if ( _.isObject( params.options ) ) {
                                    sectionParams  = _.extend( sectionParams , params.options );
                              }

                              sectionParams = _.extend( { params: sectionParams }, sectionParams ); // Inclusion of params alias is for back-compat for custom panels that expect to augment this property.
                              try { __element__ = api.section.add( new SectionConstructor( params.id, sectionParams ) ); } catch ( er ) {
                                    api.errare( 'api.CZR_Helpers::register => problem when adding a section to the api', er );
                              }
                        break;


                        case 'control' :
                              if ( api.control.has( params.id ) ) {
                                    //api.errorLog( 'registerControl => ' + params.id + ' is already registered');
                                    break;
                              }

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

                              // extend with specific additional options provided on registration
                              if ( _.isObject( params.options ) ) {
                                    controlArgs = _.extend( controlArgs, params.options );
                              }

                              options = _.extend( { params: controlArgs }, controlArgs ); // Inclusion of params alias is for back-compat for custom controls that expect to augment this property.

                              try { __element__ = api.control.add( new ControlConstructor( params.id, options ) ); } catch ( er ) {
                                    api.errare( 'api.CZR_Helpers::register => problem when adding a control to the api', er );
                              }
                        break;
                        default :
                              api.errorLog('invalid "what" when invoking the register() method');
                        break;

                  }//switch
                  __element__ = ! _.isEmpty( __element__ ) ?  __element__ : { deferred : { embedded : $.Deferred( function() { this.resolve(); }) } };

                  // this is where we populate the registered collection
                  // if the registered element is "tracked", we inform the api about its registration
                  // @see Nimble or Contextualizer for tracking usage => ui re-rendering, etc...
                  if ( false !== params.track ) {
                        api.trigger( 'czr-new-registered', params );
                  }

                  return 'setting' == params.what ? params : __element__.deferred.embedded;
            }
      });//$.extend
  // $( window ).on( 'message', function( e, o) {
  //   api.consoleLog('WHAT ARE WE LISTENING TO?', e, o );
  // });
})( wp.customize , jQuery, _);