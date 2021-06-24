//@global serverControlParams
var CZRSkopeBaseMths = CZRSkopeBaseMths || {};
( function ( api, $, _ ) {
$.extend( CZRSkopeBaseMths, {

    //////////////////////////////////////////////////////////
    /// <SKOPE HELPERSS>
    // @return string
    getSkopeProperty : function( what, skope_level ) {
        what = what || 'skope_id';
        skope_level = skope_level || 'local';
        var _skope_data = _.findWhere( api.czr_currentSkopesCollection(), { skope : skope_level });
        if ( _.isEmpty( _skope_data ) ){
            // display an error message if local skope not found. Should always be defined.
            if ( 'local' == skope_level ) {
                api.errorLog( "getSkopeProperty => local skope missing, returning not_set" );
            }
            return "_skope_not_set_";//for example when trying to get a group skope_id in a context where there can't be a group skope like home or search.
        } else if ( !_.has( _skope_data, what ) ) {
            api.errorLog( "getSkopeProperty => " + what + " property does not exist" );
            return "_skope_not_set_";
        } else {
            return _skope_data[ what ];
        }
    },


    //@return string
    firstToUpperCase : function( str ) {
          return ! _.isString( str ) ? '' : str.substr(0, 1).toUpperCase() + str.substr(1);
    }



    // //@return string
    // buildSkopeLink : function( skope_id ) {
    //     if ( ! api.czr_skope.has( skope_id ) ) {
    //         api.errorLog( 'buildSkopeLink : the requested skope id is not registered : ' + skope_id );
    //         return '';
    //     }
    //     var _link_title = [ serverControlParams.i18n.skope['Switch to scope'], api.czr_skope( skope_id )().title ].join(' : ');
    //     return [
    //         '<span class="czr-skope-switch" title=" ' + _link_title + '" data-skope-id="' + skope_id + '">',
    //         api.czr_skope( skope_id )().title,
    //         '</span>'
    //     ].join( '' );
    // },
});//$.extend
})( wp.customize , jQuery, _ );