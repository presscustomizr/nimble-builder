//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var FeaturedPagesConstruct = {
            initialize: function( id, options ) {
                    //console.log('INITIALIZING IMAGE MODULE', id, options );
                    var module = this;
                    //run the parent initialize
                    api.CZRDynModule.prototype.initialize.call( module, id, options );

                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                    module.inputConstructor = api.CZRInput.extend( module.CZRFPInputsMths || {} );
                    // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                    // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );

                    //SET THE CONTENT PICKER DEFAULT OPTIONS
                    //@see ::setupContentPicker()
                    module.bind( 'set_default_content_picker_options', function( defaultContentPickerOption ) {
                          defaultContentPickerOption = { defaultOption : [ {
                                'title'      : '<span style="font-weight:bold">@missi18n Set a custom url</span>',
                                'type'       : '',
                                'type_label' : '',
                                'object'     : '',
                                'id'         : '_custom_',
                                'url'        : ''
                          }]};
                          return defaultContentPickerOption;
                    });
            },//initialize

            CZRFPInputsMths : {
                    // initialize : function( name, options ) {
                    //       var input = this;
                    //       api.CZRInput.prototype.initialize.call( input, name, options );
                    // },

                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in featured pages module');
                                  return;
                            } else {
                                  //generates the options
                                  _.each( sektionsLocalizedData.selectOptions[input.id] , function( title, value ) {
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
                            }
                    }
            },//CZRFPInputsMths

            // CZRSocialsItem : { },//CZRSocialsItem
      };//FeaturedPagesConstruct

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
            czr_featured_pages_module : {
                  mthds : FeaturedPagesConstruct,
                  crud : true,
                  name : 'Featured Pages',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_featured_pages_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );