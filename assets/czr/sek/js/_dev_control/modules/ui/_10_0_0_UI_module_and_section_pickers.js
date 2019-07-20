//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
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
            sek_content_type_switcher_module : {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_content_type_switcher_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_content_type_switcher_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );





/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
//global sektionsLocalizedData, serverControlParams
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
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_module_picker_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel :  _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_module_picker_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );



/* ------------------------------------------------------------------------- *
 *  SECTION PICKER MODULES
/* ------------------------------------------------------------------------- */
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
      // var section_modules = [
      //       'sek_intro_sec_picker_module',
      //       'sek_features_sec_picker_module',
      //       'sek_contact_sec_picker_module',
      //       'sek_column_layouts_sec_picker_module',
      //       'sek_header_sec_picker_module',
      //       'sek_footer_sec_picker_module'
      // ];

      var section_modules = sektionsLocalizedData.presetSectionsModules;
      if ( ! _.isArray( section_modules ) || _.isEmpty( section_modules ) ) {
            api.errare( 'api.czrModuleMap => error when adding section modules');
            return;
      }

      _.each( section_modules, function( module_type ) {
            api.czrModuleMap[ module_type ] = {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( module_type, 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( module_type )
                  )
            };
      });
})( wp.customize , jQuery, _ );






/* ------------------------------------------------------------------------- *
 *  MY SECTIONS MODULE
/* ------------------------------------------------------------------------- */
//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;

                  // EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend({
                        initialize : function( name, options ) {
                              var input = this;
                              api.CZRInput.prototype.initialize.call( input, name, options );
                              input.isReady.then( function() {
                                    input.renderUserSavedSections();
                                    api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'preset_section', input_container : input.container } );
                              });
                        },


                        renderUserSavedSections : function() {
                              var input = this,
                                  html = '',
                                  $wrapper = input.container.find('.sek-content-type-wrapper'),
                                  creation_date = '',
                                  // https://stackoverflow.com/questions/3552461/how-to-format-a-javascript-date
                                  formatDate = function(date) {
                                      var monthNames = [
                                          "January", "February", "March",
                                          "April", "May", "June", "July",
                                          "August", "September", "October",
                                          "November", "December"
                                      ];

                                      var day = date.getDate(),
                                          monthIndex = date.getMonth(),
                                          year = date.getFullYear(),
                                          hours = date.getHours(),
                                          minutes = date.getMinutes(),
                                          seconds = date.getSeconds();

                                      return [
                                            day,
                                            monthNames[monthIndex],
                                            year
                                            //[hours,minutes,seconds].join(':')
                                      ].join(' ');
                                  };

                              _.each( sektionsLocalizedData.userSavedSektions, function( secData, secKey ) {
                                    try { creation_date = formatDate( new Date( secData.creation_date.replace( /-/g, '/' ) ) ); } catch( er ) {
                                          api.errare( '::renderUserSavedSections => formatDate => error', er );
                                    }
                                    html = [
                                          '<div class="sek-user-section-wrapper">',
                                            '<div class="sek-saved-section-title"><i class="sek-remove-user-section far fa-trash-alt"></i>' + secData.title + '</div>',
                                            '<div draggable="true" data-sek-is-user-section="true" data-sek-section-type="' + secData.type +'" data-sek-content-type="preset_section" data-sek-content-id="' + secKey +'" style="" title="' + secData.title + '">',
                                              '<div class="sek-overlay"></div>',
                                              '<div class="sek-saved-section-description">' + secData.description + '</div>',
                                              ! _.isEmpty( creation_date ) ? ( '<div class="sek-saved-section-date"><i class="far fa-calendar-alt"></i> @missi18n Created : ' + creation_date + '</div>' ) : '',
                                            '</div>',
                                          '</div>'
                                    ].join('');
                                    $wrapper.append( html );
                              });
                        }
                  });

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

                  // module.embedded.then( function() {
                  //       console.log('MODULE READY=> lets dance',  module.container,  module.container.find('.sek-content-type-wrapper') );
                  // });
            },//initialize
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
      if ( sektionsLocalizedData.isSavedSectionEnabled ) {
            $.extend( api.czrModuleMap, {
                  sek_my_sections_sec_picker_module : {
                        mthds : Constructor,
                        crud : false,
                        name : api.czr_sektions.getRegisteredModuleProperty( 'sek_my_sections_sec_picker_module', 'name' ),
                        has_mod_opt : false,
                        ready_on_section_expanded : false,
                        ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                        defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_my_sections_sec_picker_module' )
                  },
            });
      }
})( wp.customize , jQuery, _ );
