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
            generateUIforDraggableContent : function( params, dfd ) {
                  var self = this;
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
                        return dfd;
                  }


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
                                    value : {},
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
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );