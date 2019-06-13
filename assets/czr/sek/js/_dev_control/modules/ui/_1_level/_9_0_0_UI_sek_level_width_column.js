//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  // EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRInputConstructor || {} );
                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

            // Constructor for the input
            CZRInputConstructor : {
                    // initialize : function( name, options ) {
                    //       var input = this;
                    //       // Expand the editor when ready
                    //       if ( 'detached_tinymce_editor' == input.type ) {
                    //             input.isReady.then( function() {
                    //                   input.container.find('[data-czr-action="open-tinymce-editor"]').trigger('click');
                    //             });
                    //       }
                    //       api.CZRInput.prototype.initialize.call( input, name, options );
                    // },

                    // Overrides the default range_simple method for the column width module
                    range_simple : function( params ) {
                          var input = this,
                              $wrapper = $('.sek-range-with-unit-picker-wrapper', input.container ),
                              $numberInput = $wrapper.find( 'input[type="number"]'),
                              $rangeInput = $wrapper.find( 'input[type="range"]');

                          // synchronizes range input and number input
                          // number is the master => sets the input() val
                          $rangeInput.on('input', function( evt ) {
                                $numberInput.val( $(this).val() ).trigger('input');
                          });
                          $numberInput.on('input', function( evt ) {
                                input( $(this).val() );
                                $rangeInput.val( $(this).val() );
                          });

                          // trigger a change on init to sync the range input
                          $rangeInput.val( 30 ).trigger('input');
                          //$rangeInput.val( $numberInput.val() || 0 );
                          var moduleRegistrationParam;
                          try{ moduleRegistrationParam = input.module.control.params.sek_registration_params; } catch( er ) {
                                api.errare('Error when getting the module registration params', er  );
                                return;
                          }
                          if ( _.isUndefined( moduleRegistrationParam.level_id ) ) {
                                api.errare('Error : missing column id', er  );
                                return;
                          }
                          var columnId = moduleRegistrationParam.level_id;

                          var colNb = api.czr_sektions.getColNumberInParentSectionFromColumnId( columnId );
                          console.log('SO ?? ', input.module.control.params.sek_registration_params, colNb );
                    },
            },//CZRTextEditorInputMths
            // CZRItemConstructor : {
            //       //overrides the parent ready
            //       ready : function() {
            //             var item = this;
            //             //wait for the input collection to be populated,
            //             //and then set the input visibility dependencies
            //             item.inputCollection.bind( function( col ) {
            //                   if( _.isEmpty( col ) )
            //                     return;
            //                   try { item.setInputVisibilityDeps(); } catch( er ) {
            //                         api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
            //                   }
            //             });//item.inputCollection.bind()

            //             //fire the parent
            //             api.CZRItem.prototype.ready.call( item );
            //       },


            //       //Fired when the input collection is populated
            //       //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
            //       setInputVisibilityDeps : function() {
            //             var item = this,
            //                 module = item.module;

            //             //Internal item dependencies
            //             item.czr_Input.each( function( input ) {
            //                   switch( input.id ) {
            //                         case 'width-type' :
            //                               api.czr_sektions.scheduleVisibilityOfInputId.call( input, 'custom-width', function() {
            //                                     return 'custom' === input();
            //                               });
            //                               api.czr_sektions.scheduleVisibilityOfInputId.call( input, 'h_alignment', function() {
            //                                     return 'custom' === input();
            //                               });
            //                         break;
            //                   }
            //             });
            //       }
            // }//CZRItemConstructor
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
      $.extend( api.czrModuleMap, {
            sek_level_width_column : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_level_width_column', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_width_column' )
                  )
            },
      });
})( wp.customize , jQuery, _ );