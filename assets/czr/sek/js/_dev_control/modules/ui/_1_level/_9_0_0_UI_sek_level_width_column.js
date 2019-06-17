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

                          // Get the moduleRegistration Params
                          var moduleRegistrationParams;
                          try{ moduleRegistrationParams = input.module.control.params.sek_registration_params; } catch( er ) {
                                api.errare('Error when getting the module registration params', er  );
                                return;
                          }
                          if ( _.isUndefined( moduleRegistrationParams.level_id ) ) {
                                api.errare('Error : missing column id', er  );
                                return;
                          }

                          // Get the column id and model,
                          // the parent section model
                          // and calculate the number of columns in the parent section
                          input.columnId = moduleRegistrationParams.level_id;
                          input.columnModel = $.extend( true, {}, api.czr_sektions.getLevelModel( input.columnId ) );
                          input.parentSectionModel = api.czr_sektions.getParentSectionFromColumnId( input.columnId );

                          if ( 'no_match' == input.columnModel ) {
                                api.errare( 'sek_level_width_column module => invalid column model' );
                                return;
                          }
                          if ( 'no_match' == input.parentSectionModel ) {
                                api.errare( 'sek_level_width_column module => invalid parent section model' );
                                return;
                          }

                          // Calculate the column number in the parent section
                          input.colNb = _.size( input.parentSectionModel.collection );

                          // Add the column id identifier, so we can communicate with it and update its value when the column gets resized from user
                          // @see update api setting, 'sek-resize-columns' case
                          $numberInput.attr('data-sek-width-range-column-id', input.columnId );

                          // For single column section, we don't want to display this module
                          if ( 1 === input.colNb ) {
                                input.container.html( ['<p>', sektionsLocalizedData.i18n['This is a single-column section with a width of 100%. You can act on the internal width of the parent section, or adjust padding and margin.']].join('') );
                          } else {
                                input.container.show();
                          }

                          // Always get the value from the model instead of relying on the setting val.
                          // => because the column width value is not only set from the customizer input, but also from the preview when resizing manually, this is an exception
                          var currentColumnModelValue = api.czr_sektions.getLevelModel( input.columnId ),
                              currentColumnWidthValueFromModel = '_not_set_',
                              columnWidthInPercent;

                          if ( 'no_match' == currentColumnModelValue ) {
                                api.errare( 'sek_level_width_column module => invalid column model' );
                                return;
                          }

                          var hasCustomWidth = currentColumnModelValue.options && currentColumnModelValue.options.width && currentColumnModelValue.options.width['custom-width'] && _.isNumber( +currentColumnModelValue.options.width['custom-width'] );

                          if ( hasCustomWidth ) {
                                currentColumnWidthValueFromModel = currentColumnModelValue.options.width['custom-width'];
                          }
                          // For retrocompat, use the former width property when exists.
                          // Deprecated in June 2019. See https://github.com/presscustomizr/nimble-builder/issues/279
                          else if ( ! hasCustomWidth && currentColumnModelValue.width && _.isNumber( +currentColumnModelValue.width ) ) {
                                currentColumnWidthValueFromModel = currentColumnModelValue.width;
                          }


                          if ( '_not_set_' !== currentColumnWidthValueFromModel ) {
                                columnWidthInPercent = currentColumnWidthValueFromModel;
                          }
                          // The default width is "_not_set_"
                          // @see php sek_get_module_params_for_sek_level_width_column()
                          // If not set, calculate the column width in percent based on the number of columns of the parent section
                          else if ( '_not_set_' === input() ) {
                                //$rangeInput.val( $numberInput.val() || 0 );
                                columnWidthInPercent = Math.floor( 100/input.colNb );
                          } else {
                                columnWidthInPercent = input();
                          }

                          // Cast to a number
                          columnWidthInPercent = +parseFloat(columnWidthInPercent).toFixed(3)*1;

                          // Make sure we have a number between 0 and 100
                          if ( ! _.isNumber( columnWidthInPercent ) || 100 < columnWidthInPercent || 0 > columnWidthInPercent ) {
                                api.errare( 'Error => invalid column width', columnWidthInPercent );
                                columnWidthInPercent = 50;
                          }


                          // synchronizes range input and number input
                          // number is the master => sets the input() val
                          $rangeInput.on('input', function( evt, params ) {
                                $numberInput.val( $(this).val() ).trigger('input', params );
                          });
                          // debounced to avoid a intermediate state of visual disorder of the columns
                          $numberInput.on('input', _.debounce(function( evt, params ) {
                                $rangeInput.val( $(this).val() );
                                if ( params && params.is_init )
                                  return;
                                input( +parseFloat( $(this).val() ).toFixed(3) );
                          }, 300 ) );

                          // say it to the api, so we can regenerate the columns width for all columns.
                          // consistently with the action triggered when resizing the column manually

                          // Make sure that we don't react to the event sent when resizing column in update api setting, case 'sek-resize-columns'
                          // where we do $('body').find('[data-sek-width-range-column-id="'+ _candidate_.id +'"]').val( newWidthValue ).trigger('input', { is_resize_column_trigger : true } );
                          // => otherwise it will create an infinite loop
                          //
                          // Debounce to avoid server hammering
                          $numberInput.on( 'input', _.debounce( function( evt, params ) {
                                if ( params && ( params.is_init || params.is_resize_column_trigger ) )
                                  return;
                                input.sayItToApi( $(this).val() );
                          }, 300 ) );
                          // trigger a change on init to sync the range input
                          $rangeInput.val( columnWidthInPercent ).trigger('input', { is_init : true } );
                    },


                    sayItToApi : function( columnWidthInPercent, _val  ) {
                          var input = this;
                          // Get the sister column id
                          // If parent section has at least 2 columns, the sister column is the one on the right if not in last position. On the left if last.
                          var indexOfResizedColumn = _.findIndex( input.parentSectionModel.collection, {id : input.columnId} ),
                              isLastColumn = indexOfResizedColumn + 1 == input.colNb,
                              sisterColumnIndex = isLastColumn ? indexOfResizedColumn - 1 : indexOfResizedColumn + 1,
                              sisterColumnModel = _.find( input.parentSectionModel.collection, function( _val, _key ) { return sisterColumnIndex === _key; });

                          if ( 'no_match' === sisterColumnModel ) {
                                api.errare( 'sek_level_width_column module => invalid sister column model' );
                          }

                          api.previewer.trigger( 'sek-resize-columns', {
                                action : 'sek-resize-columns',
                                level : 'column',
                                in_sektion : input.parentSectionModel.id,
                                id : input.columnId,

                                resized_column : input.columnId,
                                sister_column : sisterColumnModel.id ,

                                resizedColumnWidthInPercent : columnWidthInPercent,

                                col_number : input.colNb
                          });
                    }

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