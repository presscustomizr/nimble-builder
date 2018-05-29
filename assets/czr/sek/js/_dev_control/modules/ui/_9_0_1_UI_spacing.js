//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var SpacingModuleConstructor = {
            initialize: function( id, options ) {
                    var module = this;
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRSpacingInputMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    module.itemConstructor = api.CZRItem.extend( module.CZRSpacingItemMths || {} );
                    //run the parent initialize
                    api.CZRDynModule.prototype.initialize.call( module, id, options );
            },//initialize

            CZRSpacingInputMths : {
                    // initialize : function( name, options ) {
                    //       var input = this;
                    //       api.CZRInput.prototype.initialize.call( input, name, options );
                    // },

                    setupSelect : function() {
                             var input              = this,
                                  item               = input.input_parent,
                                  module             = input.module;
                           //generates the options
                            _.each( sektionsLocalizedData.selectOptions.spacingUnits , function( title, value ) {
                                  var _attributes = {
                                            value : value,
                                            html: title
                                      };
                                  if ( value == input() ) {
                                        $.extend( _attributes, { selected : "selected" } );
                                  } else if ( 'px' === value ) {
                                        $.extend( _attributes, { selected : "selected" } );
                                  }
                                  $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                            });
                            $( 'select[data-czrtype]', input.container ).selecter();
                    },
            },//CZRSpacingInputMths

            CZRSpacingItemMths : {
                    initialize : function( id, options ) {
                          api.CZRItem.prototype.initialize.call( this, id, options );
                          var item = this;
                          // Listen to tab switch event
                          // @params { id : (string) }
                          item.bind( 'tab-switch', function( params ) {
                                device = 'desktop';
                                try { device = item.container.find('[data-tab-id="' + params.id + '"]').data('sek-device'); } catch( er ) {
                                      api.errare( 'spacing input => error when binding the tab switch event', er );
                                }
                                try { api.previewedDevice( device ); } catch( er ) {
                                      api.errare( 'spacing input => error when setting the device on tab switch', er );
                                }
                          });
                    }
            },//CZRSpacingItemMths
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
            sek_spacing_module : {
                  mthds : SpacingModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_spacing_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_spacing_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );