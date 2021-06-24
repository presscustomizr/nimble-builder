
( function ( api, $, _ ) {

      api.bind( 'ready', function() {
            /*****************************************************************************
            * ADD PRO BEFORE SPECIFIC SECTIONS AND PANELS
            *****************************************************************************/
            if ( window.themeServerControlParams && themeServerControlParams.isPro ) {
                  _.each( [
                        //WFC
                        'tc_font_customizer_settings',

                        //hueman pro
                        'contx_header_bg',
                        'content_blog_sec',
                        'static_front_page',
                        'content_single_sec',

                        //customizr-pro
                        'tc_fpu',
                        'nav',
                        'post_lists_sec',
                        'galleries_sec',
                        'footer_customizer_sec',
                        'custom_scripts_sec',
                        'contact_info_sec'

                  ], function( _secId ) {
                        _.delay( function() {
                            api.section.when( _secId, function( _sec_ ) {
                                  if ( 1 >= _sec_.headContainer.length ) {
                                      _sec_.headContainer.find('.accordion-section-title').prepend( '<span class="pro-title-block">Pro</span>' );
                                  }
                            });
                        }, 1000 );
                  });
                  _.each( [
                        //hueman pro
                        //'hu-header-panel',
                        //'hu-content-panel',

                        //customizr-pro
                        //'tc-header-panel',
                        //'tc-content-panel',
                        //'tc-footer-panel',
                        //'tc-advanced-panel'
                  ], function( _secId ) {
                        api.panel.when( _secId, function( _sec_ ) {
                              if ( 1 >= _sec_.headContainer.length ) {
                                  _sec_.headContainer.find('.accordion-section-title').prepend( '<span class="pro-title-block">Pro</span>' );
                              }
                        });
                  });
            }


            /*****************************************************************************
            * PRO SECTION OVERRIDE
            *****************************************************************************/
            if ( ! themeServerControlParams.isPro && _.isFunction( api.Section ) ) {
                  proSectionInstance = api.section('go_pro_sec');
                  if ( ! _.isObject( proSectionInstance ) )
                    return;

                  // No events for this type of section.
                  proSectionInstance.attachEvents = function () {};
                  // Always make the section active.
                  proSectionInstance.isContextuallyActive = function () {
                    return this.active();
                  };
                  proSectionInstance._toggleActive = function(){ return true; };

                  proSectionInstance.active( true );
            }
      });

})( wp.customize , jQuery, _);