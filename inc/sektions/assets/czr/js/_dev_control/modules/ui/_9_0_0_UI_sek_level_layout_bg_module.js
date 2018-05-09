//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var LevelBackgroundModuleConstructor = {
            initialize: function( id, options ) {
                  //console.log('INITIALIZING SEKTION OPTIONS', id, options );
                  var module = this;
                  //run the parent initialize
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend( module.CZRLBBInputMths || {} );
                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  // module.itemConstructor = api.CZRItem.extend( module.CZRSocialsItem || {} );
            },//initialize

            CZRLBBInputMths : {
                    initialize : function( name, options ) {
                          var input = this;
                          api.CZRInput.prototype.initialize.call( input, name, options );

                          // trigger a level refresh when some specific inputs are changed
                          if ( 'boxed-wide' == input.id ) {
                                input.bind( function( to, from ) {
                                      var registrationParams = input.input_parent.control.params.sek_registration_params,
                                          level = registrationParams.level,
                                          level_id = registrationParams.level_id;

                                      var refreshLevelMarkupWhenDone = function( params ) {
                                            api.previewer.trigger('sek-refresh-level', {
                                                  level : level,
                                                  id : level_id
                                            });
                                            // this has to be unbound each time, otherwise the binding will be cumulated and fired the number of time this input has been changed
                                            api.previewer.unbind('sek-set-level-options_done', refreshLevelMarkupWhenDone );
                                      };
                                      // modifying the option module triggers a "sek-set-level-options" action on the previewer
                                      // @see ::generateUI => case sek-generate-level-options-ui
                                      // which is followed by a modification of the main section setting and a ajaxRefreshStylesheet
                                      // @see preview => ::schedulePanelMsgReactions => case "sek-set-level-options"
                                      // we receive in return the 'sek-set-level-options_done' action.
                                      // This way we are sure that the input change has been taken into account in the api setting and in the preview css.
                                      api.previewer.bind('sek-set-level-options_done', refreshLevelMarkupWhenDone );
                                });
                          }
                    },

                    setupSelect : function() {
                            var input  = this,
                                  item   = input.input_parent,
                                  module = input.module,
                                  _options_ = {};

                            if ( _.isEmpty( sektionsLocalizedData.selectOptions[input.id] ) ) {
                                  api.errare( 'Missing select options for input id => ' + input.id + ' in lbb module');
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
                    },
            },//CZRLBBInputMths

            // CZRSocialsItem : { },//CZRSocialsItem
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
            sek_level_layout_bg_module : {
                  mthds : LevelBackgroundModuleConstructor,
                  crud : false,
                  name : 'Layout Background Border Options',
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_level_layout_bg_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );