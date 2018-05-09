
var CZRFlatSkopeModuleMths = CZRFlatSkopeModuleMths || {};

( function ( api, $, _ ) {
      $.extend( CZRFlatSkopeModuleMths, {
            // fired in module::initialize
            scheduleSkopeReactions : function() {
                  var module = this;

                  // @params looks like :
                  // {
                  //  newSkopes : newSkopes,
                  //  previousSkopes : previousSkopes
                  // }
                  api.bind( 'active-skopes-updated', function( params ) {
                        console.log( 'active-skopes-updated', params, module.SKOPE_LEVEL );
                        params = _.extend( { newSkopes : {}, previousSkopes : {} }, params );
                        if ( ! _.has( params.newSkopes, module.SKOPE_LEVEL  ) ) {
                              api.errorLog( 'active-skopes-updated => ' + module.SKOPE_LEVEL + ' was not found in the skope list' );
                              return;
                        } else {
                              module.isReady.then( function() {
                                    // Always update the local skope-id in the pre-item
                                    if ( module.preItem && ! _.isEmpty( module.preItem.czr_Input ) && module.preItem.czr_Input.has('skope-id') ) {
                                          module.preItem.czr_Input( 'skope-id' )( params.newSkopes[ module.SKOPE_LEVEL ] );
                                    }
                                    // Print the contextual items only
                                    //module.refreshContextualItems();


                                    // Display the parent control when relevant
                                    module.control.active( '_skope_not_set_' != params.newSkopes[ module.SKOPE_LEVEL ] );
                              });

                              // Print a bottom notification in all controls wrapper
                              //module.printSkopeInfoInControls();
                        }
                  });
            },

            //This method react to the skope change
            // => reinstantiates all items based on the current collection
            // refreshContextualItems : function() {
            //       var module = this;
            //       //Remove item views and instances
            //       module.czr_Item.each( function( _itm ) {
            //             if ( ! _.isEmpty( _itm.container ) && 0 < _itm.container.length ) {
            //                   $.when( _itm.container.remove() ).done( function() {
            //                         //Remove item instances
            //                         module.czr_Item.remove( _itm.id );
            //                   });
            //             } else {
            //                   //Remove item instances
            //                   module.czr_Item.remove( _itm.id );
            //             }
            //       });

            //       // Reset the item collection
            //       // => the collection listeners will be setup after populate, on 'items-collection-populated'
            //       var _collection_ = $.extend( true, [], module.itemCollection() );

            //       // reset the collection
            //       module.itemCollection = new api.Value( [] );
            //       module.populateSavedItemCollection( _collection_ );
            // },



            // // invoked when module.ready() and api.czr_activeSkopes updated
            // printSkopeInfoInControls : function() {
            //       var module = this,
            //           _css_class_ = 'skp-note-for-' + module.SKOPE_LEVEL;

            //       //This = _ctrl_
            //       var mayBePrintAndSetupCtrlBottomNote = function() {
            //             var _ctrl_ = this,
            //                 _skope_title_ =  FlatSkopeLocalizedData.i18n[ 'this page' ];

            //             if ( 0 < _ctrl_.container.find( '.' + _css_class_ ).length )
            //               return;

            //             var currentSkopeObj = _.findWhere( api.czr_currentSkopesCollection(), { skope : module.SKOPE_LEVEL } );
            //             if ( ! _.isEmpty( currentSkopeObj ) ) {
            //                   _skope_title_ = currentSkopeObj.ctx_title;
            //             }


            //             _ctrl_.container.append(
            //                   $('<div>', {
            //                       class: [ _css_class_ , 'skp-ctrl-bottom-note', 'czr-notice' ].join(' '),
            //                       html : [
            //                             FlatSkopeLocalizedData.i18n[ 'Can be customized for' ],
            //                             '<span class="skp-focus-link"><u>' + _skope_title_ + '</u><span>'
            //                       ].join(' ')
            //                   })
            //             );

            //             api.CZR_Helpers.setupDOMListeners(
            //                   [//toggles remove view alert
            //                         {
            //                               trigger   : 'click keydown',
            //                               selector  : ['.' + _css_class_, '.skp-focus-link'].join(' '),
            //                               name      : _css_class_,
            //                               //data = {
            //                               //       dom_el,
            //                               //       dom_event,
            //                               //       event,
            //                               //       model
            //                               // }
            //                               actions   : function( data ) {
            //                                     data.dom_event.preventDefault();
            //                                     module.control.focus();
            //                               }
            //                         }//actions to execute
            //                   ],
            //                   { model: {}, dom_el:_ctrl_.container },//model + dom scope
            //                   null //instance where to look for the cb methods

            //             );//api.CZR_Helpers.setupDOMListeners
            //       };//mayBePrintAndSetupCtrlBottomNote

            //       _.each( api.czr_skopeBase.getSkopableSettings(), function( settingData ) {
            //             //console.log('settingDATA', settingData );
            //             api.control.when( settingData.apiCtrlId, function( _ctrl_ ) {
            //                   _ctrl_.deferred.embedded.then( function() {
            //                         $.when( _ctrl_.container.find('.' + _css_class_ ).remove() ).done( function() {
            //                               if ( module.weHaveASkope() ) {
            //                                     mayBePrintAndSetupCtrlBottomNote.call( _ctrl_ );
            //                               }
            //                         });
            //                   });
            //             } );
            //       });
            // }//printSkopeInfoInControls
      });//extend
})( wp.customize , jQuery, _ );