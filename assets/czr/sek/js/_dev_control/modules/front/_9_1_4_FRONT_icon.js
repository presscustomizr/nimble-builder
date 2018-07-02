//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      //ICON MODULE
      var IconModuleConstructor = {
              initialize: function( id, options ) {
                      //console.log('INITIALIZING IMAGE MODULE', id, options );
                      var module = this;

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                      module.inputConstructor = api.CZRInput.extend( module.CZRIconInputMths || {} );

                      //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                      //module.itemConstructor = api.CZRItem.extend( module.CZRItemMethods || {} );


                      //SET THE CONTENT PICKER DEFAULT OPTIONS
                      //@see ::setupContentPicker()
                      module.bind( 'set_default_content_picker_options', function( params ) {
                            params.defaultContentPickerOption.defaultOption = {
                                  'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                                  'type'       : '',
                                  'type_label' : '',
                                  'object'     : '',
                                  'id'         : '_custom_',
                                  'url'        : ''
                            };
                            return params;
                      });

                      // run the parent initialize
                      // Note : must be always invoked always after the input / item class extension
                      // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                      api.CZRDynModule.prototype.initialize.call( module, id, options );

              },//initialize

              /* Helpers */

              CZRIconInputMths: {
                      setupSelect : function() {
                              var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _model  = item();

                               //Link select
                              if ( 'link-to' == input.id ) {
                                    if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                          api.errare( 'Missing select options for input id => ' + input.id + ' in icon module');
                                          return;
                                    } else {
                                          //generates the options
                                          _.each( sektionsLocalizedData.selectOptions[input.id] , function( title, value ) {
                                                //get only no-link and url
                                                if ( !(  _.contains([ 'no-link', 'url' ], value) ) ) {
                                                      return;
                                                }
                                                var _attributes = {
                                                          value : value,
                                                          html: title
                                                    };
                                                if ( value == input() ) {
                                                      $.extend( _attributes, { selected : "selected" } );
                                                }

                                                $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                                          });
                                          $( 'select[data-czrtype]', input.container ).selecter();
                                    }
                              }// if
                      }//setupSelect
              },//CZRIconInputMths
      };//IconModuleConstructor


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
            czr_icon_module : {
                  mthds : IconModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_icon_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_icon_module' )
            },
      });
})( wp.customize , jQuery, _ );