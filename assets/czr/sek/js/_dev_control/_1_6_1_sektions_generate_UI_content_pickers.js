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
                  // Prepare the module map to register
                  var modulesRegistrationParams = {};

                  $.extend( modulesRegistrationParams, {
                        content_type_switcher : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + '_sek_content_type_switcher_ui',
                              module_type : 'sek_content_type_switcher_module',
                              controlLabel :  sektionsLocalizedData.i18n['Select a content type'],
                              priority : 0,
                              settingValue : { content_type : params.content_type }
                              //icon : '<i class="material-icons sek-level-option-icon">center_focus_weak</i>'
                        },
                        module_picker : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + '_sek_draggable_modules_ui',
                              module_type : 'sek_module_picker_module',
                              controlLabel : sektionsLocalizedData.i18n['Pick a module'],
                              content_type : 'module',
                              priority : 20,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },

                        sek_intro_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_intro_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Sections for an introduction'],
                              content_type : 'section',
                              expandAndFocusOnInit : true,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },
                        sek_features_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_features_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Sections for services and features'],
                              content_type : 'section',
                              expandAndFocusOnInit : false,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },
                        sek_column_layouts_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.optPrefixForSektionsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_column_layouts_sec_picker_module',
                              controlLabel :  sektionsLocalizedData.i18n['Empty sections with columns layout'],
                              content_type : 'section',
                              expandAndFocusOnInit : false,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },
                  });


                  // BAIL WITH A SEE-ME ANIMATION IF THIS UI IS CURRENTLY BEING DISPLAYED
                  // Is the UI currently displayed the one that is being requested ?
                  // If so :
                  // 1) visually remind the user that a module should be dragged
                  // 2) pass the content_type param to display the requested content_type
                  var firstKey = _.keys( modulesRegistrationParams )[0],
                      firstControlId = modulesRegistrationParams[firstKey].settingControlId;

                  if ( self.isUIControlAlreadyRegistered( firstControlId ) ) {
                        api.control( firstControlId, function( _control_ ) {
                              _control_.focus({
                                    completeCallback : function() {
                                          var $container = _control_.container;
                                          // @use button-see-mee css class declared in core in /wp-admin/css/customize-controls.css
                                          if ( $container.hasClass( 'button-see-me') )
                                            return;
                                          $container.addClass('button-see-me');
                                          _.delay( function() {
                                               $container.removeClass('button-see-me');
                                          }, 800 );
                                    }
                              });
                              // this event will be listened to from the "content_type_switcher" input()
                              // @see content_type_switcher method in api.czrInputMap
                              api.section( _control_.section() ).container.first().trigger('sek-content-type-refreshed', { content_type : params.content_type } );
                        });
                        return dfd;
                  }//if


                  // @return void()
                  _do_register_ = function() {
                        _.each( modulesRegistrationParams, function( optionData, optionType ){
                              if ( ! api.has( optionData.settingControlId ) ) {
                                    // synchronize the module setting with the main collection setting
                                    api( optionData.settingControlId, function( _setting_ ) {
                                          _setting_.bind( function( to, from ) {
                                                api.errare('generateUIforDraggableContent => the setting() should not changed');
                                          });
                                    });
                                    api.CZR_Helpers.register( {
                                          origin : 'nimble',
                                          level : params.level,
                                          what : 'setting',
                                          id : optionData.settingControlId,
                                          dirty : false,
                                          value : optionData.settingValue || {},
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
                                          // set the control type property
                                          _control_.content_type = optionData.content_type;//<= used to handle visibility when switching content type with the "content_type_switcher" control

                                          // we set the focus to false when firing api.previewer.trigger( 'sek-pick-content', { focus : false }); in ::initialize()
                                          if ( true === params.focus ) {
                                                _control_.focus({
                                                      completeCallback : function() {}
                                                });
                                          }

                                          var $title = _control_.container.find('label > .customize-control-title');
                                          // if this level has an icon, let's prepend it to the title
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }

                                          // ACCORDION
                                          // Setup the accordion only for section content type
                                          if ( 'section' === _control_.content_type ) {
                                                // Hide the item wrapper
                                                _control_.container.find('.czr-items-wrapper').hide();
                                                // prepend the animated arrow
                                                $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                                // setup the initial state + initial click
                                                _control_.container.attr('data-sek-expanded', "false" );
                                                if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                      _control_.container.find('.czr-items-wrapper').show();
                                                      $title.trigger('click');
                                                }
                                          } else {
                                                _control_.container.attr('data-sek-accordion', 'no');
                                          }

                                    });
                              });
                        });//_.each
                  };//_do_register_


                  // the '__content_picker__' section is registered on initialize
                  // @fixes https://github.com/presscustomizr/nimble-builder/issues/187
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

                        // Schedule the accordion behaviour
                        self.scheduleModuleAccordion.call( _section_, { expand_first_module : true } );

                        // Fetch the presetSectionCollection from the server now, so we save a few milliseconds when injecting the first preset_section
                        // it populates api.sek_presetSections
                        self._maybeFetchSectionsFromServer();
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );