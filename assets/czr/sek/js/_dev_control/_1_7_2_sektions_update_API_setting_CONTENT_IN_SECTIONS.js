//global sektionsLocalizedData, serverControlParams
//self.updAPISetParams = {
//       params : params,
//       promise : $.Deferred(),
//       newSetValue : _.isObject( _currentSetValue ) ? $.extend( true, {}, _currentSetValue ) : self.getDefaultSektionSettingValue( params.is_global_location ? 'global' : 'local' ),
//       cloneId : '',
//       sectionInjectPromise
// };
var CZRSeksPrototype = CZRSeksPrototype || {};
(function ( api, $ ) {
      $.extend( CZRSeksPrototype, {
            // @params {
            //   drop_target_element : $(this),
            //   position : _position,// <= top or bottom
            //   before_section : $(this).data('sek-before-section'),
            //   after_section : $(this).data('sek-after-section'),
            //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
            //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
            // }
            _updAPISet_sek_add_content_in_new_sektion :  function() {
                  var self = this,
                      params,
                      columnCandidate,
                      locationCandidate;

                  params = self.updAPISetParams.params;

                  // get the position of the before or after section
                  var positionIndex = 0,
                      startingModuleValue;

                  locationCandidate = self.getLevelModel( params.location, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' == locationCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no location matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no location matched');
                        return;
                  }
                  locationCandidate.collection = _.isArray( locationCandidate.collection ) ? locationCandidate.collection : [];
                  _.each( locationCandidate.collection, function( secModel, index ) {
                        if ( params.before_section === secModel.id ) {
                              positionIndex = index;
                        }
                        if ( params.after_section === secModel.id ) {
                              positionIndex = index + 1;
                        }
                  });

                  switch( params.content_type) {
                        // When a module is dropped in a section + column structure to be generated
                        case 'module' :
                              // Let's add the starting value if provided when registrating the module
                              // Note : params.content_id is the module_type
                              startingModuleValue = self.getModuleStartingValue( params.content_id );

                              // insert the section in the collection at the right place
                              locationCandidate.collection.splice( positionIndex, 0, {
                                    id : params.id,
                                    level : 'section',
                                    collection : [
                                          {
                                                id : sektionsLocalizedData.prefixForSettingsNotSaved + self.guid(),
                                                level : 'column',
                                                collection : [
                                                      {
                                                            id : params.droppedModuleId,
                                                            level : 'module',
                                                            module_type : params.content_id,
                                                            value : 'no_starting_value' !== startingModuleValue ? startingModuleValue : null,
                                                            ver_ini : sektionsLocalizedData.nimbleVersion
                                                      }
                                                ],
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          }
                                    ],
                                    ver_ini : sektionsLocalizedData.nimbleVersion
                              });
                        break;//case 'module' :

                        // When a preset section is dropped
                        case 'preset_section' :
                              // insert the section in the collection at the right place
                              self.updAPISetParams.sectionInjectPromise = $.Deferred();//defined at the beginning of the method

                              // we use a section index here to display the section in the same order as in the json
                              // introduced when implementing multi-section pre-build section
                              // @see https://github.com/presscustomizr/nimble-builder/issues/489
                              var _injectPresetSectionInLocationOrParentColumn = function( sectionReadyToInject, positionIndex ) {
                                    // If the preset_section is inserted in a an empty nested section, add it at the right place in the parent column of the nested section.
                                    // Otherwise, add the preset section at the right position in the parent location of the section.
                                    var insertedInANestedSektion = false;
                                    if ( ! _.isEmpty( params.sektion_to_replace ) ) {
                                          var sektionToReplace = self.getLevelModel( params.sektion_to_replace, self.updAPISetParams.newSetValue.collection );
                                          if ( 'no_match' === sektionToReplace ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched' );
                                                self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no sektionToReplace matched');
                                          }
                                          insertedInANestedSektion = true === sektionToReplace.is_nested;
                                    }


                                    // The following param "collection_of_preset_section_id" has been introduced when implementing support for multi-section pre-build sections
                                    // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                    // It is sent to the preview with ::reactToPreviewMsg, see bottom of the method.
                                    var injected_section_id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();
                                    params.collection_of_preset_section_id = params.collection_of_preset_section_id || [];
                                    params.collection_of_preset_section_id.push( injected_section_id );

                                    if ( ! insertedInANestedSektion ) {
                                          locationCandidate.collection.splice( positionIndex, 0, {
                                                id : injected_section_id,//params.id,//self.guid()
                                                level : 'section',
                                                collection : sectionReadyToInject.collection,
                                                options : sectionReadyToInject.options || {},
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    } else {
                                          columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                                          if ( 'no_match' === columnCandidate ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                                                self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                                          }

                                          columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                                          // get the position of the before or after module
                                          _.each( columnCandidate.collection, function( moduleOrSectionModel, index ) {
                                                if ( params.before_section === moduleOrSectionModel.id ) {
                                                      positionIndex = index;
                                                }
                                                if ( params.after_section === moduleOrSectionModel.id ) {
                                                      positionIndex = index + 1;
                                                }
                                          });

                                          columnCandidate.collection.splice( positionIndex, 0, {
                                                id : injected_section_id,
                                                is_nested : true,
                                                level : 'section',
                                                collection : sectionReadyToInject.collection,
                                                options : sectionReadyToInject.options || {},
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    }
                              };//_injectPresetSectionInLocationOrParentColumn
                              var _doWhenPresetSectionCollectionFetched = function( presetColumnOrSectionCollection ) {
                                    self.preparePresetSectionForInjection( presetColumnOrSectionCollection )
                                          .fail( function( _er_ ){
                                                self.updAPISetParams.promise.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                                // Used when updating the setting
                                                // @see end of this method
                                                self.updAPISetParams.sectionInjectPromise.reject( _er_ );
                                          })
                                          .done( function( maybeMultiSectionReadyToInject ) {
                                                var is_multi_section = 'section' === maybeMultiSectionReadyToInject.collection[0].level;
                                                // support for pre-built multi-section has been introduced in july 2019
                                                // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                                if ( is_multi_section ) {
                                                      // we use a section index here to display the section in the same order as in the json
                                                      var sectionIndex = 0;
                                                      _.each( maybeMultiSectionReadyToInject.collection, function( sectionReadyToInject ) {
                                                            _injectPresetSectionInLocationOrParentColumn( sectionReadyToInject, positionIndex );
                                                            positionIndex++;
                                                      });
                                                } else {
                                                      _injectPresetSectionInLocationOrParentColumn( maybeMultiSectionReadyToInject, positionIndex );
                                                }

                                                // Used when updating the setting
                                                // @see end of this method
                                                self.updAPISetParams.sectionInjectPromise.resolve();
                                          });// self.preparePresetSectionForInjection.done()
                              };//_doWhenPresetSectionCollectionFetched()

                              // Try to fetch the sections from the server
                              // if sucessfull, resolve self.updAPISetParams.sectionInjectPromise.promise()
                              self.getPresetSectionCollectionData({
                                          is_user_section : params.is_user_section,
                                          presetSectionId : params.content_id
                                    })
                                    .fail( function( _er_ ) {
                                          api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollectionData()', _er_ );
                                          self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollectionData()');
                                    })
                                    .done( function( presetColumnOrSectionCollection ) {
                                          if ( ! _.isObject( presetColumnOrSectionCollection ) || _.isEmpty( presetColumnOrSectionCollection ) ) {
                                                api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnOrSectionCollection );
                                                self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                                          }
                                          // OK. time to resolve self.updAPISetParams.sectionInjectPromise.promise()
                                          _doWhenPresetSectionCollectionFetched( presetColumnOrSectionCollection );
                                    });//self.getPresetSectionCollectionData().done()

                        break;//case 'preset_section' :
                  }//switch( params.content_type)
            },







            // @params {
            //   drop_target_element : $(this),
            //   position : _position,// <= top or bottom
            //   before_section : $(this).data('sek-before-section'),
            //   after_section : $(this).data('sek-after-section'),
            //   content_type : event.originalEvent.dataTransfer.getData( "sek-content-type" ), //<= module or preset_section
            //   content_id : event.originalEvent.dataTransfer.getData( "sek-content-id" )
            // }
            _updAPISet_sek_add_preset_sektion_in_new_nested_sektion :  function() {
                  var self = this,
                      params,
                      columnCandidate,
                      parentSektionCandidate;

                  params = self.updAPISetParams.params;
                  columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' === columnCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                        return;
                  }

                  var positionIndexInColumn = 0;
                  columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                   // get the position of the before or after module or nested section
                  _.each( columnCandidate.collection, function( moduleOrNestedSectionModel, index ) {
                        if ( params.before_module_or_nested_section === moduleOrNestedSectionModel.id ) {
                              positionIndexInColumn = index;
                        }
                        if ( params.after_module_or_nested_section === moduleOrNestedSectionModel.id ) {
                              positionIndexInColumn = index + 1;
                        }
                  });

                  // can we add this nested sektion ?
                  // if the parent sektion of the column has is_nested = true, then we can't
                  parentSektionCandidate = self.getLevelModel( params.in_sektion, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' == parentSektionCandidate ) {
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no grand parent sektion found');
                        return;
                  }
                  if ( true === parentSektionCandidate.is_nested ) {
                        self.updAPISetParams.promise.reject('');
                        api.previewer.trigger('sek-notify', {
                              type : 'info',
                              duration : 30000,
                              message : [
                                    '<span style="font-size:0.95em">',
                                      '<strong>' + sektionsLocalizedData.i18n[ "You've reached the maximum number of allowed nested sections." ] + '</strong>',
                                    '</span>'
                              ].join('')
                        });
                        return;
                  }

                  // insert the nested section in the collection at the right place
                  var presetColumnOrSectionCollection;
                  self.updAPISetParams.sectionInjectPromise = $.Deferred();

                  var _doWhenPrebuiltSectionCollectionFetched = function( presetColumnOrSectionCollection ) {
                        self.preparePresetSectionForInjection( presetColumnOrSectionCollection )
                              .fail( function( _er_ ){
                                    self.updAPISetParams.promise.reject( 'updateAPISetting => error when preparePresetSectionForInjection => ' + params.action + ' => ' + _er_ );
                                    // Used when updating the setting
                                    // @see end of this method
                                    self.updAPISetParams.sectionInjectPromise.reject( _er_ );
                              })
                              .done( function( maybeMultiSectionReadyToInject ) {

                                    var _injectNestedSectionInParentColumn = function( sectionReadyToInject, positionIndexInColumn  ) {
                                          positionIndexInColumn = positionIndexInColumn || 0;

                                          // The following param "collection_of_preset_section_id" has been introduced when implementing support for multi-section pre-build sections
                                          // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                          // It is sent to the preview with ::reactToPreviewMsg, see bottom of the method.
                                          var injected_section_id = sektionsLocalizedData.prefixForSettingsNotSaved + self.guid();
                                          params.collection_of_preset_section_id = params.collection_of_preset_section_id || [];
                                          params.collection_of_preset_section_id.push( injected_section_id );

                                          columnCandidate.collection.splice( positionIndexInColumn, 0, {
                                                id : injected_section_id,
                                                level : 'section',
                                                collection : sectionReadyToInject.collection,
                                                options : sectionReadyToInject.options || {},
                                                is_nested : true,
                                                ver_ini : sektionsLocalizedData.nimbleVersion
                                          });
                                    };

                                    // support for pre-built multi-section has been introduced in july 2019
                                    // @see https://github.com/presscustomizr/nimble-builder/issues/489
                                    var is_multi_section = 'section' === maybeMultiSectionReadyToInject.collection[0].level;
                                    if ( is_multi_section ) {
                                          _.each( maybeMultiSectionReadyToInject.collection, function( sectionReadyToInject ) {
                                                _injectNestedSectionInParentColumn( sectionReadyToInject, positionIndexInColumn );
                                                positionIndexInColumn++;
                                          });
                                    } else {
                                          _injectNestedSectionInParentColumn( maybeMultiSectionReadyToInject, positionIndexInColumn );
                                    }

                                    // Used when updating the setting
                                    // @see end of this method
                                    self.updAPISetParams.sectionInjectPromise.resolve();
                              });//self.preparePresetSectionForInjection.done()
                  };//_doWhenPrebuiltSectionCollectionFetched


                  // Try to fetch the sections from the server
                  // if sucessfull, resolve self.updAPISetParams.sectionInjectPromise.promise()
                  self.getPresetSectionCollectionData({
                              is_user_section : params.is_user_section,
                              presetSectionId : params.content_id
                        })
                        .fail( function() {
                              api.errare( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollectionData()', _er_ );
                              self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => Error with self.getPresetSectionCollectionData()');
                        })
                        .done( function( presetColumnOrSectionCollection ) {
                              if ( ! _.isObject( presetColumnOrSectionCollection ) || _.isEmpty( presetColumnOrSectionCollection ) ) {
                                    api.errare( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty : ' + params.content_id, presetColumnOrSectionCollection );
                                    self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => preset section type not found or empty');
                              }
                              // OK. time to resolve self.updAPISetParams.sectionInjectPromise.promise()
                              _doWhenPrebuiltSectionCollectionFetched( presetColumnOrSectionCollection );
                        });//self.getPresetSectionCollectionData().done()
            }

      });//$.extend()
})( wp.customize, jQuery );