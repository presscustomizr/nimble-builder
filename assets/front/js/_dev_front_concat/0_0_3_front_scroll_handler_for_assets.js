// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SCROLL LISTENER FOR DYNAMIC ASSET LOADING
 /* ------------------------------------------------------------------------- */
(function(w, d){
    var callbackFunc = function() {
        jQuery(function($){
            // do nothing if dynamic asset loading is not enabled for js and css
            if ( !sekFrontLocalized.load_front_partial_css_on_scroll && !sekFrontLocalized.load_front_module_js_on_scroll )
              return;
            // nb_.scrollHandlers = [
            //    { id : 'swiper', elements : $(), func : function(){} }
            //    ...
            // ]
            var _loopOnScrollHandlers = function() {
                $.each( nb_.scrollHandlers, function( id, handlerParam ) {
                    // has it been loaded already ?
                    if ( handlerParam.loaded )
                      return true;//<=> continue see https://api.jquery.com/jquery.each/

                    if ( 1 > handlerParam.elements.length )
                      return true;

                    if( nb_.isFunction( handlerParam.func ) ) {
                        try{ _loadAssetWhenElementVisible( id, handlerParam ); } catch(er){
                            nb_.errorLog('Nimble error => _loadAssetWhenElementVisible', er, handlerParam );
                        }
                    } else {
                        nb_.errorLog('Nimble error => _loadAssetWhenElementVisible => wrong callback func param', handlerParam );
                    }

                });
            };

            var _scrollHandle = nb_.throttle( _loopOnScrollHandlers, 100 );
            // Fire now or schedule when becoming visible.
            var _loadAssetWhenElementVisible = function( id, handlerParam ) {
                var isLoading = false,
                    $elements = handlerParam.elements,
                    func = handlerParam.func;

                $.each( $elements, function( k, el ) {
                    if ( !isLoading && nb_.isInWindow($(el) ) ) {
                        isLoading = true;
                        func();
                    }
                });
                if ( !isLoading ) {
                    $.each( $elements, function( k, el ) {
                        if ( !isLoading && nb_.isInWindow( $(el) ) ) {
                            isLoading = true;
                            func();
                        }
                    });
                }
                if ( isLoading ) {
                    console.log('LOAD ASSET ?', id );
                    // I've been executed forget about me
                    nb_.scrollHandlers[id].loaded = true;
                }

                // check if we need to unbind the scroll handle when all assets are loaded
                var allAssetsLoaded = true;
                $.each( nb_.scrollHandlers, function( id, handlerParam ) {
                    if ( true !== nb_.scrollHandlers[id].loaded ) {
                        allAssetsLoaded = false;
                    }
                    return false !== allAssetsLoaded;//break the look on the first asset not loaded found
                });
                if ( allAssetsLoaded ) {
                    console.log('ALL ASSETS LOADED');
                    nb_.cachedElements.$window.unbind('scroll', _scrollHandle );
                }
            };//_loadAssetWhenElementVisible
            // First try to load on page load
            _loopOnScrollHandlers();
            // then schedule loading on scroll
            nb_.cachedElements.$window.on( 'scroll', _scrollHandle );
        });//jQuery
    };//callbackFunc

    nb_.listenTo('nimble-app-ready', callbackFunc );
}(window, document));