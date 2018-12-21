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

      api.czrInputMap = api.czrInputMap || {};
      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            content_type_switcher : function( input_options ) {
                  var input = this,
                      _section_,
                      initial_content_type;

                  if ( ! api.section.has( input.module.control.section() ) ) {
                        throw new Error( 'api.czrInputMap.content_type_switcher => section not registered' );
                  }
                  _section_ = api.section( input.module.control.section() );

                  // attach click event on data-sek-content-type buttons
                  input.container.on('click', '[data-sek-content-type]', function( evt ) {
                        evt.preventDefault();
                        // handle the is-selected css class toggling
                        input.container.find('[data-sek-content-type]').removeClass('is-selected').attr( 'aria-pressed', false );
                        $(this).addClass('is-selected').attr( 'aria-pressed', true );
                        api.czr_sektions.currentContentPickerType( $(this).data( 'sek-content-type') );
                  });


                  var _do_ = function( contentType ) {
                        input.container.find( '[data-sek-content-type="' + ( contentType || 'module' ) + '"]').trigger('click');
                        _.each( _section_.controls(), function( _control_ ) {
                              if ( ! _.isUndefined( _control_.content_type ) ) {
                                    _control_.active( contentType === _control_.content_type );
                              }
                        });
                  };

                  // Initialize
                  // Fixes issue https://github.com/presscustomizr/nimble-builder/issues/248
                  api.czr_sektions.currentContentPickerType = api.czr_sektions.currentContentPickerType || new api.Value( input() );
                  _do_( api.czr_sektions.currentContentPickerType() );

                  // Schedule a reaction to changes
                  api.czr_sektions.currentContentPickerType.bind( function( contentType ) {
                        _do_( contentType );
                  });
            }
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

      api.czrInputMap = api.czrInputMap || {};

      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            module_picker : function( input_options ) {
                var input = this;
                // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                // input.container.find('[draggable]').each( function() {
                //       $(this).on( 'mousedown mouseup', function( evt ) {
                //             switch( evt.type ) {
                //                   case 'mousedown' :
                //                         //$(this).addClass('sek-grabbing');
                //                   break;
                //                   case 'mouseup' :
                //                         //$(this).removeClass('sek-grabbing');
                //                   break;
                //             }
                //       });
                // });
                api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'module', input_container : input.container } );
                //console.log( this.id, input_options );
            }
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
      var section_modules = [
            'sek_intro_sec_picker_module',
            'sek_features_sec_picker_module',
            'sek_contact_sec_picker_module',
            'sek_column_layouts_sec_picker_module'
      ];
      // Header and footer have been introduced in v1.4.0 but not enabled by default
      // The header and footer preset sections are on hold until "header and footer" feature is released.
      if ( sektionsLocalizedData.isNimbleHeaderFooterEnabled ) {
            $.extend( section_modules, [ 'sek_header_sec_picker_module','sek_footer_sec_picker_module' ] );
      }
      _.each( section_modules, function( module_type ) {
            api.czrModuleMap[ module_type ] = {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( module_type, 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
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
                        ready_on_section_expanded : true,
                        defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_my_sections_sec_picker_module' )
                  },
            });
      }
})( wp.customize , jQuery, _ );







/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      api.czrInputMap = api.czrInputMap || {};
      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            section_picker : function( input_options ) {
                  var input = this;
                  // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                  // input.container.find('[draggable]').each( function() {
                  //       $(this).on( 'mousedown mouseup', function( evt ) {
                  //             switch( evt.type ) {
                  //                   case 'mousedown' :
                  //                         //$(this).addClass('sek-grabbing');
                  //                   break;
                  //                   case 'mouseup' :
                  //                         //$(this).removeClass('sek-grabbing');
                  //                   break;
                  //             }
                  //       });
                  // });
                  api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'preset_section', input_container : input.container } );
            }
      });
})( wp.customize , jQuery, _ );