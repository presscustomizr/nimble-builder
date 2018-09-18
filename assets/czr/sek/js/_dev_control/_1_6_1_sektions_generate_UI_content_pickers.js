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
                  // var _id_ = sektionsLocalizedData.optPrefixForSektionsNotSaved + ( 'module' === params.content_type ? '_sek_draggable_modules_ui' : '_sek_draggable_sections_ui' );

                  // Prepare the module map to register
                  var levelRegistrationParams = {};

                  $.extend( levelRegistrationParams, {
                        section_picker : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + '_sek_draggable_sections_ui',
                              module_type : 'sek_section_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Section Picker'],
                              expandAndFocusOnInit : true,
                              priority : 10
                              //icon : '<i class="material-icons sek-level-option-icon">center_focus_weak</i>'
                        },
                        module_picker : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + '_sek_draggable_modules_ui',
                              module_type : 'sek_module_picker_module',
                              controlLabel : sektionsLocalizedData.i18n['Module Picker'],
                              priority : 20
                              //icon : '<i class="material-icons sek-level-option-icon">gradient</i>'//'<i class="material-icons sek-level-option-icon">brush</i>'
                        }
                  });




                  _do_register_ = function() {
                        _.each( levelRegistrationParams, function( optionData, optionType ){
                              // Is the UI currently displayed the one that is being requested ?
                              // If so, visually remind the user that a module should be dragged
                              if ( self.isUIControlAlreadyRegistered( optionData.settingControlId ) ) {
                                    api.control( optionData.settingControlId ).focus({
                                          completeCallback : function() {
                                                var $container = api.control( optionData.settingControlId ).container;
                                                // @use button-see-mee css class declared in core in /wp-admin/css/customize-controls.css
                                                if ( $container.hasClass( 'button-see-me') )
                                                  return;
                                                $container.addClass('button-see-me');
                                                _.delay( function() {
                                                     $container.removeClass('button-see-me');
                                                }, 800 );
                                          }
                                    });
                                    return;
                              }//if


                              if ( ! api.has( optionData.settingControlId ) ) {
                                    // synchronize the module setting with the main collection setting
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( function( to, from ) {
                                                api.errare('MODULE / SECTION PICKER SETTING CHANGED');
                                          });
                                    });
                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
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
                                    id : optionData.settingControlId,
                                    label : optionData.controlLabel,
                                    type : 'czr_module',//sekData.controlType,
                                    module_type : optionData.module_type,
                                    section : '__content_picker__',
                                    priority : optionData.priority || 10,
                                    settings : { default : optionData.settingControlId },
                                    track : false//don't register in the self.registered() => this will prevent this container to be removed when cleaning the registered
                              }).done( function() {
                                    api.control( optionData.settingControlId, function( _control_ ) {
                                          // we set the focus to false when firing api.previewer.trigger( 'sek-pick-module', { focus : false }); in ::initialize()
                                          if ( true === params.focus ) {
                                                _control_.focus({
                                                    completeCallback : function() {}
                                                });
                                          }
                                          // Hide the item wrapper
                                          _control_.container.find('.czr-items-wrapper').hide();
                                          var $title = _control_.container.find('label > .customize-control-title');
                                          // if this level has a no icon, let's prepend it to the title
                                          if ( $title.find('.sek-level-option-icon').length < 1 ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( '<i class="fas fa-grip-vertical sek-level-option-icon"></i>' );
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
                        });//_.each
                  };//_do_register_



                  // Defer the registration when the parent section gets added to the api
                  // Note : the check on api.section.has( params.id ) is also performd on api.CZR_Helpers.register(), but here we use it to avoid setting up the click listeners more than once.
                  if ( ! api.section.has( '__content_picker__' ) ) {
                        api.section( '__content_picker__', function( _section_ ) {
                              // Schedule the accordion behaviour
                              self.scheduleModuleAccordion.call( _section_, { expand_first_module : false } );
                        });
                  }

                  // CONTENT PICKER SECTION
                  api.CZR_Helpers.register({
                        origin : 'nimble',
                        what : 'section',
                        id : '__content_picker__',
                        title: sektionsLocalizedData.i18n['Content Picker'],
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
                  }).done( function() {
                        api.section( '__content_picker__', function( _section_ ) {
                              _do_register_();

                              // Style the section title
                              var $sectionTitleEl = _section_.container.find('.accordion-section-title'),
                                  $panelTitleEl = _section_.container.find('.customize-section-title h3');

                              // The default title looks like this : Title <span class="screen-reader-text">Press return or enter to open this section</span>
                              if ( 0 < $sectionTitleEl.length && $sectionTitleEl.find('.sek-level-option-icon').length < 1 ) {
                                    $sectionTitleEl.prepend( '<i class="fas fa-grip-vertical sek-level-option-icon"></i>' );
                              }

                              // The default title looks like this : <span class="customize-action">Customizing</span> Title
                              if ( 0 < $panelTitleEl.length && $panelTitleEl.find('.sek-level-option-icon').length < 1 ) {
                                    $panelTitleEl.find('.customize-action').after( '<i class="fas fa-grip-vertical sek-level-option-icon"></i>' );
                              }
                        });
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );