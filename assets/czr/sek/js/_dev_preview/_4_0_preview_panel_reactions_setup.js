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
                            'sek-add-content-in-new-nested-sektion' : 'ajaxAddSektion',
                            'sek-add-column' : 'ajaxRefreshColumns',
                            'sek-add-module' : 'ajaxRefreshModulesAndNestedSections',
                            'sek-refresh-stylesheet-in-the-background-for-custom-css' : 'ajaxRefreshStylesheet',
                            'sek-refresh-stylesheet' : 'ajaxRefreshStylesheet',

                            'sek-resize-columns' : 'ajaxResizeColumns',

                            'sek-maybe-print-loader' : function( params ) {
                                  try { self.mayBePrintLoader( params ); } catch( er ) {
                                        api.errare( 'sek-print-loader => error', er );
                                  }
                            },
                            'sek-clean-loader' : function( params ) {
                                  try { self.cleanLoader( params ); } catch( er ) {
                                        api.errare( 'sek-clean-loader => error', er );
                                  }
                            },
                            'sek-remove' : function( params ) {
                                  var removeCandidateId = params.apiParams.id,
                                      $candidateEl = $('div[data-sek-id="' + removeCandidateId + '"]' ),
                                      dfd;
                                  switch ( params.apiParams.action ) {
                                        case 'sek-remove-section' :
                                              // will be cleaned on ajax.done()
                                              // @see ::scheduleTheLoaderCleaning
                                              self.mayBePrintLoader({
                                                    loader_located_in_level_id : params.apiParams.location
                                              });
                                              if ( true === params.apiParams.is_nested ) {
                                                    dfd = self.ajaxRefreshModulesAndNestedSections( params );
                                              } else {
                                                    if ( _.isEmpty( removeCandidateId ) || 1 > $candidateEl.length ) {
                                                          self.errare( 'reactToPanelMsg => sek-remove => invalid candidate id => ', removeCandidateId );
                                                    }
                                                    self.cachedElements.$body.find( $candidateEl ).remove();
                                                    // say it
                                                    // listened to clean the loader just in time
                                                    $('[data-sek-id="' + params.apiParams.location + '"]').trigger( 'sek-level-refreshed');
                                              }
                                              //self.ajaxRefreshModulesAndNestedSections( params );
                                        break;
                                        case 'sek-remove-column' :
                                              dfd = self.ajaxRefreshColumns( params );
                                        break;
                                        case 'sek-remove-module' :
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
                                  return self.doAjax({
                                        location_skope_id : params.location_skope_id,
                                        local_skope_id : params.local_skope_id,
                                        action : 'sek_get_content',
                                        id : params.apiParams.id,
                                        level : params.apiParams.level,
                                        sek_action : params.apiParams.action
                                  }).fail( function( _r_ ) {
                                        self.errare( 'ERROR reactToPanelMsg => sek-refresh-level => ' , _r_ );
                                        $( '[data-sek-id="' + params.apiParams.id + '"]' ).trigger( 'sek-ajax-error' );
                                  }).done( function( _r_ ) {
                                        var html_content = '';
                                        //@see php SEK_Front_Ajax::sek_get_level_content_for_injection
                                        if ( _r_.data && _r_.data.contents ) {
                                              html_content = _r_.data.contents;
                                        } else {
                                              self.errare( 'SekPreviewPrototype::sek-refresh-level => ajax_response.data.contents is undefined ', _r_ );
                                              self.errare( 'params ?', params );
                                        }
                                        // _r_ is an array
                                        // @see SEK_Front_Ajax::sek_get_level_content_for_injection
                                        // _r_ = array(
                                        //     'contents' => $html,
                                        //     'setting_validities' => $exported_setting_validities
                                        // );
                                        var placeholderHtml = '<span class="sek-placeholder" data-sek-placeholder-for="' + params.apiParams.id + '"></span>',
                                            $currentLevelEl = $( 'div[data-sek-id="' + params.apiParams.id + '"]' );
                                        if ( $currentLevelEl.length < 1 ) {
                                              self.errare( 'reactToPanelMsg => sek-refresh-level ajax done => the level to refresh is not rendered in the page', _r_ );
                                              return;
                                        }
                                        $currentLevelEl.before( placeholderHtml );
                                        var $placeHolder = $( '[data-sek-placeholder-for="' + params.apiParams.id + '"]' );

                                        $currentLevelEl.remove();

                                        if ( _.isUndefined( html_content ) ) {
                                              self.errare( 'reactToPanelMsg => sek-refresh-level ajax done => missing html_content', _r_ );
                                        } else {
                                              $placeHolder.after( html_content );
                                        }
                                        if ( $placeHolder.length > 0 ) {
                                          $placeHolder.remove();
                                        }

                                        //=> 'sek-level-refreshed' is listened to ( for example ) clean the loader overlay in time
                                        $( '[data-sek-id="' + params.apiParams.id + '"]' ).trigger( 'sek-level-refreshed', { level : params.apiParams.level, id : params.apiParams.id } );

                                        // When completing actions 'sek-move-section-down' && 'sek-move-section-up', a 'sek-refresh-level' is triggered.
                                        // We pass the moved_level_id so we can focus on it after it's been re-located in the DOM
                                        // implemented for https://github.com/presscustomizr/nimble-builder/issues/471
                                        if ( params.apiParams.moved_level_id ) {
                                              api.preview.trigger( 'sek-animate-to-level', { id : params.apiParams.moved_level_id } );
                                        }
                                  });
                            },






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





                            // GENERATE UI ELEMENTS
                            // when the options ui has been generated in the panel for a level, we receive back this msg
                            // 'sek-generate-level-options-ui' : function( params ) {
                            //       api.infoLog('PANEL REACT? ', 'sek-generate-level-options-ui', params );
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
                            //   location_skope_id : api.czr_skopeBase.getSkopeProperty( 'skope_id' ),//<= send skope id to the preview so we can use it when ajaxing
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

                            //@params {
                            //    content_type : module || preset_section,
                            //    eligible_for_module_dropzones : boolean //<= typically useful for multicolumn "modules" that are in reality preset_section @see https://github.com/presscustomizr/nimble-builder/issues/540
                            // }
                            'sek-drag-start' : function( params ) {
                                  // append the drop zones between sections
                                  var i = 1, previousSectionIsEmpty = false;
                                  $('[data-sek-level="location"]').children('[data-sek-level="section"]').each( function() {
                                        var sectionId = $(this).data('sek-id'),
                                            columnNb = $(this).find('[data-sek-level="column"]').length,
                                            moduleNb = $(this).find('[data-sek-level="module"]').length,
                                            isEmptySection = columnNb < 2 && moduleNb < 1,
                                            canPrintBefore = ! previousSectionIsEmpty && ! isEmptySection;

                                        // Print a dropzone before if the previous section and current section are not empty.
                                        if ( canPrintBefore && $('[data-drop-zone-before-section="' + sectionId +'"]').length < 1 ) {
                                              $(this).before(
                                                '<div class="sek-content-' + params.content_type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-sections" data-drop-zone-before-section="' + sectionId +'"></div>'
                                              );
                                        }
                                        // After the last one
                                        if ( ! isEmptySection && i == $('.sektion-wrapper').children('[data-sek-level="section"]').length ) {
                                              $(this).after(
                                                '<div class="sek-content-' + params.content_type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-sections" data-drop-zone-after-section="' + sectionId +'"></div>'
                                              );
                                        }
                                        i++;
                                        previousSectionIsEmpty = isEmptySection;
                                  });

                                  // Append the drop zone in empty locations
                                  $('.sek-empty-location-placeholder').each( function() {
                                        $.when( $(this).append(
                                              '<div class="sek-content-' + params.content_type + '-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="in-empty-location"></div>'
                                        ));
                                  });

                                  // Append a drop zone between modules and nested sections in columns
                                  // preset_sections like multicolumn structure are part of the module list
                                  // they fall under the second part of the conditional statement below
                                  // introduced for https://github.com/presscustomizr/nimble-builder/issues/540
                                  if ( 'module' == params.content_type || ( 'preset_section' == params.content_type && true === params.eligible_for_module_dropzones ) ) {
                                        $('[data-sek-level="column"]').each( function() {
                                              // Our candidates are the modules and nested section which are direct children of this column
                                              // We don't want to include the modules inserted in the columns of a nested section.
                                              var $modules_and_nested_sections = $(this).children('.sek-column-inner').children( '[data-sek-level="module"]' );
                                              var $nested_sections = $(this).children('.sek-column-inner').children( '[data-sek-is-nested="true"]' );
                                              $modules_and_nested_sections = $modules_and_nested_sections.add( $nested_sections );

                                              var j = 1;
                                              $modules_and_nested_sections.each( function() {
                                                    // Always before
                                                    if ( $('[data-drop-zone-before-module-or-nested-section="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                                          $(this).before(
                                                              '<div class="sek-content-module-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-modules-and-nested-sections" data-drop-zone-before-module-or-nested-section="' + $(this).data('sek-id') +'"></div>'
                                                          );
                                                    }
                                                    // After the last one
                                                    if (  j == $modules_and_nested_sections.length && $('[data-drop-zone-after-module-or-nested-section="' + $(this).data('sek-id') +'"]').length < 1 ) {
                                                          $(this).after(
                                                            '<div class="sek-content-module-drop-zone sek-dynamic-drop-zone sek-drop-zone" data-sek-location="between-modules-and-nested-sections" data-drop-zone-after-module-or-nested-section="' + $(this).data('sek-id') +'"></div>'
                                                          );
                                                    }
                                                    j++;
                                              });
                                        });
                                  }

                                  // toggle a parent css classes controlling some css rules @see preview.css
                                  self.cachedElements.$body.addClass('sek-dragging');

                                  // Reveal all dynamic dropzones after a delay
                                  _.delay( function() {
                                        $('.sek-dynamic-drop-zone').css({ opacity : 1 });
                                  }, 100 );

                            },
                            // is sent on dragend and drop
                            'sek-drag-stop' : function( params ) {
                                  self.cachedElements.$body.removeClass('sek-dragging');
                                  // Clean any remaining placeholder
                                  $('.sortable-placeholder').remove();

                                  // Remove the drop zone dynamically add on sek-drag-start
                                  $('.sek-dynamic-drop-zone').remove();
                            },















                            // FOCUS
                            // Sent from the panel when duplicating a section level for example
                            // focus on a level
                            'sek-animate-to-level' : function( params ) {
                                    var $elToFocusOn = $('[data-sek-id="' + params.id + '"]' );
                                    if ( 0 < $elToFocusOn.length && !nb_.isInScreen( $elToFocusOn[0]) ) {
                                          $elToFocusOn[0].scrollIntoView({
                                                behavior: 'auto',
                                                block: 'center',
                                                inline: 'center'
                                          });
                                    }
                                    // if ( $elToFocusOn.length > 0 ) {
                                    //       console.log( 'EL IN WINDOW ?', nb_.elOrFirstVisibleParentIsInWindow( $elToFocusOn ), $elToFocusOn );
                                    //       $elToFocusOn[0].scrollIntoView();
                                    //       $('html, body').animate({
                                    //             scrollTop : $elToFocusOn.offset().top - 100
                                    //       }, 200 );
                                    // }
                            },


                            // LEVEL UI's
                            'sek-clean-level-uis' : function( params ) {
                                  $('.sek-dyn-ui-wrapper').each( function() {
                                        $(this).remove();
                                  });
                            },
                            // triggered when navigating the level tree
                            'sek-display-level-ui' : function( params ) {
                                  var $elToFocusOn = $('[data-sek-id="' + params.id + '"]' );
                                  if ( $elToFocusOn.length > 0 ) {
                                        //$elToFocusOn.trigger('click'); //<= the click is not needed anymore since June 2019, we trigger the generation of the level options on 'click' in the level tree
                                        self.printLevelUI($elToFocusOn);
                                  }
                            },


                            // DOUBLE CLICK INSERTION => HIGHLIGHTED TARGET
                            // implemented for double-click insertion
                            // https://github.com/presscustomizr/nimble-builder/issues/317
                            'sek-set-double-click-target' : function( params ) {
                                  // First clean any other highlighted target
                                  $('.sek-target-for-double-click-insertion').removeClass('sek-target-for-double-click-insertion');

                                  if ( _.isObject( params ) && params.id ) {
                                        var $elToHighlight = $('[data-sek-id="' + params.id + '"]' );
                                        if( 1 === $elToHighlight.length ) {
                                              $elToHighlight.addClass('sek-target-for-double-click-insertion');
                                        }
                                  }
                            },
                            'sek-reset-double-click-target' : function( params ) {
                                  $('.sek-target-for-double-click-insertion').removeClass('sek-target-for-double-click-insertion');
                            },

                            // introduced for https://github.com/presscustomizr/nimble-builder/issues/403
                            // this is fired for module with postMessage refresh, like text editor
                            // @see control::refreshMarkupWhenNeededForInput()
                            // July 2019 => since the new UI rendering with JS template ( https://github.com/presscustomizr/nimble-builder/issues/465 ), this action is fired too early when inserting a new module with postMessage refresh
                            // resulting in the target element not being rendered on first call
                            'sek-update-html-in-selector' : function( params ) {
                                  var $level_el = $('[data-sek-id="' + params.id + '"]' ),
                                      $target_el;

                                  // for multi-item modules, the changed item id is passed
                                  if ( !_.isEmpty( params.changed_item_id ) ) {
                                        // if a selector is provided in param 'refresh_markup'
                                        if ( params.selector ) {
                                          $target_el = $( '[data-sek-item-id="' + params.changed_item_id + '"] ' + params.selector, $level_el);
                                        } else {
                                                $target_el = $( '[data-sek-item-id="' + params.changed_item_id + '"]', $level_el);
                                          }
                                  } else {
                                        $target_el = $(params.selector, $level_el);
                                  }

                                  if ( $level_el.length > 0 && $target_el.length > 0 ) {
                                        $target_el.html( params.html );
                                  } else {
                                        self.errare( 'reactToPanelMsg => sek-update-html-in-selector => missing level or target dom element', params );
                                  }
                            },
                             // introduced for CUSTOM CSS see https://github.com/presscustomizr/nimble-builder-pro/issues/201
                            // fired when refresh_css_via_post_message = true in input registration params
                            'sek-update-css-with-postmessage' : function( params ) {
                                    //console.log('ALORS PARAMS ?', params );
                                    if ( _.isUndefined(params.css_content) || !_.isString(params.css_content) ) {
                                          self.errare( 'error => sek-update-css-with-postmessage => css content is not a string' );
                                          return;
                                    }

                                    // Comments removal
                                    // see https://stackoverflow.com/questions/5989315/regex-for-match-replacing-javascript-comments-both-multiline-and-inline
                                    params.css_content = params.css_content.replace(/\/\*.+?\*\/|\/\/.*(?=[\n\r])/g, '');

                                    var custom_css_sel,
                                          $custom_css_el,
                                          _level_selector;

                                    if ( params.is_current_page_custom_css ) {
                                          custom_css_sel = 'nb-custom-css-for-local-page';
                                          _level_selector = '';
                                    } else {
                                          custom_css_sel = 'nb-custom-css-for-level' + params.id;
                                          _level_selector = 'body .sektion-wrapper [data-sek-id="' + params.id +'"]';
                                    }
                                    $custom_css_el = $('#'  + custom_css_sel );

                                    if ( $custom_css_el.length < 1 ) {
                                          $('head').append( $('<style/>' , {
                                                id : custom_css_sel,
                                          }) );
                                          $custom_css_el = $('#'  + custom_css_sel );
                                    }

                                    // Apply the same treatment made server side in sek_add_css_rules_for_level_custom_css()
                                    var _exploded_rules,
                                          _rules_with_level_specificity = [],
                                          _specific_rules = '',
                                          _rule_selectors,
                                          _rule_selectors_without_space,
                                          _comma_exploded_selectors;

                                    // 1) FIRST => Explode by } and add level specificity
                                    _exploded_rules = params.css_content.split('}');

                                    _.each( _exploded_rules, function( _rule ){
                                          // remove all line breaks
                                          _rule = _rule.replace(/\n|\r/g, "" );
                                          if ( _.isEmpty(_rule) || -1 === _rule.indexOf('{') )
                                                return;
                                          _rule = _rule.replace(/.nimble-level/g, '' );
                                          _rule_selectors = _rule.substr(0, _rule.indexOf('{'));
                                          _rule_selectors_without_space = _rule_selectors.replace(/ /g,'');

                                          _rule = _rule.replace(_rule_selectors, '' );
                                          // If no selectors specified, simply use the level selector
                                          if ( _.isEmpty( _rule_selectors_without_space ) ) {
                                                _rules_with_level_specificity.push(_level_selector + _rule + '}css_delimiter');
                                          } else {
                                                // => handle selectors separated by commas, in this case, the previous treatment has only added specificity to the first selector of the list
                                                _comma_exploded_selectors = _rule_selectors.split(',');
                                                _.each( _comma_exploded_selectors, function( _sel ){
                                                      if ( _.isEmpty(_sel) )
                                                            return;
                                                      _rules_with_level_specificity.push(_level_selector + ' ' + _sel + _rule + '}css_delimiter');
                                                });
                                          }
                                    });

                                    if ( !_.isEmpty(_rules_with_level_specificity) ) {
                                          _specific_rules = _rules_with_level_specificity.join('css_delimiter');
                                          _specific_rules = _specific_rules.replace(/css_delimiter/g, '' );
                                    }

                                    // If there are no rules to write, refresh the stylesheet ajaxily ( because it might contain previous CSS rules that should be cleaned up )
                                    // + remove the custom CSS style element
                                    if ( !_.isEmpty( _specific_rules.replace(/ /g, '' ) ) ) {
                                          $custom_css_el.html( _specific_rules );
                                    } else {
                                          $custom_css_el.remove();
                                    }
                                    params = $.extend({}, true, params );
                                    params.dont_print_loader = true;
                                    // Let's refresh the stylesheet ajaxily in the background to make sure that previously saved rules do not override new ones
                                    api.preview.trigger('sek-refresh-stylesheet-in-the-background-for-custom-css', params);
                              },
                              // march 2020 : print confettis when displaying the review request
                              'sek-print-confettis' : function( params ) {
                                  if (!window.confetti || !window.requestAnimationFrame)
                                    return;
                                  params = params || {};
                                  var end = params.duration || Date.now() + (1 * 1000);
                                  var colors = ['#f18700', '#684F2F', '#eea236'];

                                  (function frame() {
                                    confetti({
                                      particleCount: 2,
                                      angle: 60,
                                      spread: 55,
                                      origin: {
                                        x: 0,
                                        y: 0.8
                                      },
                                      colors: colors
                                    });
                                    confetti({
                                      particleCount: 4,
                                      angle: 120,
                                      spread: 55,
                                      origin: {
                                        x: 1,
                                        y: 0.8
                                      },
                                      colors: colors
                                    });

                                    if (Date.now() < end) {
                                      window.requestAnimationFrame(frame);
                                    }
                                  }());
                            }
                      };//msgCollection




                  var $_activeElement; // <= will be used to cache self.activeLevelEl()

                  var _apiPreviewCallback = function( params, callbackFn, msgId ) {
                        params = _.extend( {
                            location_skope_id : '',
                            apiParams : {},
                            uiParams : {}
                        }, params || {} );

                        // If the ajax response is an array formed this way ( @see sek-refresh-level case ) :
                        // @see SEK_Front_Ajax::sek_get_level_content_for_injection
                        // _ajaxResponse_ = array(
                        //     'contents' => $html,
                        //     'setting_validities' => $exported_setting_validities
                        // );
                        // Then we send an additional setting-validity message to the control panel
                        // This is the same mechanism used by WP to handle the setting validity of the partial refresh

                        var sendSuccessDataToPanel = function( _ajaxResponse_ ) {
                              // always send back the {msgId}_done message, so the control panel can fire the "complete" callback.
                              // @see api.czr_sektions::reactToPreviewMsg
                              api.preview.send( [ msgId, 'done'].join('_'), params );

                              // For multi-items module, when the level is refreshed, we want to focus on the changed_item
                              // @see CZRSeksPrototype::doSektionThinksOnApiReady
                              if ( params.apiParams.is_multi_items && params.apiParams.action === 'sek-refresh-level' ) {
                                    api.preview.send( 'multi-items-module-refreshed', params );
                              }

                              if ( _.isUndefined( _ajaxResponse_ ) )
                                return;

                              if ( _ajaxResponse_.data && _ajaxResponse_.data.setting_validities ) {
                                    api.preview.send( 'selective-refresh-setting-validities', _ajaxResponse_.data.setting_validities );
                              }
                        };
                        // the action being processed is added as a css class to the body of the preview
                        // it's used to enable/disable specific css properties during the action
                        // for example, we don't want css transitions while duplicating or removing a column
                        self.cachedElements.$body.addClass( msgId );
                        try {
                              $.when( _.isFunction( callbackFn ) ? callbackFn( params ) : self[callbackFn].call( self, params ) )
                                    .done( function( _ajaxResponse_ ) {
                                          sendSuccessDataToPanel( _ajaxResponse_ );
                                    })
                                    .fail( function() {
                                          api.preview.send( 'sek-notify', { type : 'error', duration : 10000, message : sekPreviewLocalized.i18n['Something went wrong, please refresh this page.'] });
                                    })
                                    .always( function( _ajaxResponse_ ) {
                                          self.cachedElements.$body.removeClass( msgId );
                                    })
                                    .then( function() {
                                          api.preview.trigger( 'control-panel-requested-action-done', { action : msgId, args : params } );
                                          // Focus on the edited level
                                          self.mayBeAnimateToEditedLevel( params );
                                    });
                        } catch( _er_ ) {
                              self.errare( 'reactToPanelMsg => Error when firing the callback of ' + msgId , _er_  );
                              self.cachedElements.$body.removeClass( msgId );
                        }

                        // set the activeElement if needed/possible
                        if ( _.isObject( params ) && params.apiParams && params.apiParams.id ) {
                              $_activeElement = self.activeLevelEl();

                              if ( !$_activeElement || !_.isObject($_activeElement) || $_activeElement.length < 1 || self.activeLevelUI() !== params.apiParams.id ) {
                                    self.activeLevelEl( $('[data-sek-id="' + params.apiParams.id + '"]' ) );
                                    $_activeElement = self.activeLevelEl();
                              }
                        }
                        // Focus on the edited level
                        self.mayBeAnimateToEditedLevel( params );
                  };//_apiPreviewCallback


                  // Bind api preview
                  _.each( msgCollection, function( callbackFn, msgId ) {
                        if ( 'sek-refresh-stylesheet' === msgId ) {
                              api.preview.bind( msgId, function( params ) {
                                    _apiPreviewCallback( params, callbackFn, msgId );
                              });// api.preview.bind( msgId, function( params ) {
                              // September 2020 : added a debounced callback when generating stylesheet in order to prevent
                              // css value may not be taken into account when typed fast, for example an height in pixels
                              // https://github.com/presscustomizr/nimble-builder/issues/742
                              api.preview.bind( msgId, _.debounce( function( params ) {
                                    _apiPreviewCallback( params, callbackFn, msgId );
                                    if ( params && params.apiParams && params.apiParams.id ) {
                                          api.preview.trigger('sek-animate-to-level', { id : params.apiParams.id });
                                    }
                              }, 1000 ));// api.preview.bind( msgId, function( params ) {
                        } else if ( 'sek-refresh-stylesheet-in-the-background-for-custom-css' === msgId ) {
                              api.preview.bind( msgId, _.debounce( function( params ) {
                                    _apiPreviewCallback( params, callbackFn, msgId );
                                    if ( params && params.apiParams && params.apiParams.id ) {
                                          api.preview.trigger('sek-animate-to-level', { id : params.apiParams.id });
                                    }
                              }, 500 ));// api.preview.bind( msgId, function( params ) {
                        } else {
                              api.preview.bind( msgId, function( params ) {
                                    _apiPreviewCallback( params, callbackFn, msgId );
                              });// api.preview.bind( msgId, function( params ) {
                        }

                  });
            },//schedulePanelMsgReactions()

            mayBeAnimateToEditedLevel : function( params ) {
                  var self = this;
                  // MAY 2020 : focus on the edited element
                  if ( _.isObject( params ) && params.apiParams && params.apiParams.id ) {
                        $elToFocusOn = $('[data-sek-id="' + params.apiParams.id + '"]' );
                        // if user scrolled while editing an element, let's focus again
                        if ( 0 < $elToFocusOn.length && !nb_.isInScreen( $elToFocusOn[0]) ) {
                              $elToFocusOn[0].scrollIntoView({
                                    behavior: 'auto',
                                    block: 'center',
                                    inline: 'center'
                              });
                        }
                  }
            }
      });//$.extend()
})( wp.customize, jQuery, _ );
