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
            sek_section_picker_module : {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : 'Section Picker',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_section_picker_module' )
                  )
            },
      });

      api.czrInputMap = api.czrInputMap || {};
      //input_type => callback fn to fire in the Input constructor on initialize
      //the callback can receive specific params define in each module constructor
      //For example, a content picker can be given params to display only taxonomies
      $.extend( api.czrInputMap, {
            section_picker : function( input_options ) {
                  var input = this;
                  input.container.find( '[draggable]').sekDrag({
                        // $(this) is the dragged element
                        onDragStart: function( event ) {
                              //console.log('ON DRAG START', $(this), $(this).data('sek-module-type'), event );
                              event.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                              event.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                              api.previewer.send( 'sek-drag-start' );
                              $(event.currentTarget).addClass('sek-grabbing');
                        },

                        onDragEnd: function( event ) {
                              //console.log('ON DRAG END', $(this), event );
                              api.previewer.send( 'sek-drag-stop' );
                              $(event.currentTarget).removeClass('sek-grabbing');
                        }
                  }).attr('data-sek-drag', true );

                   // Mouse effect with cursor: -webkit-grab; -webkit-grabbing;
                  input.container.find('[draggable]').each( function() {
                        $(this).on( 'mousedown mouseup', function( evt ) {
                              switch( evt.type ) {
                                    case 'mousedown' :
                                          $(this).addClass('sek-grabbing');
                                    break;
                                    case 'mouseup' :
                                          $(this).removeClass('sek-grabbing');
                                    break;
                              }
                        });
                  });
                  api.czr_sektions.trigger( 'sek-refresh-sekdrop', { type : 'section_picker' } );
            }
      });
})( wp.customize , jQuery, _ );