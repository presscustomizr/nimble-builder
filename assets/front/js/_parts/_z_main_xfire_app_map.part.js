var czrapp = czrapp || {};
//@global sekFrontLocalized
/************************************************
* LET'S DANCE
*************************************************/
( function ( czrapp ) {
      //adds the server params to the app now
      //czrapp.localized = window.sekFrontLocalized || {};

      //THE DEFAULT MAP
      //Other methods can be hooked. @see czrapp.customMap
      var appMap = {
                base : {
                      ctor : czrapp.Base,
                      ready : [
                            'cacheProp'
                      ]
                },
                // browserDetect : {
                //       ctor : czrapp.Base.extend( czrapp.methods.BrowserDetect ),
                //       ready : [ 'addBrowserClassToBody' ]
                // },
                jqPlugins : {
                      ctor : czrapp.Base.extend( czrapp.methods.JQPlugins ),
                      ready : [
                            'centerImagesWithDelay',
                            // 'imgSmartLoad',
                            //'dropCaps',
                            //'extLinks',
                            // 'lightBox',
                            // 'parallax'
                      ]
                },
                // userXP : {
                //       ctor : czrapp.Base.extend( czrapp.methods.UserXP ),
                //       ready : [
                //             'setupUIListeners',//<= setup various observable values like this.isScrolling, this.scrollPosition, ...

                //             'variousHoverActions',
                //             'formFocusAction',

                //             'smoothScroll',

                //             'attachmentsFadeEffect',

                //             'onEscapeKeyPressed',

                //             'featuredPagesAlignment',

                //             'anchorSmoothScroll',

                //             'mayBePrintFrontNote',
                //       ]
                // }
      };//map

      //set the observable value
      //listened to by _instantianteAndFireOnDomReady = function( newMap, previousMap, isInitial )
      czrapp.appMap( appMap , true );//true for isInitial map

})( czrapp );