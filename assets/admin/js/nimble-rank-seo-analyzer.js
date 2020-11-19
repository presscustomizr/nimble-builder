/**
 * Rank Math SEO Integration
 * Nov 2020 Add Nimble Builder content to RM analyzer
 * doc https://rankmath.com/kb/content-analysis-api/
 */
;( function( $ ) {
    jQuery(function($){
        var nimblePluginForRankMath = function( skope_id ) {
            wp.ajax.post( 'sek_get_nimble_content_for_seo_plugins', {
                skope_id : skope_id
            }).done( function( nimbleContent ) {
                wp.hooks.addFilter( 'rank_math_content', 'nimblePlugin', function( originalContent ) {
                    return originalContent + nimbleContent;
                });
                rankMathEditor.refresh( 'content' );
            }).fail( function( er ) {
                console.log('NimblePlugin for Rank Math => error when fetching Nimble content.');
            });
        };
        if ( window.nb_skope_id_for_rank_math_seo ) {
            nimblePluginForRankMath( window.nb_skope_id_for_rank_math_seo );
        } else {
            $(document).on('nb-skope-id-ready.rank-math', function( evt, params ) {
                if ( params && params.skope_id ) {
                    nimblePluginForRankMath( params.skope_id );
                }
            });
        }
    });
})( jQuery );