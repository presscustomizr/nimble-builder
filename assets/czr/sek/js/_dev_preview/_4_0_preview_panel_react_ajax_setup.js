//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // Fired in ::initialize()
            schedulePanelMsgReactions : function() {
                  var self = this,
                      apiParams = {},
                      uiParams = {},
                      msgCollection = {
                            // DOM MODIFICATION CASES
                            'sek-add-section' : 'ajaxAddSektion',
                            'sek-add-content-in-new-sektion' : 'ajaxAddSektion',
                            'sek-add-column' : 'ajaxRefreshColumns',
                            'sek-add-module' : 'ajaxRefreshModulesAndNestedSections',
                            'sek-remove' : function( params ) {
                                  var removeCandidateId = params.apiParams.id,
                                      $candidateEl = $('div[data-sek-id="' + removeCandidateId + '"]' ),
                                      dfd;
                                  switch ( params.apiParams.action ) {
                                        case 'sek-remove-section' :
                                              if ( true === params.apiParams.is_nested ) {
                                                    dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                              } else {
                                                    if ( _.isEmpty( removeCandidateId ) || 1 > $candidateEl.length ) {
                                                          self.errare( 'reactToPanelMsg => sek-remove => invalid candidate id => ', removeCandidateId );
                                                    }
                                                    $('body').find( $candidateEl ).remove();
                                                    // say it
                                                    $('[data-sek-id="' + params.apiParams.location + '"]').trigger( 'sek-level-refreshed');
                                              }
                                              //console.log( params.apiParams.action, params );
                                              //self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        case 'sek-remove-column' :
                                              //console.log( params.apiParams.action, params );
                                              dfd = self.ajaxRefreshColumns( params );
                                        break;
                                        case 'sek-remove-module' :
                                              //console.log( params.apiParams.action, params );
                                              dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        default :
                                        break;
                                  }
                                  // We should always return a promise
                                  return _.isEmpty( dfd ) ? $.Deferred( function() { this.resolve(); } ) : dfd;
                            },

                            'sek-duplicate' : function( params ) {
                                  var dfd;
                                  switch ( params.apiParams.action ) {
                                        case 'sek-duplicate-section' :
                                              // replace the original id by the new cloneId registered in the main setting, and sent by the panel
                                              params.apiParams.id = params.cloneId;
                                              dfd = self.ajaxAddSektion( params );
                                        break;
                                        case 'sek-duplicate-column' :
                                              // replace the original id by the new cloneId registered in the main setting, and sent by the panel
                                              params.apiParams.id = params.cloneId;
                                              dfd = self.ajaxRefreshColumns( params );
                                        break;
                                        case 'sek-duplicate-module' :
                                              // replace the original id by the new cloneId registered in the main setting, and sent by the panel
                                              params.apiParams.id = params.cloneId;
                                              dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                  }
                                  return dfd;
                            },

                            // Re-print a level
                            // Can be invoked when setting the section layout option boxed / wide, when we need to add a css class server side
                            // @params {
                            //   action : 'sek-refresh-level',
                            //   level : params.level,
                            //   id : params.id
                            // }
                            'sek-refresh-level' : function( params ) {
                                  // will be cleaned on 'sek-module-refreshed'
                                  self.mayBePrintLoader({
                                        loader_located_in_level_id : params.apiParams.id
                                  });
                                  return self.doAjax( {
                                        skope_id : params.skope_id,
                                        action : 'sek_get_content',
                                        id : params.apiParams.id,
                                        level : params.apiParams.level,
                                        sek_action : params.apiParams.action
                                  }).fail( function( _r_ ) {
                                        self.errare( 'ERROR reactToPanelMsg => sek-refresh-level => ' , _r_ );
                                  }).done( function( _r_ ) {
                                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.id + '"></span>',
                                            $currentLevelEl = $( 'div[data-sek-id="' + params.apiParams.id + '"]' );
                                        if ( $currentLevelEl.length < 1 ) {
                                              self.errare( 'reactToPanelMsg => sek-refresh-level ajax done => the level to refresh is not rendered in the page', _r_ );
                                              return;
                                        }
                                        $currentLevelEl.before( placeholderHtml );
                                        var $placeHolder = $( '[data-sek-placeholder-for="' + params.apiParams.id + '"]' );
                                        $currentLevelEl.remove();
                                        $placeHolder.after( _r_.data );
                                        $placeHolder.remove();

                                        $( '[data-sek-id="' + params.apiParams.id + '"]' )
                                              .trigger( 'sek-refresh-level', { level : params.apiParams.level, id : params.apiParams.id } );
                                  });
                            },

                            //'sek-set-level-options' : 'ajaxRefreshStylesheet',
                            'sek-refresh-stylesheet' : 'ajaxRefreshStylesheet',





                            // EDITING MODULE AND OPTIONS
                            'sek-move' : function( params ) {
                                  switch ( params.apiParams.action ) {
                                        // case 'sek-move-section' :
                                        //       //always re-render the source sektion and target sektion if different
                                        //       //=> this will ensure a reset of the column's widths
                                        //       if ( params.apiParams.from_location != params.apiParams.to_location ) {
                                        //             var paramsForSourceSektion = $.extend( true, {}, params );
                                        //             var paramsForTargetSektion = $.extend( true, {}, params );

                                        //             // SOURCE SEKTION
                                        //             // if the source sektion has been emptied, let's populate it with a new column
                                        //             if ( $('[data-sek-id="' + params.apiParams.from_sektion +'"]', '.sektion-wrapper').find('div[data-sek-level="column"]').length < 1 ) {
                                        //                   api.preview.send( 'sek-add-column', {
                                        //                         in_sektion : params.apiParams.from_sektion,
                                        //                         autofocus:false//<= because we want to focus on the column that has been moved away from the section
                                        //                   });
                                        //             } else {
                                        //                   paramsForSourceSektion.apiParams =  _.extend( paramsForSourceSektion.apiParams, {
                                        //                         in_sektion : params.apiParams.from_sektion,
                                        //                         action : 'sek-refresh-columns-in-sektion'
                                        //                   });
                                        //                   self.ajaxRefreshColumns( paramsForSourceSektion );
                                        //             }

                                        //             // TARGET SEKTION
                                        //             paramsForTargetSektion.apiParams =  _.extend( paramsForTargetSektion.apiParams, {
                                        //                   in_sektion : params.apiParams.to_sektion,
                                        //                   action : 'sek-refresh-columns-in-sektion'
                                        //             });
                                        //             self.ajaxRefreshColumns( paramsForTargetSektion );

                                        //       }
                                        // break;
                                        case 'sek-move-column' :
                                              //always re-render the source sektion and target sektion if different
                                              //=> this will ensure a reset of the column's widths
                                              if ( params.apiParams.from_sektion != params.apiParams.to_sektion ) {
                                                    var paramsForSourceSektion = $.extend( true, {}, params );
                                                    var paramsForTargetSektion = $.extend( true, {}, params );

                                                    // SOURCE SEKTION
                                                    // if the source sektion has been emptied, let's populate it with a new column
                                                    if ( $('[data-sek-id="' + params.apiParams.from_sektion +'"]', '.sektion-wrapper').find('div[data-sek-level="column"]').length < 1 ) {
                                                          api.preview.send( 'sek-add-column', {
                                                                in_sektion : params.apiParams.from_sektion,
                                                                autofocus:false//<= because we want to focus on the column that has been moved away from the section
                                                          });
                                                    } else {
                                                          paramsForSourceSektion.apiParams =  _.extend( paramsForSourceSektion.apiParams, {
                                                                in_sektion : params.apiParams.from_sektion,
                                                                action : 'sek-refresh-columns-in-sektion'
                                                          });
                                                          self.ajaxRefreshColumns( paramsForSourceSektion );
                                                    }

                                                    // TARGET SEKTION
                                                    paramsForTargetSektion.apiParams =  _.extend( paramsForTargetSektion.apiParams, {
                                                          in_sektion : params.apiParams.to_sektion,
                                                          action : 'sek-refresh-columns-in-sektion'
                                                    });
                                                    self.ajaxRefreshColumns( paramsForTargetSektion );

                                              }
                                        break;
                                        case 'sek-move-module' :
                                              var paramsForSourceColumn = $.extend( true, {}, params ),
                                                  paramsForTargetColumn = $.extend( true, {}, params );
                                              // SOURCE COLUMN
                                              //always re-render the source column if different than the target column
                                              //=> this will ensure that we have the drop-zone placeholder printed for a no-module column
                                              //+ will refresh the sortable()
                                              if ( paramsForSourceColumn.apiParams.from_column != paramsForSourceColumn.apiParams.to_column ) {
                                                    paramsForSourceColumn.apiParams = _.extend( paramsForSourceColumn.apiParams, {
                                                          in_column : paramsForSourceColumn.apiParams.from_column,
                                                          in_sektion : paramsForSourceColumn.apiParams.from_sektion,
                                                          action : 'sek-refresh-modules-in-column'
                                                    });
                                                    self.ajaxRefreshModulesAndNestedSections( paramsForSourceColumn );
                                              }

                                              // TARGET COLUMN
                                              params.apiParams = _.extend( paramsForTargetColumn.apiParams, {
                                                    in_column : paramsForTargetColumn.apiParams.to_column,
                                                    in_sektion : paramsForTargetColumn.apiParams.to_sektion,
                                                    action : 'sek-refresh-modules-in-column'
                                              });
                                              self.ajaxRefreshModulesAndNestedSections( paramsForTargetColumn );

                                              // Re-instantiate sortable for the target column
                                              $('[data-sek-id="' + params.apiParams.to_column +'"]', '.sektion-wrapper').find('.sek-column-inner').sortable( "refresh" );
                                        break;
                                  }
                            },

                            'sek-resize-columns' : 'ajaxResizeColumns',




                            // GENERATE UI ELEMENTS
                            // when the options ui has been generated in the panel for a level, we receive back this msg
                            // 'sek-generate-level-options-ui' : function( params ) {
                            //       console.log('PANEL REACT? ', 'sek-generate-level-options-ui', params );
                            // },

                            'sek-edit-options' : function( params ) {
                                  // ::activeLevelUI is declared in ::initialized()
                                  self.activeLevelUI( params.uiParams.id );
                            },
                            'sek-edit-module' : function( params ) {
                                  // ::activeLevelUI is declared in ::initialized()
                                  self.activeLevelUI( params.uiParams.id );
                            },

                            // @params =  {
                            //   skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
                            //   apiParams : apiParams,
                            //   uiParams : uiParams
                            // }
                            // uiParams = {
                            //       action : 'sek-edit-module',
                            //       level : params.level,
                            //       id : params.id,
                            //       in_sektion : params.in_sektion,
                            //       in_column : params.in_column,
                            //       options : params.options || []
                            // };
                            //
                            // when the module ui has been generated in the panel, we receive back this msg
                            //'sek-generate-module-ui' : function( params ) {},

                            //@params { type : module || preset_section }
                            'sek-drag-start' : function( params ) {
                                  //console.log('PARAMS in sek-drag-start', params, $('.sektion-wrapper').children('[data-sek-level="section"]').length );
                                  // append the drop zones between sections
                                  var i = 1;
                                  $('.sektion-wrapper').children('[data-sek-level="section"]').each( function() {
                                        // Always before
                                        if ( $('[data-drop-zone-before-section="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                              $(this).before(
                                                '<div class="sek-content-' + params.type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-sections" data-drop-zone-before-section="' + $(this).data('sek-id') +'"></div>'
                                              );
                                        }
                                        // After the last one
                                        if (  i == $('.sektion-wrapper').children('[data-sek-level="section"]').length ) {
                                              $(this).after(
                                                '<div class="sek-content-' + params.type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-sections" data-drop-zone-after-section="' + $(this).data('sek-id') +'"></div>'
                                              );
                                        }
                                        i++;
                                  });

                                  // Append the drop zone in empty locations
                                  $('.sek-empty-location-placeholder').each( function() {
                                        //console.log('SEK-DRAG-START', params );
                                        $.when( $(this).append(
                                              '<div class="sek-content-' + params.type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="in-empty-location"></div>'
                                        ));
                                  });

                                  // Append a drop zone between modules in columns
                                  if ( 'module' ==  params.type ) {
                                        $('[data-sek-level="column"]').each( function() {
                                              var $modules = $(this).find('[data-sek-level="module"]');
                                              // if ( $modules.length < 2 )
                                              //   return;
                                              var j = 1;
                                              $modules.each( function() {
                                                    // Always before
                                                    if ( $('[data-drop-zone-before-module="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                                          $(this).before(
                                                              '<div class="sek-content-module-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-modules" data-drop-zone-before-module="' + $(this).data('sek-id') +'"></div>'
                                                          );
                                                    }
                                                    // After the last one
                                                    if (  j == $modules.length && $('[data-drop-zone-after-module="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                                          $(this).after(
                                                            '<div class="sek-content-module-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-modules" data-drop-zone-after-module="' + $(this).data('sek-id') +'"></div>'
                                                          );
                                                    }
                                                    j++;
                                              });
                                        });
                                  }


                                  // toggle a parent css classes controlling some css rules @see preview.css
                                  $('body').addClass('sek-dragging');

                                  // Reveal all dynamic dropzones after a delay
                                  _.delay( function() {
                                        $('.sek-dynamic-drop-zone').css({ opacity : 1 });
                                  }, 100 );

                            },
                            // is sent on dragend and drop
                            'sek-drag-stop' : function( params ) {
                                  $('body').removeClass('sek-dragging');
                                  // Clean any remaining placeholder
                                  $('.sortable-placeholder').remove();

                                  // Remove the drop zone dynamically add on sek-drag-start
                                  $('.sek-dynamic-drop-zone').remove();
                            },


                            // FOCUS
                            'sek-focus-on' : function( params ) {
                                  var $elToFocusOn = $('div[data-sek-id="' + params.id + '"]' );
                                  if ( $elToFocusOn.length > 0 ) {
                                        $('html, body').animate({
                                              scrollTop : $('div[data-sek-id="' + params.id + '"]' ).offset().top - 100
                                        }, 'slow');
                                  }
                            }

                      };//msgCollection

                  _.each( msgCollection, function( callbackFn, msgId ) {
                        api.preview.bind( msgId, function( params ) {
                              params = _.extend( {
                                  skope_id : '',
                                  apiParams : {},
                                  uiParams : {}
                              }, params || {} );

                              if ( _.isFunction( callbackFn ) ) {
                                    try {
                                          $.when( callbackFn( params ) ).done( function() {
                                                api.preview.send( [ msgId, 'done'].join('_'), params );
                                          }).fail( function() {
                                                api.preview.send( 'sek-notify', { type : 'error', duration : 10000, message : sekPreviewLocalized.i18n['Something went wrong, please refresh this page.'] });
                                          }).then( function() {
                                                api.preview.trigger( 'control-panel-requested-action-done', { action : msgId, args : params } );
                                          });
                                    } catch( _er_ ) {
                                          self.errare( 'reactToPanelMsg => Error when firing the callback of ' + msgId , _er_  );
                                    }
                              } else {
                                    try {
                                          $.when( self[callbackFn].call( self, params ) ).done( function() {
                                                api.preview.send( [ msgId, 'done'].join('_'), params );
                                          }).fail( function() {
                                                api.preview.send( 'sek-notify', { type : 'error', duration : 10000, message : sekPreviewLocalized.i18n['Something went wrong, please refresh this page.'] });
                                          }).then( function() {
                                                api.preview.trigger( 'control-panel-requested-action-done', { action : msgId, args : params } );
                                          });
                                    } catch( _er_ ) {
                                          self.errare( 'reactToPanelMsg => Error when firing the callback of ' + msgId , _er_  );
                                    }
                              }


                        });
                  });
            }//schedulePanelMsgReactions()
      });//$.extend()
})( wp.customize, jQuery, _ );
