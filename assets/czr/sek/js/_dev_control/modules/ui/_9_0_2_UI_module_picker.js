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
                input.container.find( '[draggable]').nimbleZones({
                      // DRAG OPTIONS
                      // $(this) is the dragged element
                      onStart: function( event ) {
                            console.log('ON DRAG START', $(this), $(this).data('sek-content-id'), event );
                            event.originalEvent.dataTransfer.setData( "sek-content-type", $(this).data('sek-content-type') );
                            event.originalEvent.dataTransfer.setData( "sek-content-id", $(this).data('sek-content-id') );
                            // event.originalEvent.dataTransfer.effectAllowed = "move";
                            // event.originalEvent.dataTransfer.dropEffect = "move";

                            api.previewer.send( 'sek-drag-start' );
                            $(event.currentTarget).addClass('sek-grabbing');
                      },
                      onEnd: function( event ) {
                            console.log('ON DRAG END', $(this), event );
                            api.previewer.send( 'sek-drag-stop' );
                            // make sure that the sek-grabbing class ( -webkit-grabbing ) gets reset on dragEnd
                            $(event.currentTarget).removeClass('sek-grabbing');
                      },

                      // DROP OPTIONS
                      dropZones : $( api.previewer.targetWindow().document ).find( '.sektion-wrapper'),
                      placeholderClass: 'sortable-placeholder',
                      onDrop: function( position, event ) {
                            event.stopPropagation();
                            var _position = 'after' === position ? $(this).index() + 1 : $(this).index();
                            console.log('ON DROPPING', position, event.originalEvent.dataTransfer.getData( "sek-content-id" ), $(self) );

                            // console.log('onDropping params', position, event );
                            // console.log('onDropping element => ', $(self) );
                            api.czr_sektions.trigger( 'sek-content-dropped', {
                                  drop_target_element : $(this),
                                  location : $(this).closest('[data-sek-level="location"]').data('sek-id'),
                                  position : _position,
                                  before_section : $(this).data('sek-before-section'),
                                  after_section : $(this).data('sek-after-section'),
                                  content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ),
                                  content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
                            });
                      },
                      dropSelectors: [
                            '.sek-module-drop-zone-for-first-module',//the drop zone when there's no module or nested sektion in the column
                            '.sek-module',// the drop zone when there is at least one module
                            '.sek-column > .sek-module-wrapper sek-section',// the drop zone when there is at least one nested section
                            '.sek-content-drop-zone'//between sections
                      ].join(','),
                      placeholderContent : function( evt ) {
                            var $target = $( evt.currentTarget ),
                                html = '@missi18n Insert Here';

                            if ( $target.length > 0 ) {
                                if ( 'between-sections' == $target.data('sek-location') ) {
                                      html = '@missi18n Insert in a new section';
                                }
                            }
                            return '<div class="sek-module-placeholder-content"><p>' + html + '</p></div>';
                      },
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
                api.czr_sektions.trigger( 'sek-refresh-sekdrop', { type : 'module_picker' } );
                //console.log( this.id, input_options );
            }
      });
})( wp.customize , jQuery, _ );