//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var ModuleConstructor = {
            // initialize: function( id, options ) {
            //         //console.log('INITIALIZING IMAGE MODULE', id, options );
            //         var module = this;
            //         //run the parent initialize
            //         api.CZRDynModule.prototype.initialize.call( module, id, options );

            //         // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
            //         //module.inputConstructor = api.CZRInput.extend( module.CCZRInputMths || {} );
            //         // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
            //         // module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );
            // },//initialize

            // CZRInputMths : {},//CZRInputMths

            // CZRItemMethods : { },//CZRItemMethods
      };//ModuleConstructor


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
            czr_heading_module : {
                  mthds : ModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_heading_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_heading_module' )
            },
      });
})( wp.customize , jQuery, _ );