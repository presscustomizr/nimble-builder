/* Fix caching, czrSelect2 default one seems to not correctly work, or it doesn't what I think it should */
// the content_picker options are set in the module with :
// $.extend( module.inputOptions, {
//       'content_picker' : {
//             post : '',//<= all post types
//             taxonomy : ''//<= all taxonomy types
//       }
// });
// To narrow down the post or taxonomy types, the option can be set this way :
// $.extend( module.inputOptions, {
//       'content_picker' : {
//             post : [ 'page', 'cpt1', ...]
//             taxonomy : [ 'category', 'tag', 'Custom_Tax_1', ... ]
//       }
// });
// To disable all posts or taxonomy, use '_none_'
// $.extend( module.inputOptions, {
//       'content_picker' : {
//             post : [ 'page', 'cpt1', ...]
//             taxonomy : '_none_' //<= won't load or search in taxonomies when requesting wp in ajax
//       }
// });
//
// input is an object structured this way
// {
//  id:"2838"
//  object_type:"post"
//  title:"The Importance of Water and Drinking Lots Of It"
//  type_label:"Post"
//  url:"http://customizr-dev.dev/?p=2838"
// }
var CZRInputMths = CZRInputMths || {};
( function ( api, $, _ ) {
$.extend( CZRInputMths , {
      // fired in the input::initialize()
      setupContentPicker: function( wpObjectTypes ) {
              var input  = this,
              _event_map = [];

              /* Dummy for the prototype purpose */
              //input.object = ['post']; //this.control.params.object_types  - array('page', 'post')
              $.extend( _.isObject( wpObjectTypes ) ? wpObjectTypes : {}, {
                    post : '',
                    taxonomy : ''
              } );

              input.wpObjectTypes = wpObjectTypes;

              /* Methodize this or use a template */
              input.container.find('.czr-input').append('<select data-select-type="content-picker-select" class="js-example-basic-simple"></select>');

              // Overrides the default input_event_map declared in ::initialize()
              input.input_event_map = [
                    //set input value
                    {
                          trigger   : 'change',
                          selector  : 'select[data-select-type]',
                          name      : 'set_input_value',
                          actions   : function( obj ){
                                var $_changed_input   = $( obj.dom_event.currentTarget, obj.dom_el ),
                                    _raw_val          = $( $_changed_input, obj.dom_el ).czrSelect2( 'data' ),
                                    _val_candidate    = {},
                                    _default          = {
                                          id          : '',
                                          type_label  : '',
                                          title       : '',
                                          object_type : '',
                                          url         : ''
                                    };

                                _raw_val = _.isArray( _raw_val ) ? _raw_val[0] : _raw_val;
                                if ( ! _.isObject( _raw_val ) || _.isEmpty( _raw_val ) ) {
                                    api.errare( 'Content Picker Input : the picked value should be an object not empty.');
                                    return;
                                }

                                //normalize and purge useless czrSelect2 fields
                                //=> skip a possible _custom_ id, used for example in the slider module to set a custom url
                                _.each( _default, function( val, k ){
                                      if ( '_custom_' !== _raw_val.id ) {
                                            if ( ! _.has( _raw_val, k ) || _.isEmpty( _raw_val[ k ] ) ) {
                                                  api.errare( 'content_picker : missing input param : ' + k );
                                                  return;
                                            }
                                      }
                                      _val_candidate[ k ] = _raw_val[ k ];
                                } );
                                //set the value now
                                input.set( _val_candidate );
                          }
                    }
              ];

              //input.setupDOMListeners( _event_map , { dom_el : input.container }, input );
              //setup when ready.
              input.isReady.done( function() {
                    input.setupContentSelecter();
              });

      },


      // input is an object structured this way
      // {
      //  id:"2838"
      //  object_type:"post"
      //  title:"The Importance of Water and Drinking Lots Of It"
      //  type_label:"Post"
      //  url:"http://customizr-dev.dev/?p=2838"
      // }
      setupContentSelecter : function() {
              var input = this;
              //set the previously selected value
              if ( ! _.isEmpty( input() ) ) {
                    var _attributes = {
                          value : input().id || '',
                          title : input().title || '',
                          selected : "selected"
                    };
                    //input.container.find('select')
                    input.container.find('select').append( $( '<option>', _attributes ) );
              }

              // Stores the current ajax action
              input.currentAjaxAction = input.currentAjaxAction || new api.Value();

              // When the ajax action changes, reset the rendering status of the defaultContentPickerOption
              // fixes "Set Custom Url" being printed multiple times, @see https://github.com/presscustomizr/nimble-builder/issues/207
              input.currentAjaxAction.bind( function( ajaxAction ) {
                    input.defaultValueHasBeenPushed = false;
              });

              // reset the rendering status of the defaultContentPickerOption
              // fixes "Set Custom Url" being printed multiple times, @see https://github.com/presscustomizr/nimble-builder/issues/207
              input.container.find( 'select' ).on('czrSelect2:select czrSelect2:unselect czrSelect2:close czrSelect2:open', function (e) {
                    input.defaultValueHasBeenPushed = false;
              });

              input.container.find( 'select' ).czrSelect2( {
                    placeholder: {
                          id: '-1', // the value of the option
                          title: 'Select'
                    },
                    data : input.setupSelectedContents(),
                    //  allowClear: true,
                    ajax: {
                          url: wp.ajax.settings.url,// was serverControlParams.AjaxUrl,
                          type: 'POST',
                          dataType: 'json',
                          delay: 250,
                          debug: true,
                          data: function ( params ) {
                                //for some reason I'm not getting at the moment the params.page returned when searching is different
                                var page = params.page ? params.page : 0;
                                page = params.term ? params.page : page;

                                // Set the current ajax action now
                                input.currentAjaxAction( params.term ? "search-available-content-items-customizer" : "load-available-content-items-customizer" );

                                return {
                                      action          : input.currentAjaxAction(),
                                      search          : params.term,
                                      wp_customize    : 'on',
                                      page            : page,
                                      wp_object_types : JSON.stringify( input.wpObjectTypes ),
                                      nonce : api.settings.nonce.save
                                };
                          },
                          //  transport: function (params, success, failure) {
                          //   console.log('params', params );
                          //   console.log('success', success );
                          //   console.log('failure', failure );
                          //   var $request = $.ajax(params);

                          //   $request.then(success);
                          //   $request.fail(failure);

                          //   return $request;
                          // },
                          processResults: function ( data, params ) {
                                //allows us to remotely set a default option like custom link when initializing the content picker input.
                                var defaultContentPickerOption = { defaultOption : {
                                            id          : '',
                                            title       : '',
                                            type_label  : '',
                                            object_type : '',
                                            url         : ''
                                      }
                                };
                                if ( input.input_parent && input.input_parent.module ) {
                                      input.input_parent.module.trigger( 'set_default_content_picker_options', { defaultContentPickerOption : defaultContentPickerOption } );
                                } else {
                                      api.infoLog(' content_picker input => ::processResults => event "set_default_content_picker_option" not triggered when in pre-item');
                                }

                                if ( ! data.success ) {
                                      api.errare('request failure in setupContentPicker => processResults', data );
                                      return { results: defaultContentPickerOption.defaultOption };
                                }

                                var items   = data.data.items,
                                    _results = [];

                                // cast items to an array
                                items = !_.isArray( items ) ? [] : items;

                                input.defaultValueHasBeenPushed = input.defaultValueHasBeenPushed || false;

                                if ( 'load-available-content-items-customizer' === input.currentAjaxAction() && ! _.isEmpty( defaultContentPickerOption.defaultOption ) ) {
                                      if ( defaultContentPickerOption.defaultOption.id && ! input.defaultValueHasBeenPushed ) {
                                            _results.push( defaultContentPickerOption.defaultOption );
                                            input.defaultValueHasBeenPushed = true;
                                      }
                                }

                                _.each( items, function( item ) {
                                      _results.push({
                                            id          : item.id,
                                            title       : item.title,
                                            type_label  : item.type_label,
                                            object_type : item.object,
                                            url         : item.url
                                      });
                                });

                                return {
                                      results: _results,
                                      //The pagination param will trigger the infinite load
                                      //@to be improved
                                      pagination:  { more: items.length >= 1 }//<= the pagination boolean param can be tricky => here set to >= 10 because we query 10 + add a custom link item on the first query
                                };
                          },
                    },//ajax
                    templateSelection: input.czrFormatContentSelected,
                    templateResult: input.czrFormatContentSelected,
                    escapeMarkup: function ( markup ) { return markup; },
             });//czrSelect2 setup
      },

      // item is structured this way :
      // {
      // id          : item.id,
      // title       : item.title,
      // type_label  : item.type_label,
      // object_type : item.object,
      // url         : item.url
      // }
      czrFormatContentSelected: function ( item ) {
              if ( item.loading ) return item.text;
              var markup = "<div class='content-picker-item'>" +
                "<div class='content-item-bar'>" +
                  "<span class='czr-picker-item-title'>" + item.title + "</span>";

              if ( item.type_label ) {
                markup += "<span class='czr-picker-item-type'>" + item.type_label + "</span>";
              }

              markup += "</div></div>";

              return markup;
      },

      setupSelectedContents : function() {
            var input = this,
               _model = input();

            return _model;
      }
});//$.extend
})( wp.customize , jQuery, _ );