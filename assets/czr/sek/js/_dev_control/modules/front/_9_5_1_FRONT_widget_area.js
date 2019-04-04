//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
              initialize: function( id, options ) {
                      var module = this;

                      // fixes https://github.com/presscustomizr/nimble-builder/issues/426
                      // 'nimble-set-select-input-options' is triggered in api.czr_sektions.setupSelectInput
                      module.bind('nimble-set-select-input-options', function( filtrable ) {
                            filtrable.params = sektionsLocalizedData.registeredWidgetZones;
                      });

                      // run the parent initialize
                      // Note : must be always invoked always after the input / item class extension
                      // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize
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
            czr_widget_area_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_widget_area_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_widget_area_module' )
            }
      });
})( wp.customize , jQuery, _ );