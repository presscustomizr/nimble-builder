//global sektionsLocalizedData
var SekPreviewPrototype = SekPreviewPrototype || {};
( function( api, $, _ ) {
      $.extend( SekPreviewPrototype, {
            // inspired from wp.template in wp-includes/js/wp-util.js
            parseTemplate : _.memoize(function ( id ) {
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
                            czrapp.errare( 'preview => parseTemplate => the requested tmpl does not exist =>' + id );
                            return '';
                        }
                        try { compiled = compiled || _.template( $( id ).html(),  options );} catch( _er_ ) {
                              czrapp.errare( 'preview => parseTemplate => problem when parsing tmpl =>' + id, _er_ );
                        }
                        return compiled( data );
                  };
            }),
      });//$.extend()
})( wp.customize, jQuery, _ );