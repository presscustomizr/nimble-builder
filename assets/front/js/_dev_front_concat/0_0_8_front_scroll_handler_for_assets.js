// global sekFrontLocalized, nimbleListenTo
/* ------------------------------------------------------------------------- *
 *  SCROLL LISTENER FOR DYNAMIC ASSET LOADING
 /* ------------------------------------------------------------------------- */
(function(w, d){
    // Fire now or schedule when becoming visible.
    nb_.loadAssetWhenElementVisible = function( id, handlerParams ) {
        jQuery(function($){
            if ( nb_.scrollHandlers[id].loaded )
              return;
            nb_.scrollHandlers[id].loaded = false;
            var $elements = handlerParams.elements,
                loaderFunc = handlerParams.func;

            $.each( $elements, function( k, el ) {
                if ( !nb_.scrollHandlers[id].loaded && nb_.elOrFirstVisibleParentIsInWindow($(el) ) ) {
                    loaderFunc();
                    nb_.scrollHandlers[id].loaded = true;
                }
            });

            if ( handlerParams.scrollHandler && nb_.scrollHandlers[id].loaded ) {
                nb_.cachedElements.$window.off('scroll', handlerParams.scrollHandler );
            }
        });
    };//_loadAssetWhenElementVisible

    nb_.loopOnScrollHandlers = function() {
        var _scrollHandler;
        jQuery(function($){
            $.each( nb_.scrollHandlers, function( id, handlerParams ) {
                // has it been loaded already ?
                if ( handlerParams.loaded )
                  return true;//<=> continue see https://api.jquery.com/jquery.each/

                // do nothing if dynamic asset loading is not enabled for js and css AND the assets in not in "force" mode
                var load_authorized = sekFrontLocalized.load_front_assets_on_dynamically;
                if ( true === handlerParams.force_loading ) {
                    load_authorized = true;
                }
                if ( !load_authorized )
                  return;

                if ( 1 > handlerParams.elements.length )
                  return true;

                // try on load
                try{ nb_.loadAssetWhenElementVisible( id, handlerParams ); } catch(er){
                    nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers', er, handlerParams );
                }

                // schedule on scroll
                // the scroll event is unbound once the scrollhandler is executed
                if( nb_.isFunction( handlerParams.func ) && nb_.isUndefined( handlerParams.scrollHandler ) ) {
                    handlerParams.scrollHandler = nb_.throttle( function() {
                        try{ nb_.loadAssetWhenElementVisible( id, handlerParams ); } catch(er){
                            nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers', er, handlerParams );
                        }
                    }, 100 );

                    nb_.cachedElements.$window.on( 'scroll', handlerParams.scrollHandler );
                } else if ( !nb_.isFunction( handlerParams.func ) ) {
                    nb_.errorLog('Nimble error => nb_.loopOnScrollHandlers => wrong callback func param', handlerParams );
                }

            });
        });
    };

    nb_.listenTo('nb-app-ready', function() {
        jQuery(function($){
            // nb_.scrollHandlers = [
            //    { id : 'swiper', elements : $(), func : function(){} }
            //    ...
            // ]

            // each time a new scroll handler is added, it emits the event 'nimble-new-scroll-handler-added'
            // so when caught, let's try to detect any dependant element is visible in the page
            // and if so, load.
            // Typically useful on page load if for example the slider is on top of the page and we need to load swiper-bundle.js right away before scrolling
            nb_.listenTo('nimble-new-scroll-handler-added', nb_.loopOnScrollHandlers );

        });//jQuery
    });
}(window, document));