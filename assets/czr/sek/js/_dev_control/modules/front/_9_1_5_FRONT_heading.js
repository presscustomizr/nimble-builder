//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      //HEADING MODULE
      var HeadingModuleConstructor  = {
            initialize: function( id, options ) {
                  var module = this;

                  //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRHeadingInputMths || {} );


                  //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  //module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRHeadingInputMths: {
                  setupSelect : function() {
                        api.czr_sektions.setupSelectInput.call( this );
                  }
            },//CZRHeadingsInputMths
      };//HeadingModuleConstructor


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
            czr_heading_child : {
                  mthds : HeadingModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_heading_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_heading_child' )
            }
      });
})( wp.customize , jQuery, _ );