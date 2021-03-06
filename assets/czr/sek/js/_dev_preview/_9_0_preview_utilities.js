//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // inspired from wp.template in wp-includes/js/wp-util.js
            parseTemplate : _.memoize(function ( id ) {
                  var self = this;
                  var compiled,
                    //
                    // Underscore's default ERB-style templates are incompatible with PHP
                    // when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
                    //
                    // @see trac ticket #22344.
                    //
                    options = {
                          evaluate:    /<#([\s\S]+?)#>/g,
                          interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                          escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
                          variable:    'data'
                    };

                  return function ( data ) {
                        if ( $( id ).length < 1 ) {
                            self.errare( 'preview => parseTemplate => the requested tmpl does not exist =>' + id );
                            return '';
                        }
                        try { compiled = compiled || _.template( $( id ).html(),  options );} catch( _er_ ) {
                              self.errare( 'preview => parseTemplate => problem when parsing tmpl =>' + id, _er_ );
                        }
                        return compiled( data );
                  };
            }),



            //@return [] for console method
            //@bgCol @textCol are hex colors
            //@arguments : the original console arguments
            _prettyPrintLog : function( args ) {
                  var _defaults = {
                        bgCol : '#5ed1f5',
                        textCol : '#000',
                        consoleArguments : []
                  };
                  args = _.extend( _defaults, args );

                  var _toArr = Array.from( args.consoleArguments ),
                      _truncate = function( string ){
                            if ( ! _.isString( string ) )
                              return '';
                            return string.length > 300 ? string.substr( 0, 299 ) + '...' : string;
                      };

                  //if the array to print is not composed exclusively of strings, then let's stringify it
                  //else join(' ')
                  if ( ! _.isEmpty( _.filter( _toArr, function( it ) { return ! _.isString( it ); } ) ) ) {
                        _toArr =  JSON.stringify( _toArr.join(' ') );
                  } else {
                        _toArr = _toArr.join(' ');
                  }
                  return [
                        '%c ' + _truncate( _toArr ),
                        [ 'background:' + args.bgCol, 'color:' + args.textCol, 'display: block;' ].join(';')
                  ];
            },

            _wrapLogInsideTags : function( title, msg, bgColor ) {
                  //fix for IE, because console is only defined when in F12 debugging mode in IE
                  if ( ( _.isUndefined( console ) && typeof window.console.log != 'function' ) )
                    return;
                  if ( sekPreviewLocalized.isDevMode ) {
                        if ( _.isUndefined( msg ) ) {
                              console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ '<' + title + '>' ] } ) );
                        } else {
                              console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ '<' + title + '>' ] } ) );
                              console.log( msg );
                              console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ '</' + title + '>' ] } ) );
                        }
                  } else {
                        console.log.apply( console, this._prettyPrintLog( { bgCol : bgColor, textCol : '#000', consoleArguments : [ title ] } ) );
                  }
            },

            errare : function( title, msg ) { this._wrapLogInsideTags( title, msg, '#ffd5a0' ); },
            infoLog : function( title, msg ) { this._wrapLogInsideTags( title, msg, '#5ed1f5' ); },

            //encapsulates a WordPress ajax request in a normalize method
            //@param queryParams = {}
            //
            //Important : we MUST use $.ajax() because when previewing, the ajax requests params are amended with a preFilter ( see customize-preview.js, $.ajaxPrefilter( prefilterAjax ) )in order to include params
            //in particular the 'customized' dirty values, that NB absolutely needs to dynamically register settings that have not yet been instantiated by WP_Customize_Manager
            // see WP Core => class WP_Customize_Manager, add_action( 'customize_register', array( $this, 'register_dynamic_settings' ), 11 );
            // see NB => class SEK_CZR_Dyn_Register
            // see NB => Nimble_Collection_Setting::filter_previewed_sek_get_skoped_seks => this is how we can get the sektions collection while customizing, see sek_get_skoped_seks()
            doAjax : function( queryParams ) {
                  var self = this;
                  //do we have a queryParams ?
                  queryParams = queryParams || ( _.isObject( queryParams ) ? queryParams : {} );

                  var ajaxUrl = queryParams.ajaxUrl || sekPreviewLocalized.ajaxUrl,//the ajaxUrl can be specified when invoking doAjax
                      nonce = sekPreviewLocalized.frontNonce,//{ 'id' => 'HuFrontNonce', 'handle' => wp_create_nonce( 'hu-front-nonce' ) },
                      dfd = $.Deferred(),
                      _query_ = _.extend( {
                                  action : '',
                                  withNonce : false
                            },
                            queryParams
                      );

                  // Check if the ajax url passes WP core customize-preview test
                  // Nov 2020 : added when fixing WPML compat https://github.com/presscustomizr/nimble-builder/issues/753
                  var urlParser = document.createElement( 'a' );
                      urlParser.href = ajaxUrl;
                  // Abort if the request is not for this site.
                  if ( ! api.isLinkPreviewable( urlParser, { allowAdminAjax: true } ) ) {
                      self.errare( 'self.doAjax => error => !api.isLinkPreviewable for action ' + _query_.action, urlParser );
                      return dfd.resolve().promise();
                  }

                  // HTTP ajaxurl when site is HTTPS causes Access-Control-Allow-Origin failure in Desktop and iOS Safari
                  if ( "https:" == document.location.protocol ) {
                        ajaxUrl = ajaxUrl.replace( "http://", "https://" );
                  }

                  //check if we're good
                  if ( _.isEmpty( _query_.action ) || ! _.isString( _query_.action ) ) {
                        self.errare( 'self.doAjax : unproper action provided' );
                        return dfd.resolve().promise();
                  }
                  //setup nonce
                  //Note : the nonce might be checked server side ( not in all cases, only when writing in db )  with check_ajax_referer( 'hu-front-nonce', 'HuFrontNonce' )
                  _query_[ nonce.id ] = nonce.handle;
                  if ( ! _.isObject( nonce ) || _.isUndefined( nonce.id ) || _.isUndefined( nonce.handle ) ) {
                        self.errare( 'self.doAjax : unproper nonce' );
                        return dfd.resolve().promise();
                  }

                  // introduced for https://github.com/presscustomizr/nimble-builder/issues/494
                  // september 2019
                  // this guid is used to differentiate dynamically rendered content from static content that may include a Nimble generated HTML structure
                  // an attribute "data-sek-preview-level-guid" is added to each rendered level when customizing or ajaxing
                  // otherwise the preview UI can be broken
                  _query_[ 'preview-level-guid' ] = sekPreviewLocalized.previewLevelGuid;

                  // introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
                  // Those query params are used for template tags related to the WP Query.
                  // Like "{{the_title}}" => in this case, using "get_the_title()" as callback when ajaxing with Nimble will return nothing. We need get_the_title( $post_id )
                  // That's why those static query params are written in the preview frame, and used as ajax params that we can access server side via php $_POST.
                  _query_.czr_query_params = JSON.stringify( _.isObject( _wpCustomizeSettings.czr_query_params ) ? _wpCustomizeSettings.czr_query_params : [] );

                  // note that the $_POST['customized'] param is set by core WP customizer-preview.js with $.ajaxPrefilter
                  $.post( ajaxUrl, _query_ )
                        .done( function( _r ) {
                              // Check if the user is logged out.
                              if ( '0' === _r ||  '-1' === _r || false === _r.success ) {
                                    self.errare( 'self.doAjax : done ajax error for action : ' + _query_.action , _r );
                                    dfd.reject( _r );
                              }
                              dfd.resolve( _r );

                        })
                        .fail( function( _r ) {
                              self.errare( 'self.doAjax : failed ajax error for : ' + _query_.action, _r );
                              dfd.reject( _r );
                        });
                        //.always( function( _r ) { dfd.resolve( _r ); });
                  return dfd.promise();
            },//doAjax






            // @return boolean
            isModuleRegistered : function( moduleType ) {
                  return sekPreviewLocalized.registeredModules && ! _.isUndefined( sekPreviewLocalized.registeredModules[ moduleType ] );
            },


            //@return mixed
            getRegisteredModuleProperty : function( moduleType, property ) {
                  if ( ! this.isModuleRegistered( moduleType ) ) {
                        return 'not_set';
                  }
                  return sekPreviewLocalized.registeredModules[ moduleType ][ property ];
            },

            // @params = { id : '', level : '' }
            // Recursively walk the level tree until a match is found
            // @return the level model object
            getLevelModel : function( id, collection ) {
                  var self = this, _data_ = 'no_match';
                  // do we have a collection ?
                  // if not, let's use the root one
                  if ( _.isUndefined( collection ) ) {
                        self.errare( 'getLevelModel => a collection must be provided' );
                  }
                  _.each( collection, function( levelData ) {
                        // did we have a match recursively ?
                        if ( 'no_match' != _data_ )
                          return;
                        if ( id === levelData.id ) {
                              _data_ = levelData;
                        } else {
                              if ( _.isArray( levelData.collection ) ) {
                                    _data_ = self.getLevelModel( id, levelData.collection );
                              }
                        }
                  });
                  return _data_;
            },
      });//$.extend()
})( wp.customize, jQuery, _ );