
//extends api.CZRModule
var CZRBodyBgModuleMths = CZRBodyBgModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRBodyBgModuleMths, {
      initialize: function( id, options ) {
            var module = this;
            //run the parent initialize
            api.CZRModule.prototype.initialize.call( module, id, options );

            //extend the module with new template Selectors
            $.extend( module, {
                  itemInputList : 'czr-module-bodybg-item-content'
            } );

            //EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
            module.inputConstructor = api.CZRInput.extend( module.CZRBodyBgInputMths || {} );
            //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
            module.itemConstructor = api.CZRItem.extend( module.CZBodyBgItemMths || {} );

            //declares a default model
            module.defaultItemModel = {
                  'background-color' : '#eaeaea',
                  'background-image' : '',
                  'background-repeat' : 'no-repeat',
                  'background-attachment' : 'fixed',
                  'background-position' : 'center center',
                  'background-size' : 'cover'
            };

            //fired ready :
            //1) on section expansion
            //2) or in the case of a module embedded in a regular control, if the module section is alreay opened => typically when skope is enabled
            if ( _.has( api, 'czr_activeSectionId' ) && module.control.section() == api.czr_activeSectionId() && 'resolved' != module.isReady.state() ) {
                  module.ready();
            }
            api.section( module.control.section() ).expanded.bind(function(to) {
                  if ( 'resolved' == module.isReady.state() )
                    return;
                  module.ready();
            });
      },//initialize



      CZRBodyBgInputMths : {
            //////////////////////////////////////////////////
            ///SETUP SELECTS
            //////////////////////////////////////////////////
            //setup select on view_rendered|item_content_event_map
            setupSelect : function() {
                  var input         = this,
                      _id_param_map = {
                        'background-repeat' : 'bg_repeat_options',
                        'background-attachment' : 'bg_attachment_options',
                        'background-position' : 'bg_position_options'
                      },
                      item          = input.input_parent,
                      serverParams  = serverControlParams.body_bg_module_params,
                      options       = {},
                      module        = input.module;

                  if ( ! _.has( _id_param_map, input.id ) )
                    return;

                  if ( _.isUndefined( serverParams ) || _.isUndefined( serverParams[ _id_param_map[input.id] ] ) )
                    return;
                  options = serverParams[ _id_param_map[input.id] ];
                  if ( _.isEmpty(options) )
                    return;
                  //generates the options
                  _.each( options, function( title, key ) {
                        var _attributes = {
                              value : key,
                              html: title
                            };
                        if ( key == input() || _.contains( input(), key ) )
                          $.extend( _attributes, { selected : "selected" } );

                        $( 'select[data-czrtype]', input.container ).append( $('<option>', _attributes) );
                  });
                  //fire czrSelect2
                  $( 'select[data-czrtype]', input.container ).czrSelect2();
            }
      },


      CZBodyBgItemMths : {
            //Fired if the item has been instantiated
            //The item.callbacks are declared.
            ready : function() {
                  var item = this;
                  api.CZRItem.prototype.ready.call( item );

                  item.inputCollection.bind( function( _col_ ) {
                        if ( ! _.isEmpty( _col ) && item.czr_Input && item.czr_Input.has( 'background-image' ) ) {
                              item.czr_Input('background-image').isReady.done( function( input_instance ) {
                                    var set_visibilities = function( bg_val  ) {
                                          var is_bg_img_set = ! _.isEmpty( bg_val ) ||_.isNumber( bg_val);
                                          _.each( ['background-repeat', 'background-attachment', 'background-position', 'background-size'], function( dep ) {
                                                item.czr_Input(dep).container.toggle( is_bg_img_set || false );
                                          });
                                    };
                                    set_visibilities( input_instance() );
                                    //update the item model on 'background-image' change
                                    item.bind('background-image:changed', function(){
                                          set_visibilities( item.czr_Input('background-image')() );
                                    });
                              });
                        }
                  });

            },

      }
});//$.extend
})( wp.customize , jQuery, _ );