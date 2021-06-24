
//named czr_multiple_picker in the php setting map
var CZRMultiplePickerMths = CZRMultiplePickerMths || {};
/* Multiple Picker */
/**
 * @constructor
 * @augments wp.customize.Control
 * @augments wp.customize.Class
 */
( function ( api, $, _ ) {
$.extend( CZRMultiplePickerMths , {
      ready: function() {
            var control  = this,
                _select  = this.container.find('select');


            _select.czrSelect2({
                  closeOnSelect: false,
                  templateSelection: czrEscapeMarkup
            });

            function czrEscapeMarkup(obj) {
                  //trim dashes
                  return obj.text.replace(/\u2013|\u2014/g, "");
            }

            //handle case when all choices become unselected
            _select.on('change', function(e){
                  if ( 0 === $(this).find("option:selected").length )
                    control.setting.set([]);
            });
      }
});//$.extend
})( wp.customize , jQuery, _ );