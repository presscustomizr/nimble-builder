//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      //BUTTON MODULE
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                      module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                      // run the parent initialize
                      // Note : must be always invoked always after the input / item class extension
                      // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize

              //////////////////////////////////////////////////////////
              /// ITEM CONSTRUCTOR
              //////////////////////////////////////////
              CZRItemConstructor : {
                    //overrides the parent ready
                    ready : function() {
                          var item = this;
                          //wait for the input collection to be populated,
                          //and then set the input visibility dependencies
                          item.inputCollection.bind( function( col ) {
                                if( _.isEmpty( col ) )
                                  return;
                                try { item.setInputVisibilityDeps(); } catch( er ) {
                                      api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                                }
                          });//item.inputCollection.bind()

                          //fire the parent
                          api.CZRItem.prototype.ready.call( item );
                    },

                    //Fired when the input collection is populated
                    //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                    setInputVisibilityDeps : function() {
                          var item = this,
                              module = item.module;
                          //Internal item dependencies
                          item.czr_Input.each( function( input ) {
                                switch( input.id ) {
                                    case 'border-type' :
                                          _.each( [ 'borders' ] , function(_inputId_ ) {
                                                try { api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      return 'none' !== input();
                                                }); } catch( er ) {
                                                      api.errare( module.id + ' => error in setInputVisibilityDeps', er );
                                                }
                                          });
                                    break;
                                }
                          });
                    }
              }
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
            czr_simple_form_design_child: {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_simple_form_design_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_simple_form_design_child' )
            }
      });
})( wp.customize , jQuery, _ );