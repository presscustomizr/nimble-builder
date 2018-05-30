//global sekPreviewLocalized
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // inspired from wp.template in wp-includes/js/wp-util.js
            parseTemplate : _.memoize(function ( id ) {
                  var self = this;
                  var compiled,
                    /*
                     * Underscore's default ERB-style templates are incompatible with PHP
                     * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
                     *
                     * @see trac ticket #22344.
                     */
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
            }//doAjax
      });//$.extend()
})( wp.customize, jQuery, _ );