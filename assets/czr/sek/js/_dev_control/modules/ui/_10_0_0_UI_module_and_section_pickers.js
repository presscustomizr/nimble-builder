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
                      _section_;
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
                        input.contentType( $(this).data( 'sek-content-type') );
                  });

                  input.contentType = new api.Value();
                  input.contentType.bind( function( contentType ) {
                        input.container.find( '[data-sek-content-type="' + input.contentType() + '"]').trigger('click');
                        _.each( _section_.controls(), function( _control_ ) {
                              if ( ! _.isUndefined( _control_.content_type ) ) {
                                    _control_.active( contentType === _control_.content_type );
                              }
                        });
                  });

                  // initialize
                  input.contentType( input() );

                  // react to content_type changes triggered by on user actions
                  // @see api.czr_sektions.generateUIforDraggableContent()
                  _section_.container.first().bind( 'sek-content-type-refreshed', function( evt, param ){
                        input.contentType( param.content_type || 'section' );
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
      _.each([
            'sek_intro_sec_picker_module',
            'sek_features_sec_picker_module',
            'sek_contact_sec_picker_module',
            'sek_column_layouts_sec_picker_module'
      ], function( module_type ) {
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