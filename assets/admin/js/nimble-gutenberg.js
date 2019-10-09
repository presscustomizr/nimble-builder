// Introduced for https://github.com/presscustomizr/nimble-builder/issues/449
(function ($) {
    var button_printed = false,
        _callback = function () {
            if ( button_printed )
              return;
            if ( $('#editor').find('.edit-post-header-toolbar').length > 0 ) {
                var html = $($('#sek-edit-with-nb').html());
                $('#editor').find('.edit-post-header-toolbar').append( html );
                button_printed = true;
                // unsubscribe the listener
                // documented here : https://developer.wordpress.org/block-editor/packages/packages-data/#subscribe
                wp.data.subscribe(_callback)();
            }
        };

    // PRINT THE BUTTON WHEN element .edit-post-header-toolbar has been rendered
    // wp.data.subscribe() documented here : https://developer.wordpress.org/block-editor/packages/packages-data/#subscribe
    // Given a listener function, the function will be called any time the state value
    // of one of the registered stores has changed. This function returns a unsubscribe
    // function used to stop the subscription.
    wp.data.subscribe(_callback);

    // ATTACH EVENT LISTENER
    // with delegation
    // When editing an existing post, the customizer url is rendered server side in the data-cust-url='' attribute
    // @see inc/admin/nimble-admin.php => sek_print_nb_btn_edit_with()
    //
    // When creating a new post, the customizer url is generated with an ajax call
    $('body').on( 'click', '#sek-edit-with-nimble', function(evt) {
        evt.preventDefault();
        var $clickedEl = $(this),
            _url = $clickedEl.data('cust-url'),
            attempts = 0,
            _openCustomizer = function( customizer_url ) {
                // We don't want to enter in an infinite loop, that's why the number of attempts is limited to 5 if isSavingPost()
                if ( wp.data.select('core/editor').isSavingPost() && attempts < 5 ) {
                    _.delay(function () {
                          self._openCustomizer();
                          attempts++;
                    }, 300 );
                } else {
                    location.href = customizer_url;
                }
                //$clickedEl.removeClass('sek-loading-customizer');
            };

        if ( _.isEmpty( _url ) ) {
            // introduced for https://github.com/presscustomizr/nimble-builder/issues/509
            $clickedEl.addClass('sek-loading-customizer').removeClass('button-primary');

            // for new post, the url is empty, let's generate it server side with an ajax call
            var post_id = wp.data.select('core/editor').getCurrentPostId();
            wp.ajax.post( 'sek_get_customize_url_for_nimble_edit_button', {
                nimble_edit_post_id : post_id
            }).done( function( resp ) {
                _openCustomizer( resp );
            }).fail( function( resp ) {
                $clickedEl.removeClass('sek-loading-customizer').addClass('button-primary');

                // If the ajax request fails, let's save the draft with a Nimble Builder title, and refresh the page, so the url is generated server side on next load.
                var post_title = wp.data.select('core/editor').getEditedPostAttribute('title');
                if ( !post_title ) {
                    wp.data.dispatch('core/editor').editPost({ title: 'Nimble Builder #' + post_id });
                }
                wp.data.dispatch('core/editor').savePost();
                _.delay(function () {
                    // off the javascript pop up warning if post not saved yet
                    $( window ).off( 'beforeunload' );
                    location.href = location.href;
                }, 300 );
            });
        } else {
            _openCustomizer( _url );
        }
    });//.on( 'click'

})(jQuery);