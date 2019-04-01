//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRTextEditorInputMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );

                    // run the parent initialize
                    // Note : must be always invoked always after the input / item class extension
                    // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRTextEditorInputMths : {
                    initialize : function( name, options ) {
                          var input = this;
                          // Expand the editor when ready
                          if ( 'detached_tinymce_editor' == input.type ) {
                                input.isReady.then( function() {
                                      input.container.find('[data-czr-action="open-tinymce-editor"]').trigger('click');
                                });
                          }
                          api.CZRInput.prototype.initialize.call( input, name, options );
                    }
            },//CZRTextEditorInputMths

            // CZRSocialsItem : { },//CZRSocialsItem
      };//Constructor


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
            czr_tinymce_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_tinymce_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_tinymce_child' )
            },
      });
})( wp.customize , jQuery, _ );