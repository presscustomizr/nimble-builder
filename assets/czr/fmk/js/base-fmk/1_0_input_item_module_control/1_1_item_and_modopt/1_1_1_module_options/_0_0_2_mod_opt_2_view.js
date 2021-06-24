//extends api.CZRBaseControl

var CZRModOptMths = CZRModOptMths || {};
( function ( api, $, _ ) {
$.extend( CZRModOptMths , {
      //fired when modOpt is ready and embedded
      //define the modOpt view DOM event map
      //bind actions when the modOpt is embedded
      modOptWrapperViewSetup : function( modOpt_model ) {
              var modOpt = this,
                  module = this.module,
                  dfd = $.Deferred(),
                  _setupDOMListeners = function( $_container ) {
                        //DOM listeners for the user action in modOpt view wrapper
                        api.CZR_Helpers.setupDOMListeners(
                             [
                                    //toggle mod options
                                    {
                                          trigger   : 'click keydown',
                                          selector  : '.' + module.control.css_attr.close_modopt_icon,
                                          name      : 'close_mod_option',
                                          actions   : function() {
                                                // @see : moduleCtor::maybeAwakeAndBindSharedModOpt => api.czr_ModOptVisible.bind()
                                                api.czr_ModOptVisible( false, {
                                                      module : module,//the current module for which the modOpt is being expanded
                                                      focus : false//the id of the tab we want to focus on
                                                });
                                          }
                                    },
                                    //tabs navigation
                                    {
                                          trigger   : 'click keydown',
                                          selector  : '.tabs nav li',
                                          name      : 'tab_nav',
                                          actions   : function( args ) {
                                                //toggleTabVisibility is declared in the module ctor and its "this" is the item or the modOpt
                                                var tabIdSwitchedTo = $( args.dom_event.currentTarget, args.dom_el ).data('tab-id');
                                                this.module.toggleTabVisibility.call( this, tabIdSwitchedTo );
                                                this.trigger( 'tab-switch', { id : tabIdSwitchedTo } );
                                          }
                                    }
                              ],//actions to execute
                              { dom_el: $_container },//model + dom scope
                              modOpt //instance where to look for the cb methods
                        );
                  };

              modOpt_model = modOpt() || modOpt.initial_modOpt_model;//could not be set yet

              //renderview content now
              modOpt.renderModOptContent( modOpt_model )
                    .done( function( $_container ) {
                          //update the $.Deferred state
                          if ( ! _.isEmpty( $_container ) && 0 < $_container.length ) {
                                _setupDOMListeners( $_container );
                                dfd.resolve( $_container );
                          }
                          else {
                                throw new Error( 'Module : ' + modOpt.module.id + ', the modOpt content has not been rendered' );
                          }
                    })
                    .fail( function( _r_ ) {
                          api.errorLog( "failed modOpt.renderModOptContent for module : " + module.id, _r_ );
                    })
                    .then( function() {
                          //the modOpt.container is now available
                          //Setup the tabs navigation
                          //setupTabNav is defined in the module ctor and its this is the item or the modOpt
                          modOpt.module.setupTabNav.call( modOpt );
                    });

              return dfd.promise();
      },


      //renders saved modOpt views
      //returns a promise( $container )
      //the saved modOpt look like :
      //array[ { id : 'sidebar-one', title : 'A Title One' }, {id : 'sidebar-two', title : 'A Title Two' }]
      renderModOptContent : function( modOpt_model ) {
              //=> an array of objects
              var modOpt = this,
                  module = this.module,
                  dfd = $.Deferred();

              modOpt_model = modOpt_model || modOpt();

              var appendAndResolve = function( _tmpl_ ) {
                    //do we have an html template ?
                    if ( _.isEmpty( _tmpl_ ) ) {
                          dfd.reject( 'renderModOptContent => Missing html template for module : '+ module.id );
                    }

                    var _ctrlLabel = '';
                    try {
                          _ctrlLabel = [ serverControlParams.i18n['Options for'], module.control.params.label ].join(' ');
                    } catch( er ) {
                          api.errorLog( 'renderItemContent => Problem with ctrl label => ' + er );
                          _ctrlLabel = serverControlParams.i18n['Settings'];
                    }

                    $('#widgets-left').after( $( '<div/>', {
                          class : module.control.css_attr.mod_opt_wrapper,
                          html : [
                                [ '<h2 class="mod-opt-title">', _ctrlLabel , '</h2>' ].join(''),
                                '<span class="fas fa-times ' + module.control.css_attr.close_modopt_icon + '" title="close"></span>'
                          ].join('')
                    } ) );

                    //render the mod opt content for this module
                    $( '.' + module.control.css_attr.mod_opt_wrapper ).append( _tmpl_ );

                    dfd.resolve( $( '.' + module.control.css_attr.mod_opt_wrapper ) );
              };//appendAndResolve

              // Do we have view content template script?
              // if yes, let's use it <= Old way
              // Otherwise let's fetch the html template from the server
              if ( ! _.isEmpty( module.itemPreAddEl ) ) {
                    var tmplSelectorSuffix = module.getTemplateSelectorPart( 'modOptInputList', modOpt_model );
                    if ( 1 > $( '#tmpl-' + tmplSelectorSuffix ).length ) {
                          dfd.reject( 'renderModOptContent => No modOpt content template defined for module ' + module.id + '. The template script id should be : #tmpl-' + tmplSelectorSuffix );
                    }
                    appendAndResolve( wp.template( tmplSelectorSuffix )( modOpt_model ) );
              } else {
                    api.CZR_Helpers.getModuleTmpl( {
                          tmpl : 'mod-opt',
                          module_type: module.module_type,
                          module_id : module.id,
                          control_id : module.control.id
                    } ).done( function( _serverTmpl_ ) {
                          //console.log( 'renderModOptContent => success response =>', _serverTmpl_);
                          appendAndResolve( api.CZR_Helpers.parseTemplate( _serverTmpl_ )( modOpt_model ) );
                    }).fail( function( _r_ ) {
                          //console.log( 'renderModOptContent => fail response =>', _r_);
                          dfd.reject( 'renderPreItemView => Problem when fetching the mod-opt tmpl from server for module : '+ module.id );
                    });
              }

              return dfd.promise();
      },



      toggleModPanelView : function( visible ) {
            var modOpt = this,
                module = this.module,
                ctrl = module.control,
                dfd = $.Deferred();

            module.control.container.toggleClass( 'czr-modopt-visible', visible );
            $('body').toggleClass('czr-editing-modopt', visible );
            //Let the panel slide (  -webkit-transition: left .18s ease-in-out )
            _.delay( function() {
                  dfd.resolve();
            }, 200 );
            return dfd.promise();
      }
});//$.extend
})( wp.customize , jQuery, _ );