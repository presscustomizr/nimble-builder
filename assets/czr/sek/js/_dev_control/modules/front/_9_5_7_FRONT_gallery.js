//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;


                  module.crudModulePart = 'nimble-crud-module-part';
                  module.rudItemPart = 'nimble-rud-item-part';

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

                  // module.isReady.then( function() {
                  //       if ( _.isUndefined( module.preItem ) )
                  //         return;
                  //       //specific update for the item preModel on social-icon change
                  //       module.preItem.bind( function( to, from ) {
                  //             if ( ! _.has(to, 'icon') )
                  //               return;
                  //             if ( _.isEqual( to['icon'], from['icon'] ) )
                  //               return;
                  //             module.updateItemModel( module.preItem, true );
                  //       });
                  // });
            },//initialize


            // overrides the default fmk method which generates a too long id for each item, like : "czr_gallery_collection_child_2"
            // this method generates a uniq GUID id for each item
            generateItemId : function() {
                    return api.czr_sektions.guid();
            },

            // Overrides the default fmk method, to disable the default preview refresh
            _makeItemsSortable : function(obj) {
                  if ( wp.media.isTouchDevice || ! $.fn.sortable )
                    return;
                  var module = this;
                  $( '.' + module.control.css_attr.items_wrapper, module.container ).sortable( {
                        handle: '.' + module.control.css_attr.item_sort_handle,
                        start: function() {},
                        update: function( event, ui ) {
                              var _sortedCollectionReact = function() {
                                    if ( _.has(module, 'preItem') ) {
                                          module.preItemExpanded.set(false);
                                    }

                                    module.closeAllItems().closeRemoveDialogs();
                                    // var refreshPreview = function() {
                                    //       api.previewer.refresh();
                                    // };
                                    // //refreshes the preview frame  :
                                    // //1) only needed if transport is postMessage, because is triggered by wp otherwise
                                    // //2) only needed when : add, remove, sort item(s).
                                    // //var isItemUpdate = ( _.size(from) == _.size(to) ) && ! _.isEmpty( _.difference(from, to) );
                                    // if ( 'postMessage' == api(module.control.id).transport  && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                                    //       refreshPreview = _.debounce( refreshPreview, 500 );//500ms are enough
                                    //       refreshPreview();
                                    // }

                                    module.trigger( 'item-collection-sorted' );
                              };
                              module._getSortedDOMItemCollection()
                                    .done( function( _collection_ ) {
                                          module.itemCollection.set( _collection_ );
                                    })
                                    .then( function() {
                                          _sortedCollectionReact();
                                    });
                              //refreshes the preview frame, only if the associated setting is a postMessage transport one, with no partial refresh
                              // if ( 'postMessage' == api( module.control.id ).transport && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                              //         _.delay( function() { api.previewer.refresh(); }, 100 );
                              // }
                        }//update
                      }
                  );
            },//_makeItemsSortable


            // Overrides core FMK method
            // introduced in July 2019 to solve the problem of the default image for the items
            // @see https://github.com/presscustomizr/nimble-builder/issues/479
            getPreItem : function() {
                  var rawStartingValue = api.czr_sektions.getRegisteredModuleProperty( 'czr_gallery_collection_child', 'starting_value' ),
                      preItemValue = $.extend( true, {}, this.preItem() );//create a new detached clones object

                  if ( _.isObject( rawStartingValue ) ) {
                        var startingValue = $.extend( true, {}, rawStartingValue );//create a new detached clones object
                        return $.extend( preItemValue, startingValue );
                  }

                  return this.preItem();
            },

            //////////////////////////////////////////////////////////
            /// ITEM CONSTRUCTOR
            //////////////////////////////////////////
            CZRItemConstructor : {
                  //overrides the parent ready
                  ready : function() {
                        var item = this;
                        // //wait for the input collection to be populated,
                        // //and then set the input visibility dependencies
                        // item.inputCollection.bind( function( col ) {
                        //       if( _.isEmpty( col ) )
                        //         return;
                        //       try { item.setInputVisibilityDeps(); } catch( er ) {
                        //             api.errorLog( 'item.setInputVisibilityDeps() : ' + er );
                        //       }
                        // });//item.inputCollection.bind()

                        // //update the item model on social-icon change
                        // item.bind('icon:changed', function(){
                        //       //item.module.updateItemModel( item );
                        // });
                        //fire the parent
                        api.CZRItem.prototype.ready.call( item );

                        // FOCUS ON CURRENTLY EXPANDED / EDITED ITEM
                        var requestFocusToPreview = function() {
                              api.previewer.send( 'sek-item-focus', {
                                    control_id : item.module.control.id,
                                    item_id : item.id,
                                    item_value : item()
                              });
                        };
                        // when the item get expanded
                        item.viewState.callbacks.add( function( to, from ) {
                              if ( 'expanded' === to ) {
                                    requestFocusToPreview();
                              }
                        });

                        // when the item value is changed
                        item.callbacks.add( requestFocusToPreview );

                        // when the module requests a focus after a preview update
                        item.bind('sek-request-item-focus-in-preview', requestFocusToPreview );

                        // // rewriteItemTitle when item are sorted, so that the placeholder title ( based in item DOM index ) get refreshed
                        // item.module.bind('item-collection-sorted', function() {
                        //       item.writeItemViewTitle( item(), { input_changed : 'title_text'} );
                        // });
                  },



                  //overrides the default parent method by a custom one
                  //at this stage, the model passed in the obj is up to date
                  writeItemViewTitle : function( model, data ) {
                        var item = this,
                            index = 1,
                            module  = item.module,
                            _model = model || item(),
                            _title = '',
                            _slideBg = '',
                            _src = 'not_set',
                            _areDataSet = ! _.isUndefined( data ) && _.isObject( data );

                        //When shall we update the item title ?
                        //=> when the slide title or the thumbnail have been updated
                        //=> on module model initialized
                        if ( _areDataSet && data.input_changed && ! _.contains( [ 'img' ], data.input_changed ) )
                          return;

                        //set title with index
                        if ( ! _.isEmpty( _model.title ) ) {
                              _title = _model.title;
                        } else {
                              //find the current item index in the collection
                              var _index = _.findIndex( module.itemCollection(), function( _itm ) {
                                    return _itm.id === item.id;
                              });
                              _index = _.isUndefined( _index ) ? index : _index + 1;
                        }

                        //if the slide title is set, use it
                        _title = api.CZR_Helpers.truncate( _title, 25 );

                        if ( _model['img'] ) {
                              _slideBg = _model['img'];
                              if ( _.isString( _model['img'] ) ) {
                                    // if the img is already an url, typically the default image
                                    if ( -1 !==  _model['img'].indexOf( 'http' ) ) {
                                          _slideBg = _model['img'];
                                    // else, cast to an int
                                    } else {
                                          _slideBg = parseInt( _model['img'], 10 );
                                    }
                              }
                        }

                        var _getThumbSrc = function() {
                              return $.Deferred( function() {
                                    var dfd = this;
                                    if ( _.isUndefined( _slideBg ) || _.isEmpty( '' + _slideBg ) ) { //<= always cast to a string when using _.isEmpty
                                          dfd.resolve( '' );
                                    }
                                    //try to set the default src
                                    else if ( _.isString( _slideBg ) && -1 !== _slideBg.indexOf( 'http' ) ) {
                                          dfd.resolve( _slideBg );
                                    } else {
                                          wp.media.attachment( _slideBg ).fetch()
                                                .always( function() {
                                                      var attachment = this;
                                                      if ( _.isObject( attachment ) && _.has( attachment, 'attributes' ) && _.has( attachment.attributes, 'sizes' ) ) {
                                                            var _sizes = attachment.get('sizes');
                                                            if ( _sizes && _.isObject( _sizes ) ) {
                                                                  // loop on the various possible image sizes, starting with thumbnail, the smallest.
                                                                  // as soon as an available size is found, use it as src
                                                                  _.each( ['thumbnail', 'medium', 'large', 'full' ], function( _val, _k ) {
                                                                        if ( 'not_set' === _src && _sizes[_val] && _.isObject( _sizes[_val] ) && _sizes[_val].url ) {
                                                                              _src = _sizes[_val].url;
                                                                        }
                                                                  });
                                                            }
                                                            dfd.resolve( _src );
                                                      }
                                                });
                                    }
                              }).promise();
                        };

                        var //$itemTitleEl = $( '.' + module.control.css_attr.item_title , item.container ).find('.sek-accord-title'),
                              $itemThumbEl = $( '.' + module.control.css_attr.item_title , item.container ).find( '.sek-slide-thumb');

                        //THUMB
                        //When shall we append the item thumb ?
                        //=>IF the sek-slide-thumb element is not set
                        //=>OR in the case where data have been provided and the input_changed is 'img'
                        //=>OR if no data is provided ( we are in the initialize phase )
                        var _isBgChange = _areDataSet && data.input_changed && 'img' === data.input_changed;

                        var _getThumbHtml = function( src ) {
                            return ( _.isEmpty( '' + src ) || 'not_set' === src ) ? '' : '<img src="' + src + '" width="32" alt="' + _title + '" />';
                        };

                        $( '.' + module.control.css_attr.item_title, item.container ).css('padding', '0 4px');


                        if ( 1 > $itemThumbEl.length ) {
                              _getThumbSrc().done( function( src ) {
                                    $( '.' + module.control.css_attr.item_title, item.container ).prepend( $('<div/>',
                                          {
                                                class : 'sek-slide-thumb',
                                                html : _getThumbHtml( src )
                                          }
                                    ));
                              });
                        } else if ( _isBgChange || ! _areDataSet ) {
                              _getThumbSrc().done( function( src ) {
                                    $itemThumbEl.html( _getThumbHtml( src ) );
                              });
                        }

                        //TITLE
                        // //always write the title
                        // var _text = _model['title_text'] ? _model['title_text'] : '';
                        // // Strip all html tags and keep only first characters
                        // _text = $("<div>").html(_text).text();

                        // // placeholder text, consistent with the php one in tmpl/gallery_tmpl.php
                        // var item_index = item.module.container.find( '.czr-items-wrapper > li').index( item.container );
                        // _text = _.isEmpty( _text ) ? sektionsLocalizedData.i18n['Accordion title'] + ' #' + ( +item_index+1 ) : _text;

                        // _text = _text.substring(0,60);
                        // if ( 1 > $itemTitleEl.length ) {
                        //       //remove the default item title
                        //       $( '.' + module.control.css_attr.item_title , item.container ).html( '' );
                        //       //write the new one
                        //       $( '.' + module.control.css_attr.item_title , item.container ).append( $( '<div/>',
                        //             {
                        //                 class : 'sek-accord-title',
                        //                 html : _text
                        //             }
                        //       ) );
                        // } else {
                        //       $itemTitleEl.html( _text );
                        // }
                  },//writeItemViewTitle

                  // Overrides the default fmk method in order to disable the remove dialog box
                  toggleRemoveAlert : function() {
                        this.removeItem();
                  },

                  // Overrides the default fmk method, to disable the default preview refresh
                  //fired on click dom event
                  //for dynamic multi input modules
                  //@return void()
                  //@param params : { dom_el : {}, dom_event : {}, event : {}, model {} }
                  removeItem : function( params ) {
                        params = params || {};
                        var item = this,
                            module = this.module,
                            _new_collection = _.clone( module.itemCollection() );

                        //hook here
                        module.trigger('pre_item_dom_remove', item() );

                        //destroy the Item DOM el
                        item._destroyView();

                        //new collection
                        //say it
                        _new_collection = _.without( _new_collection, _.findWhere( _new_collection, {id: item.id }) );
                        module.itemCollection.set( _new_collection );
                        //hook here
                        module.trigger('pre_item_api_remove', item() );

                        var _item_ = $.extend( true, {}, item() );

                        // <REMOVE THE ITEM FROM THE COLLECTION>
                        module.czr_Item.remove( item.id );
                        // </REMOVE THE ITEM FROM THE COLLECTION>

                        //refresh the preview frame (only needed if transport is postMessage && has no partial refresh set )
                        //must be a dom event not triggered
                        //otherwise we are in the init collection case where the items are fetched and added from the setting in initialize
                        if ( 'postMessage' == api(module.control.id).transport && _.has( params, 'dom_event') && ! _.has( params.dom_event, 'isTrigger' ) && ! api.CZR_Helpers.hasPartRefresh( module.control.id ) ) {
                              // api.previewer.refresh().done( function() {
                              //       _dfd_.resolve();
                              // });
                              // It would be better to wait for the refresh promise
                              // The following approach to bind and unbind when refreshing the preview is similar to the one coded in module::addItem()
                              var triggerEventWhenPreviewerReady = function() {
                                    api.previewer.unbind( 'ready', triggerEventWhenPreviewerReady );
                                    module.trigger( 'item-removed', _item_ );
                              };
                              api.previewer.bind( 'ready', triggerEventWhenPreviewerReady );
                              //api.previewer.refresh();
                        } else {
                              module.trigger( 'item-removed', _item_ );
                              module.control.trigger( 'item-removed', _item_ );
                        }
                  }
            },//CZRItemConstructor
      };//Constructor

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
            czr_gallery_collection_child : {
                  mthds : Constructor,
                  crud : true,//api.czr_sektions.getRegisteredModuleProperty( 'czr_gallery_collection_child', 'is_crud' ),
                  hasPreItem : false,//a crud module has a pre item by default
                  refresh_on_add_item : false,// the preview is refreshed on item add
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_gallery_collection_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_gallery_collection_child' ),
                  items_are_clonable : true
            },
      });
})( wp.customize , jQuery, _ );

















