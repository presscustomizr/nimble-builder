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
                  var registrationParams = {};


                  $.extend( registrationParams, {
                        // The content type switcher has a priority lower than the other so it's printed on top
                        // it's loaded last, because it needs to know the existence of all other
                        sek_content_type_switcher_module : {
                              settingControlId : sektionsLocalizedData.prefixForSettingsNotSaved + '_sek_content_type_switcher_ui',
                              module_type : 'sek_content_type_switcher_module',
                              controlLabel :  self.getRegisteredModuleProperty( 'sek_content_type_switcher_module', 'name' ),//sektionsLocalizedData.i18n['Select a content type'],
                              priority : 10,
                              settingValue : { content_type : params.content_type }
                              //icon : '<i class="material-icons sek-level-option-icon">center_focus_weak</i>'
                        },
                        sek_module_picker_module : {
                              settingControlId : sektionsLocalizedData.prefixForSettingsNotSaved + '_sek_draggable_modules_ui',
                              module_type : 'sek_module_picker_module',
                              controlLabel : self.getRegisteredModuleProperty( 'sek_module_picker_module', 'name' ),//sektionsLocalizedData.i18n['Pick a module'],
                              content_type : 'module',
                              priority : 20,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>'
                        },

                        // June 2020 for : https://github.com/presscustomizr/nimble-builder/issues/520
                        // and https://github.com/presscustomizr/nimble-builder/issues/713
                        sek_my_sections_sec_picker_module : {
                              settingControlId : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : 'sek_my_sections_sec_picker_module',
                              controlLabel :  self.getRegisteredModuleProperty( 'sek_my_sections_sec_picker_module', 'name' ),//sektionsLocalizedData.i18n['My sections'],
                              content_type : 'section',
                              expandAndFocusOnInit : false,
                              priority : 10,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>',
                              is_new : false
                        },
                  });//



                  // Register prebuild sections modules
                  // declared server side in inc/sektions/_front_dev_php/_constants_and_helper_functions/0_5_2_sektions_local_sektion_data.php
                  // to understand how those sections are registered server side, see @see https://github.com/presscustomizr/nimble-builder/issues/713
                  _.each([
                        'sek_intro_sec_picker_module',
                        'sek_features_sec_picker_module',
                        'sek_post_grids_sec_picker_module',
                        'sek_about_sec_picker_module',
                        'sek_contact_sec_picker_module',
                        'sek_team_sec_picker_module',
                        'sek_column_layouts_sec_picker_module',
                        'sek_header_sec_picker_module',
                        'sek_footer_sec_picker_module'
                  ], function( mod_type, key ) {
                        registrationParams[mod_type] = {
                              settingControlId : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid() + '_sek_draggable_sections_ui',
                              module_type : mod_type,
                              controlLabel :  self.getRegisteredModuleProperty( mod_type, 'name' ),
                              content_type : 'section',
                              expandAndFocusOnInit : 0 === key,//<= first section group is expanded on start
                              priority : 30,
                              icon : '<i class="fas fa-grip-vertical sek-level-option-icon"></i>',
                              is_new : 'sek_post_grids_sec_picker_module' === mod_type
                        };
                  });


                  //$.extend( registrationParams, { });

                  // Beta features to merge here ?
                  // if ( sektionsLocalizedData.areBetaFeaturesEnabled ) {
                  //       $.extend( registrationParams, {});
                  // }


                  // BAIL WITH A SEE-ME ANIMATION IF THIS UI IS CURRENTLY BEING DISPLAYED
                  // Is the UI currently displayed the one that is being requested ?
                  // If so :
                  // 1) visually remind the user that a module should be dragged
                  // 2) pass the content_type param to display the requested content_type
                  var firstKey = _.keys( registrationParams )[0],
                      firstControlId = registrationParams[firstKey].settingControlId;

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
                        });

                        return dfd;
                  }//if


                  // @return void()
                  _do_register_ = function() {
                        _.each( registrationParams, function( optionData, optionType ){
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
                                    section : self.SECTION_ID_FOR_CONTENT_PICKER,
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

                                          var $title = _control_.container.find('label > .customize-control-title'),
                                              _titleContent = $title.html();

                                          // We wrap the original text content in this span.sek-ctrl-accordion-title in order to style it (underlined) independently ( without styling the icons next to it )
                                          $title.html( ['<span class="sek-ctrl-accordion-title">', _titleContent, '</span>' ].join('') );

                                          if ( optionData.is_new ) {
                                                var _titleHtml = $title.html();
                                                $title.html( _titleHtml + ' <span class="sek-new-label">New!</span>' );
                                          }
                                          // if this level has an icon, let's prepend it to the title
                                          if ( ! _.isUndefined( optionData.icon ) ) {
                                                $title.addClass('sek-flex-vertical-center').prepend( optionData.icon );
                                          }

                                          // ACCORDION
                                          // Setup the accordion only for section content type
                                          if ( 'section' === _control_.content_type ) {
                                                // Hide the item wrapper
                                                // @see css
                                                _control_.container.attr('data-sek-expanded', "false" );
                                                // prepend the animated arrow
                                                $title.prepend('<span class="sek-animated-arrow" data-name="icon-chevron-down"><span class="fa fa-chevron-down"></span></span>');
                                                // setup the initial state + initial click
                                                _control_.container.attr('data-sek-expanded', "false" );
                                                if ( true === optionData.expandAndFocusOnInit && "false" == _control_.container.attr('data-sek-expanded' ) ) {
                                                      //_control_.container.find('.czr-items-wrapper').show();
                                                      //$title.trigger('click');
                                                      _control_.container.addClass('sek-expand-on-init');
                                                }
                                          } else {
                                                _control_.container.attr('data-sek-accordion', 'no');
                                          }

                                    });
                              });
                        });//_.each

                        api.trigger('nimble-modules-and-sections-controls-registered');
                  };//_do_register_


                  // the self.SECTION_ID_FOR_CONTENT_PICKER section is registered on initialize
                  // @fixes https://github.com/presscustomizr/nimble-builder/issues/187
                  api.section( self.SECTION_ID_FOR_CONTENT_PICKER, function( _section_ ) {
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
                        self.scheduleModuleAccordion.call( _section_, { expand_first_control : false } );
                        _section_.container.find('.customize-control.sek-expand-on-init').find('label > .customize-control-title').trigger('click');
                        // Fetch the presetSectionCollection from the server now, so we save a few milliseconds when injecting the first preset_section
                        // it populates api.nimble_ApiSections
                        //
                        // updated in v1.7.5, may 21st : performance improvements on customizer load
                        // inserting preset sections is not on all Nimble sessions => let's only fetch when user inserts the first section
                        // self._getApiSingleSectionData();
                  });
                  return dfd;
            }
      });//$.extend()
})( wp.customize, jQuery );