//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;
                  // EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );
                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

            },//initialize

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
                                    case 'links_underline' :
                                          api.czr_sektions.scheduleVisibilityOfInputId.call( input, 'links_underline_hover', function() {
                                                return !input();
                                          });
                                    break;
                              }
                        });
                  }
            }//CZRItemConstructor
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
            sek_global_text : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_global_text', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_global_text' )
                  )
            },
      });
})( wp.customize , jQuery, _ );