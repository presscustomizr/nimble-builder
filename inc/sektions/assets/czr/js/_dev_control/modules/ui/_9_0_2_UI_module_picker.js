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
                  name : 'Module Picker',
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
                input.container.find( '[draggable]').sekDrag({
                      // $(this) is the dragged element
                      onDragStart: function( event ) {
                            //console.log('ON DRAG START', $(this), $(this).data('sek-module-type'), event );
                            event.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                            event.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                            api.previewer.send( 'sek-drag-start' );
                      },

                      onDragEnd: function( event ) {
                            //console.log('ON DRAG END', $(this), event );
                            api.previewer.send( 'sek-drag-stop' );
                      }
                }).attr('data-sek-drag', true );

                api.czr_sektions.trigger( 'sek-refresh-sekdrop', { type : 'module_picker' } );
                //console.log( this.id, input_options );
            }
      });
})( wp.customize , jQuery, _ );