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
            _updAPISet_sek_add_module :  function() {
                  var self = this,
                      params,
                      columnCandidate,
                      startingModuleValue,// will be populated by the optional starting value specificied on module registration
                      position;//<= the position of the module or section or nested section in a column or location

                  params = self.updAPISetParams.params;

                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }
                  // a module_type must be provided
                  if ( _.isEmpty( params.module_type ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing module_type' );
                  }
                  columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' === columnCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                        return;
                  }

                  position = 0;
                  columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                  // get the position of the before or after module or nested section
                  _.each( columnCandidate.collection, function( moduleOrNestedSectionModel, index ) {
                        if ( params.before_module_or_nested_section === moduleOrNestedSectionModel.id ) {
                              position = index;
                        }
                        if ( params.after_module_or_nested_section === moduleOrNestedSectionModel.id ) {
                              position = index + 1;
                        }
                  });

                  var _moduleParams = {
                        id : params.id,
                        level : 'module',
                        module_type : params.module_type,
                        ver_ini : sektionsLocalizedData.nimbleVersion
                  };
                  // Let's add the starting value if provided when registrating the module
                  startingModuleValue = self.getModuleStartingValue( params.module_type );
                  if ( 'no_starting_value' !== startingModuleValue ) {
                        _moduleParams.value = startingModuleValue;
                  }

                  columnCandidate.collection.splice( position, 0, _moduleParams );
            },


            _updAPISet_sek_duplicate_module :  function() {
                  var self = this,
                      params,
                      columnCandidate;

                  params = self.updAPISetParams.params;

                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }
                  columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' == columnCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => no parent column matched');
                        return;
                  }

                  columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];

                  var deepClonedModule;
                  try { deepClonedModule = self.cloneLevel( params.id ); } catch( er ) {
                        api.errare( 'updateAPISetting => ' + params.action, er );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => error when cloning the level');
                        return;
                  }
                  // items id of multi-items module must always be unique
                  // this recursive method sniff and does the job
                  self.maybeGenerateNewItemIdsForCrudModules( deepClonedModule );

                  var insertInposition = self.getLevelPositionInCollection( params.id, self.updAPISetParams.newSetValue.collection );
                  self.updAPISetParams.cloneId = deepClonedModule.id;//will be passed in resolve()
                  columnCandidate.collection.splice( parseInt( insertInposition + 1, 10 ), 0, deepClonedModule );
            },

            _updAPISet_sek_remove_module :  function() {
                  var self = this,
                      params,
                      columnCandidate;

                  params = self.updAPISetParams.params;

                  // an id must be provided
                  if ( _.isEmpty( params.id ) ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing id' );
                  }
                  columnCandidate = self.getLevelModel( params.in_column, self.updAPISetParams.newSetValue.collection );
                  if ( 'no_match' != columnCandidate ) {
                        columnCandidate.collection =  _.isArray( columnCandidate.collection ) ? columnCandidate.collection : [];
                        columnCandidate.collection = _.filter( columnCandidate.collection, function( module ) {
                              return module.id != params.id;
                        });

                  } else {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no parent column matched' );
                  }
            },

            _updAPISet_sek_move_module :  function() {
                  var self = this,
                      params;

                  params = self.updAPISetParams.params;

                  var toColumnCandidate,
                      movedModuleCandidate,
                      copyOfMovedModuleCandidate;

                  // loop on the sektions to find the toColumnCandidate
                  // _.each( self.updAPISetParams.newSetValue.collection, function( _sektion_ ) {
                  //       _.each( _sektion_.collection, function( _column_ ) {
                  //             if ( _column_.id == params.to_column ) {
                  //                  toColumnCandidate = _column_;
                  //             }
                  //       });
                  // });
                  toColumnCandidate = self.getLevelModel( params.to_column, self.updAPISetParams.newSetValue.collection );

                  if ( _.isEmpty( toColumnCandidate ) || 'no_match' == toColumnCandidate ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => missing target column' );
                  }

                  // If the module has been moved to another column
                  // => remove the moved module from the source column
                  if ( params.from_column != params.to_column ) {
                        var fromColumnCandidate;
                        fromColumnCandidate = self.getLevelModel( params.from_column, self.updAPISetParams.newSetValue.collection );

                        if ( _.isEmpty( fromColumnCandidate ) || 'no_match' == fromColumnCandidate ) {
                              throw new Error( 'updateAPISetting => ' + params.action + ' => missing source column' );
                        }

                        fromColumnCandidate.collection =  _.isArray( fromColumnCandidate.collection ) ? fromColumnCandidate.collection : [];
                        // Make a copy of the module candidate now, before removing it
                        movedModuleCandidate = self.getLevelModel( params.id, self.updAPISetParams.newSetValue.collection );
                        copyOfMovedModuleCandidate = $.extend( true, {}, movedModuleCandidate );
                        // remove the module from its previous column
                        fromColumnCandidate.collection = _.filter( fromColumnCandidate.collection, function( module ) {
                              return module.id != params.id;
                        });
                  }// if params.from_column != params.to_column

                  // update the target column
                  toColumnCandidate.collection =  _.isArray( toColumnCandidate.collection ) ? toColumnCandidate.collection : [];
                  originalCollection = $.extend( true, [], toColumnCandidate.collection );
                  var reorderedCollection = [];
                  _.each( params.newOrder, function( _id_ ) {
                        if ( params.from_column != params.to_column && _id_ == copyOfMovedModuleCandidate.id ) {
                              reorderedCollection.push( copyOfMovedModuleCandidate );
                        } else {
                              moduleCandidate = self.getLevelModel( _id_, self.updAPISetParams.newSetValue.collection );
                              if ( _.isEmpty( moduleCandidate ) || 'no_match' == moduleCandidate ) {
                                    throw new Error( 'updateAPISetting => ' + params.action + ' => missing moduleCandidate' );
                              }
                              reorderedCollection.push( moduleCandidate );
                        }
                  });
                  // Check if we have duplicates ?
                  if ( reorderedCollection.length != _.uniq( reorderedCollection ).length ) {
                        throw new Error( 'updateAPISetting => ' + params.action + ' => there are duplicated modules in column : ' + toColumnCandidate.id );
                  } else {
                        toColumnCandidate.collection = reorderedCollection;
                  }
            },

            _updAPISet_sek_set_module_value :  function() {
                  var self = this,
                      params,
                      moduleCandidate;

                  params = self.updAPISetParams.params;

                  moduleCandidate = self.getLevelModel( params.id, self.updAPISetParams.newSetValue.collection );

                  // Is this a multi-item module ?
                  // Fixes https://github.com/presscustomizr/nimble-builder/issues/616
                  var _ctrl_ = params.settingParams.args.moduleRegistrationParams.control,
                      _module_id_ = params.settingParams.args.moduleRegistrationParams.id,
                      parentModuleInstance = _ctrl_.czr_Module( _module_id_ );

                  if ( ! _.isEmpty( parentModuleInstance ) ) {
                        isMultiItemModule = parentModuleInstance.isMultiItem();
                  } else {
                        api.errare( 'updateAPISetting => missing parentModuleInstance', params );
                  }

                  // if multi-item module, the value is array of items, otherwise an object
                  var _modValueCandidate = isMultiItemModule ? [] : {};
                  // consider only the non empty settings for db
                  // booleans should bypass this check
                  _.each( params.value || (isMultiItemModule ? [] : {}), function( _val_, _key_ ) {
                        // Note : _.isEmpty( 5 ) returns true when checking an integer,
                        // that's why we need to cast the _val_ to a string when using _.isEmpty()
                        if ( ! _.isBoolean( _val_ ) && _.isEmpty( _val_ + "" ) )
                          return;
                        _modValueCandidate[ _key_ ] = _val_;
                  });
                  if ( 'no_match' == moduleCandidate ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => no module matched', params );
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => error no module matched');
                        return;
                  }
                  if ( _.isEmpty( params.options_type ) ) {
                        api.errare( 'updateAPISetting => ' + params.action + ' => missing options_type');
                        self.updAPISetParams.promise.reject( 'updateAPISetting => ' + params.action + ' => missing options_type');
                        return;
                  }

                  // Is this a father module ?
                  // If yes, the module value is structured by option group, each option group being updated by a child module
                  // If no, the default option type is : '__no_option_group_to_be_updated_by_children_modules__'
                  if ( '__no_option_group_to_be_updated_by_children_modules__' === params.options_type ) {
                        moduleCandidate.value = _modValueCandidate;
                  } else {
                        // start from a deep cloned object
                        // prevents issues like https://github.com/presscustomizr/nimble-builder/issues/455
                        var _new_module_values = $.extend( true, {}, _.isEmpty( moduleCandidate.value ) ? {} : moduleCandidate.value );
                        _new_module_values[ params.options_type ] = _modValueCandidate;
                        moduleCandidate.value = _new_module_values;
                  }
            },

      });//$.extend()
})( wp.customize, jQuery );