/* ------------------------------------------------------------------------- *
 *  GALLERY OPTIONS
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;


                  module.crudModulePart = 'nimble-crud-module-part';
                  module.rudItemPart = 'nimble-rud-item-part';

                  // //EXTEND THE DEFAULT CONSTRUCTORS FOR MONOMODEL
                  module.itemConstructor = api.CZRItem.extend( module.CZRItemConstructor || {} );

                  // module.isReady.then( function() {
                  //       if ( _.isUndefined( module.preItem ) )
                  //         return;
                  //       //specific update for the item preModel on social-icon change
                  //       module.preItem.bind( function( to, from ) {
                  //             if ( ! _.has(to, 'icon') )
                  //               return;
                  //             if ( _.isEqual( to['icon'], from['icon'] ) )
                  //               return;
                  //             module.updateItemModel( module.preItem, true );
                  //       });
                  // });
                  //SET THE CONTENT PICKER DEFAULT OPTIONS
                  //@see ::setupContentPicker()
                  // module.bind( 'set_default_content_picker_options', function( params ) {
                  //       params.defaultContentPickerOption.defaultOption = {
                  //             'title'      : '<span style="font-weight:bold">' + sektionsLocalizedData.i18n['Set a custom url'] + '</span>',
                  //             'type'       : '',
                  //             'type_label' : '',
                  //             'object'     : '',
                  //             'id'         : '_custom_',
                  //             'url'        : ''
                  //       };
                  //       return params;
                  // });

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
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

                        api.CZRItem.prototype.ready.call( item );
                  },

                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                                    


                  //Fired when the input collection is populated
                  //At this point, the inputs are all ready (input.isReady.state() === 'resolved') and we can use their visible Value ( set to true by default )
                  setInputVisibilityDeps : function() {
                        var item = this,
                            module = item.module;

                        //Internal item dependencies
                        item.czr_Input.each( function( input ) {
                              switch( input.id ) {
                                    case 'link-to' :
                                          _.each( [ 'link-target' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'link-target' :
                                                                  bool = ! _.contains( [ 'no-link', 'img-lightbox' ], input() );
                                                            break;
                                                      }
                                                      return bool;
                                                });
                                          });
                                    break;
                                    case 'custom-rows-columns' :
                                          _.each( [ 'column_width', 'raw_height' ] , function( _inputId_ ) {
                                                api.czr_sektions.scheduleVisibilityOfInputId.call( input, _inputId_, function() {
                                                      var _is_masonry_on = item.czr_Input.has('masonry_on')  &&  item.czr_Input('masonry_on')(),
                                                            _is_auto_fill_on = item.czr_Input.has('auto_fill')  &&  item.czr_Input('auto_fill')();

                                                      var bool = false;
                                                      switch( _inputId_ ) {
                                                            case 'column_width' :
                                                                  bool = input() && !_is_masonry_on && !_is_auto_fill_on;
                                                            break;
                                                            case 'raw_height' :
                                                                  bool = input() && !_is_masonry_on;
                                                            break;
                                                      }
                                                      return bool;
                                                });
                                          });
                                    break;
                              }
                        });
                  },
            },//CZRItemConstructor
      };//Constructor
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
            czr_gallery_opts_child : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'czr_gallery_opts_child', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'czr_gallery_opts_child' )
            }
      });
})( wp.customize , jQuery, _ );