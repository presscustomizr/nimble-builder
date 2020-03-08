// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SCROLL LISTENER FOR DYNAMIC ASSET LOADING
 /* ------------------------------------------------------------------------- */
(function(w, d){
    // Fire now or schedule when becoming visible.
    nb_.loadAssetWhenElementVisible = function( id, handlerParams ) {
        if ( nb_.scrollHandlers[id].loaded )
          return;
        nb_.scrollHandlers[id].loaded = false;
        var $elements = handlerParams.elements,
            loaderFunc = handlerParams.func;

        $.each( $elements, function( k, el ) {
            if ( !nb_.scrollHandlers[id].loaded && nb_.isInWindow($(el) ) ) {
                loaderFunc();
                nb_.scrollHandlers[id].loaded = true;
                //console.log('LOAD ASSET ?', id );
            }
        });
        // check if we need to unbind the scroll handle when all assets are loaded
        var allAssetsLoaded = true;
        $.each( nb_.scrollHandlers, function( id, handlerParams ) {
            if ( true !== nb_.scrollHandlers[id].loaded ) {
                allAssetsLoaded = false;
            }
            return false !== allAssetsLoaded;//break the look on the first asset not loaded found
        });
        if ( allAssetsLoaded ) {
            //console.log('ALL ASSETS LOADED');
            nb_.cachedElements.$window.unbind('scroll', nb_.scrollHandleForLoadingAssets );
        }
    };//_loadAssetWhenElementVisible

    nb_.loopOnScrollHandlers = function() {
        $.each( nb_.scrollHandlers, function( id, handlerParams ) {
            // has it been loaded already ?
            if ( handlerParams.loaded )
              return true;//<=> continue see https://api.jquery.com/jquery.each/

            if ( 1 > handlerParams.elements.length )
              return true;

            if( nb_.isFunction( handlerParams.func ) ) {
                try{ nb_.loadAssetWhenElementVisible( id, handlerParams ); } catch(er){
                    nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers', er, handlerParams );
                }
            } else {
                nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers => wrong callback func param', handlerParams );
            }

        });
    };

    // each time a new scroll handler is added, it emits the event 'nimble-new-scroll-handler-added'
    // so when caught, let's try to detect any dependant element is visible in the page
    // and if so, load.
    // Typically useful on page load if for example the slider is on top of the page and we need to load swiper.js right away before scrolling
    nb_.listenTo('nimble-new-scroll-handler-added', nb_.loopOnScrollHandlers );

    // bound on scroll,
    // unbound when all assets are loaded
    nb_.scrollHandleForLoadingAssets = nb_.throttle( nb_.loopOnScrollHandlers, 100 );

    nb_.listenTo('nimble-app-ready', function() {
        jQuery(function($){
            // do nothing if dynamic asset loading is not enabled for js and css
            if ( !sekFrontLocalized.load_front_partial_css_on_scroll && !sekFrontLocalized.load_front_module_js_on_scroll )
              return;
            // nb_.scrollHandlers = [
            //    { id : 'swiper', elements : $(), func : function(){} }
            //    ...
            // ]
            //
            // schedule loading on scroll
            // unbound when all assets are loaded
            nb_.cachedElements.$window.on( 'scroll', nb_.scrollHandleForLoadingAssets );
        });//jQuery
    });
}(window, document));