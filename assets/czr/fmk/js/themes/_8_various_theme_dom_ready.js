
//DOM READY :
//1) FIRE SPECIFIC INPUT PLUGINS
//2) ADD SOME COOL STUFFS
//3) SPECIFIC CONTROLS ACTIONS
( function ( wp, $ ) {
      $( function($) {
            var api = wp.customize || api;

            //WHAT IS HAPPENING IN THE MESSENGER
            // $(window.parent).on( 'message', function(e, o) {
            //   api.consoleLog('SENT STUFFS', JSON.parse( e.originalEvent.data), e );
            // });
            // $( window ).on( 'message', function(e, o) {
            //   api.consoleLog('INCOMING MESSAGE', JSON.parse( e.originalEvent.data), e );
            // });
            // $(window.document).bind("ajaxSend", function(e, o){
            //    api.consoleLog('AJAX SEND', e, arguments );
            // }).bind("ajaxComplete", function(e, o){
            //    api.consoleLog('AJAX COMPLETE', e, o);
            // });

            /* RECENTER CURRENT SECTIONS */
            $('.accordion-section').not('.control-panel').on('click', function () {
                  _recenter_current_section($(this));
            });

            function _recenter_current_section( section ) {
                  var $siblings               = section.siblings( '.open' );
                  //check if clicked element is above or below sibling with offset.top
                  if ( 0 !== $siblings.length &&  $siblings.offset().top < 0 ) {
                        $('.wp-full-overlay-sidebar-content').animate({
                              scrollTop:  - $('#customize-theme-controls').offset().top - $siblings.height() + section.offset().top + $('.wp-full-overlay-sidebar-content').offset().top
                        }, 700);
                  }
            }//end of fn


            /* CHECKBOXES */
            api.czrSetupCheckbox = function( controlId, refresh ) {
                  var _ctrl = api.control( controlId );
                  $('input[type=checkbox]:not(.nimblecheck-input)', _ctrl.container ).each( function() {
                        //Exclude font customizer
                        if ( 'tc_font_customizer_settings' == _ctrl.params.section )
                          return;
                        //first fix the checked / unchecked status
                        if ( 0 === $(this).val() || '0' == $(this).val() || 'off' == $(this).val() || _.isEmpty($(this).val() ) ) {
                              $(this).prop('checked', false);
                        } else {
                              $(this).prop('checked', true);
                        }

                        //then render icheck if not done already
                        if ( 0 !== $(this).closest('div[class^="icheckbox"]').length )
                          return;

                        $(this).iCheck({
                              checkboxClass: 'icheckbox_flat-grey',
                              //checkedClass: 'checked',
                              radioClass: 'iradio_flat-grey',
                        })
                        .on( 'ifChanged', function(e){
                              $(this).val( false === $(this).is(':checked') ? 0 : 1 );
                              $(e.currentTarget).trigger('change');
                        });
                  });
            };//api.czrSetupCheckbox()

            /* SELECT INPUT */
            api.czrSetupSelect = function(controlId, refresh) {
                  //Exclude no-selecter-js
                  $('select[data-customize-setting-link]', api.control(controlId).container )
                        .not('.no-selecter-js')
                        .each( function() {
                              $(this).selecter({
                              //triggers a change event on the view, passing the newly selected value + index as parameters.
                              // callback : function(value, index) {
                              //   self.triggerSettingChange( window.event || {} , value, index); // first param is a null event.
                              // }
                              });
                        });
            };//api.czrSetupSelect()


            /* NUMBER INPUT */
            api.czrSetupStepper = function( controlId, refresh ) {
                  var _ctrl = api.control( controlId );
                  $('input[type="number"]', _ctrl.container ).each( function() { $(this).stepper(); });
            };//api.czrSetupStepper()

            // LOOP ON EACH CONTROL REGISTERED AND INSTANTIATE THE PLUGINS
            // @todo => react on control added
            api.control.each( function( control ){
                  if ( ! _.has( control, 'id' ) )
                    return;
                  //exclude widget controls and menu controls for checkboxes
                  if ( 'widget_' != control.id.substring(0, 'widget_'.length ) && 'nav_menu' != control.id.substring( 0, 'nav_menu'.length ) ) {
                        api.czrSetupCheckbox(control.id);
                  }
                  if ( 'nav_menu_locations' != control.id.substring( 0, 'nav_menu_locations'.length ) ) {
                        api.czrSetupSelect(control.id);
                  }

                  // Stepper : exclude controls from specific sections
                  var _exclude = [
                       'publish_settings', //<= the outer section introduced in v4.9 to publish / saved draft / schedule
                       'tc_font_customizer_settings' //the font customizer plugin has its own way to instantiate the stepper, with custom attributes previously set to the input like step, min, etc...
                  ];

                  if ( 0 < control.container.find( 'input[type="number"]' ).length && control.params && control.params.section && ! _.contains( _exclude,  control.params.section ) ) {
                        api.czrSetupStepper(control.id);
                  }
            });
      });//end of $( function($) ) dom ready
})( wp, jQuery );