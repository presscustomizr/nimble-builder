<?php
namespace Nimble;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'customize_controls_print_footer_scripts', '\Nimble\sek_print_nimble_input_templates' );
function sek_print_nimble_input_templates() {


      // data structure :
      // {
      //     input_type : input_type,
      //     input_data : input_data,
      //     input_id : input_id,
      //     item_model : item_model,
      //     input_tmpl : wp.template( 'nimble-input___' + input_type )
      // }
      ?>
      <script type="text/html" id="tmpl-nimble-input-wrapper">
        <# var css_attr = serverControlParams.css_attr,
            input_data = data.input_data,
            input_type = input_data.input_type,
            is_width_100 = true === input_data['width-100'];


        // some inputs have a width of 100% even if not specified in the input_data
        if ( _.contains( ['color', 'radio', 'textarea'], input_type ) ) {
            is_width_100 = true;
        }
        var width_100_class = is_width_100 ? 'width-100' : '',
            hidden_class = 'hidden' === input_type ? 'hidden' : '',
            data_transport_attr = !_.isEmpty( input_data.transport ) ? 'data-transport="' + input_data.transport + '"' : '',
            input_width = !_.isEmpty( input_data.input_width ) ? input_data.input_width : '';
        #>

        <div class="{{css_attr.sub_set_wrapper}} {{width_100_class}} {{hidden_class}}" data-input-type="{{input_type}}" {{data_transport_attr}}>
          <# if ( input_data.html_before ) { #>
            <div class="czr-html-before"><# print(input_data.html_before); #></div>
          <# } #>
          <# if ( input_data.notice_before_title ) { #>
            <span class="czr-notice"><# print(input_data.notice_before_title); #></span><br/>
          <# } #>
          <# if ( 'hidden' !== input_type ) { #>
            <# var title_width = ! _.isEmpty( input_data.title_width ) ? input_data.title_width : ''; #>
            <div class="customize-control-title {{title_width}}"><# print( input_data.title ); #></div>
          <# } #>
          <# if ( input_data.notice_before ) { #>
            <span class="czr-notice"><# print(input_data.notice_before); #></span>
          <# } #>

          <?php // nested template, see https://stackoverflow.com/questions/8938841/underscore-js-nested-templates#13649447 ?>
          <?php // about print(), see https://underscorejs.org/#template ?>
          <div class="czr-input {{input_width}}"><# if ( _.isFunction( data.input_tmpl ) ) { print( data.input_tmpl( data ) ); } #></div>

          <# if ( input_data.notice_after ) { #>
            <span class="czr-notice"><# print(input_data.notice_after); #></span>
          <# } #>
          <# if ( input_data.html_after ) { #>
            <div class="czr-html-after"><# print(input_data.html_after); #></div>
          <# } #>
        </div><?php //css_attr.sub_set_wrapper ?>
      </script>










      <?php
      /* ------------------------------------------------------------------------- *
       *  SUBTEMPLATES
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-subtemplate___range_number">
        <?php
          // we save the int value + unit
          // we want to keep only the numbers when printing the tmpl
          // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
        ?>
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null,
              unit;

          value = _.isString( value ) ? value.replace(/px|em|%/g,'') : value;
          unit = _.isString( value ) ? value.replace(/[0-9]|\.|,/g, '') : 'px';
          unit = _.isEmpty( unit ) ? 'px' : unit;
          var _step = _.has( item_model, 'step' ) ? 'step="' + item_model.step + '"' : '',
              _saved_unit = _.has( item_model, 'unit' ) ? 'data-unit="' + item_model.unit + '"' : '',
              _min = _.has( item_model, 'min' ) ? 'min="' + item_model.min + '"': '',
              _max = _.has( item_model, 'max' ) ? 'max="' + item_model.max + '"': '';
        #>
        <div class="sek-range-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden" data-sek-unit="{{unit}}"/>
          <input class="sek-range-input" type="range" {{_step}} {{_saved_unit}} {{_min}} {{_max}}/>
        </div>
        <div class="sek-number-wrapper">
            <input class="sek-pm-input" value="{{value}}" type="number"  >
        </div>
      </script>


      <script type="text/html" id="tmpl-nimble-subtemplate___unit_picker">
          <div class="sek-unit-wrapper">
            <div aria-label="<?php _e('unit', 'text_doma'); ?>" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_doma'); ?>" data-sek-unit="px">px</button><button type="button" aria-pressed="false" class="sek-ui-button" title="em" data-sek-unit="em">em</button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_doma'); ?>" data-sek-unit="%">%</button></div>
          </div>
      </script>












      <?php
      /* ------------------------------------------------------------------------- *
       * CODE EDITOR
      /* ------------------------------------------------------------------------- */
      ?>
      <?php
      // data structure :
      // {
      //     input_type : input_type,
      //     input_data : input_data,
      //     input_id : input_id,
      //     item_model : item_model,
      //     input_tmpl : wp.template( 'nimble-input___' + input_type )
      // }
      ?>

      <script type="text/html" id="tmpl-nimble-input___code_editor">
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null,
              code_type = data.input_data.code_type;
        #>
        <textarea data-czrtype="{{input_id}}" data-editor-code-type="{{code_type}}" class="width-100" name="textarea" rows="10" cols="">{{ value }}</textarea>
      </script>



      <script type="text/html" id="tmpl-nimble-input___detached_tinymce_editor">
        <#
          var input_data = data.input_data,
              item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null,
              code_type = data.input_data.code_type;
        #>
        <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{input_id}}" data-czr-action="open-tinymce-editor"><?php _e('Edit', 'text_doma'); ?></button>&nbsp;
        <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{input_id}}" data-czr-action="close-tinymce-editor"><?php _e('Hide editor', 'text_doma'); ?></button>
        <input data-czrtype="{{input_id}}" type="hidden" value="{{value}}"/>
      </script>

      <script type="text/html" id="tmpl-nimble-input___nimble_tinymce_editor">
        <?php
        // Added an id attribute for https://github.com/presscustomizr/nimble-builder/issues/403
        // needed to instantiate wp.editor.initialize(...)
        ?>
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null;
        #>
        <textarea id="textarea-{{data.control_id}}" data-czrtype="{{input_id}}" class="width-100" name="textarea" rows="10" cols="">{{value}}</textarea>
      </script>



      <script type="text/html" id="tmpl-nimble-input___h_alignment">
        <#
          var input_id = data.input_id;
        #>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left', 'text_doma'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center', 'text_doma'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right', 'text_doma'); ?>"><i class="material-icons">format_align_right</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
      </script>


      <script type="text/html" id="tmpl-nimble-input___h_text_alignment">
        <#
          var input_id = data.input_id;
        #>
        <div class="sek-h-align-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="left" title="<?php _e('Align left', 'text_doma'); ?>"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="<?php _e('Align center', 'text_doma'); ?>"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="<?php _e('Align right', 'text_doma'); ?>"><i class="material-icons">format_align_right</i></div>
            <div data-sek-align="justify" title="<?php _e('Justified', 'text_doma'); ?>"><i class="material-icons">format_align_justify</i></div>
          </div>
        </div><?php // sek-h-align-wrapper ?>
      </script>


      <script type="text/html" id="tmpl-nimble-input___nimblecheck">
        <#
          var input_id = data.input_id,
          item_model = data.item_model,
          value = _.has( item_model, input_id ) ? item_model[input_id] : false,
          _checked = ( false != value ) ? "checked=checked" : '',
          _uniqueId = wp.customize.czr_sektions.guid();
        #>
        <div class="nimblecheck-wrap">
          <input id="nimblecheck-{{_uniqueId}}" data-czrtype="{{input_id}}" type="checkbox" {{ _checked }} class="nimblecheck-input">
          <label for="nimblecheck-{{_uniqueId}}" class="nimblecheck-label">{{sektionsLocalizedData.i18n['Switch']}}</label>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  FONT SIZE / LINE HEIGHT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___font_size_line_height">
        <?php
          // we save the int value + unit
          // we want to keep only the numbers when printing the tmpl
          // dev note : value.replace(/\D+/g, '') : ''; not working because remove "." which we might use for em for example
        ?>
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null,
              unit;

          value = _.isString( value ) ? value.replace(/px|em|%/g,'') : value;
          unit = _.isString( value ) ? value.replace(/[0-9]|\.|,/g, '') : 'px';
          unit = _.isEmpty( unit ) ? 'px' : unit;
          var _step = _.has( item_model, 'step' ) ? 'step="' + item_model.step + '"' : '',
              _saved_unit = _.has( item_model, 'unit' ) ? 'data-unit="' + item_model.unit + '"' : '',
              _min = _.has( item_model, 'min' ) ? 'min="' + item_model.min + '"': '',
              _max = _.has( item_model, 'max' ) ? 'max="' + item_model.max + '"': '';
        #>
        <div class="sek-font-size-line-height-wrapper">
          <input data-czrtype="{{input_id}}" type="hidden" data-sek-unit="{{unit}}"/>
          <div aria-label="<?php _e('unit', 'text_doma'); ?>" class="sek-ui-button-group sek-float-right" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('pixels', 'text_doma'); ?>" data-sek-unit="px">px</button><button type="button" aria-pressed="false" class="sek-ui-button" title="em" data-sek-unit="em">em</button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('percents', 'text_doma'); ?>" data-sek-unit="%">%</button></div>
          </div>
        </div>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  ALPHA COLOR
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___wp_color_alpha">
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null;
        #>
        <input data-czrtype="{{data.input_id}}" class="width-100"  data-alpha="true" type="text" value="{{value}}"></input>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  SIMPLE SELECT : USED FOR SELECT, FONT PICKER, ICON PICKER, ...
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___simpleselect">
        <select data-czrtype="{{data.input_id}}"></select>
      </script>




      <?php
      /* ------------------------------------------------------------------------- *
       *  RANGE
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___range_simple">
        <div class="sek-range-with-unit-picker-wrapper sek-no-unit-picker">
          <#
            var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
            if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
          #>
        </div>
      </script>


      <script type="text/html" id="tmpl-nimble-input___range_with_unit_picker">
        <div class="sek-range-with-unit-picker-wrapper">
            <#
              var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
              if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
        </div>
      </script>




      <?php
      /* ------------------------------------------------------------------------- *
       *  SPACING
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___spacing">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-spacing-wrapper">
            <div class="sek-pad-marg-inner">
              <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="margin-top" title="<?php _e('Margin top', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
              <div class="sek-pm-middle-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch sek-pm-margin-left" data-sek-spacing="margin-left" title="<?php _e('Margin left', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>

                <div class="sek-pm-padding-wrapper">
                  <div class="sek-flex-justify-center">
                    <div class="sek-flex-center-stretch" data-sek-spacing="padding-top" title="<?php _e('Padding top', 'text_doma'); ?>">
                      <div class="sek-pm-input-parent">
                        <input class="sek-pm-input" value="" type="number"  >
                      </div>
                    </div>
                  </div>
                    <div class="sek-flex-justify-center sek-flex-space-between">
                      <div class="sek-flex-center-stretch" data-sek-spacing="padding-left" title="<?php _e('Padding left', 'text_doma'); ?>">
                        <div class="sek-pm-input-parent">
                          <input class="sek-pm-input" value="" type="number"  >
                        </div>
                      </div>
                      <div class="sek-flex-center-stretch" data-sek-spacing="padding-right" title="<?php _e('Padding right', 'text_doma'); ?>">
                        <div class="sek-pm-input-parent">
                          <input class="sek-pm-input" value="" type="number"  >
                        </div>
                      </div>
                    </div>
                  <div class="sek-flex-justify-center">
                    <div class="sek-flex-center-stretch" data-sek-spacing="padding-bottom" title="<?php _e('Padding bottom', 'text_doma'); ?>">
                      <div class="sek-pm-input-parent">
                        <input class="sek-pm-input" value="" type="number"  >
                      </div>
                    </div>
                  </div>
                </div>

                <div class="sek-flex-center-stretch sek-pm-margin-right" data-sek-spacing="margin-right" title="<?php _e('Margin right', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>

              <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="margin-bottom" title="<?php _e('Margin bottom', 'text_doma'); ?>">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
            </div><?php //sek-pad-marg-inner ?>

            <#
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
            <div class="reset-spacing-wrap"><span class="sek-do-reset"><?php _e('Reset all spacing', 'text_doma' ); ?></span></div>

        </div><?php // sek-spacing-wrapper ?>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  TEXT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___text">
        <# var input_data = data.input_data; #>
        <input data-czrtype="{{data.input_id}}" type="text" value="" placeholder="{{input_data.placeholder}}"></input>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  CONTENT PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___content_picker">
        <span data-czrtype="{{data.input_id}}"></span>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  UPLOAD
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___upload">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="{{serverControlParams.css_attr.img_upload_container}}"></div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  BORDERS
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___borders">
        <div class="sek-borders">
          <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text_doma'); ?>" data-sek-border-type="_all_"><?php _e('All', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Left', 'text_doma'); ?>" data-sek-border-type="left"><?php _e('Left', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top', 'text_doma'); ?>" data-sek-border-type="top"><?php _e('Top', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Right', 'text_doma'); ?>" data-sek-border-type="right"><?php _e('Right', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom', 'text_doma'); ?>" data-sek-border-type="bottom"><?php _e('Bottom', 'text_doma'); ?></button></div>
          </div>
          <div class="sek-range-unit-wrapper">
            <#
              var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
              if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
          </div>
          <div class="sek-color-wrapper">
              <div class="sek-color-picker"><input class="sek-alpha-color-input" data-alpha="true" type="text" value=""/></div>
              <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e('Reset', 'text_doma'); ?></button></div>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  BORDER RADIUS
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___border_radius">
        <div class="sek-borders">
          <div class="sek-border-type-wrapper">
            <div aria-label="unit" class="sek-ui-button-group sek-float-left" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="<?php _e('All', 'text_doma'); ?>" data-sek-radius-type="_all_"><?php _e('All', 'text_doma'); ?></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top left', 'text_doma'); ?>" data-sek-radius-type="top_left"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Top right', 'text_doma'); ?>" data-sek-radius-type="top_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom right', 'text_doma'); ?>" data-sek-radius-type="bottom_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Bottom left', 'text_doma'); ?>" data-sek-radius-type="bottom_left"><i class="material-icons">border_style</i></button></div>
            <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right"><?php _e('Reset', 'text_doma'); ?></button></div>
          </div>
          <div class="sek-range-unit-wrapper">
            <#
              var range_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'range_number' );
              if ( _.isFunction( range_tmpl ) ) { print( range_tmpl( data ) ); }
              var unit_tmpl = wp.customize.CZR_Helpers.getInputSubTemplate( 'unit_picker' );
              if ( _.isFunction( unit_tmpl ) ) { print( unit_tmpl( data ) ); }
            #>
          </div>
        </div>
      </script>




      <?php
      /* ------------------------------------------------------------------------- *
       *  CONTENT SWITCHER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___content_type_switcher">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <div aria-label="<?php _e('Content type', 'text_doma'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a section', 'text_doma'); ?>" data-sek-content-type="section"><?php _e('Pick a section', 'text_doma'); ?></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a module', 'text_doma'); ?>" data-sek-content-type="module"><?php _e('Pick a module', 'text_doma'); ?></button>
            </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  MODULE PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___module_picker">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <#
            var icon_img_html = '<i style="color:red">Missing Icon</i>', icon_img_src;

            _.each( sektionsLocalizedData.moduleCollection, function( rawModData ) {
                //normalizes the module params
                var modData = $.extend( true, {}, rawModData ),
                defaultModParams = {
                  'content-type' : 'module',
                  'content-id' : '',
                  'title' : '',
                  'icon' : '',
                  'font_icon' : '',
                  'active' : true
                },
                modData = $.extend( defaultModParams, modData );

                if ( ! _.isEmpty( modData['icon'] ) ) {
                    icon_img_src = sektionsLocalizedData.moduleIconPath + modData['icon'];
                    icon_img_html = '<img draggable="false" title="' + modData['title'] + '" alt="' +  modData['title'] + '" class="nimble-module-icons" src="' + icon_img_src + '"/>';
                } else if ( ! _.isEmpty( modData['font_icon'] ) ) {
                    icon_img_html = modData['font_icon'];
                }
                var title_attr = "<?php _e('Drag and drop or double-click to insert in your chosen target element.', 'text_doma'); ?>",
                    font_icon_class = !_.isEmpty( modData['font_icon'] ) ? 'is-font-icon' : '',
                    is_draggable = true !== modData['active'] ? 'false' : 'true';
                if ( true !== modData['active'] ) {
                    title_attr = "<?php _e('Available soon ! This module is currently in beta, you can activate it in Site Wide Options > Beta features', 'text_doma'); ?>";
                }
                #>
                <div draggable="{{is_draggable}}" data-sek-content-type="{{modData['content-type']}}" data-sek-content-id="{{modData['content-id']}}" title="{{title_attr}}"><div class="sek-module-icon {{font_icon_class}}"><# print(icon_img_html); #></div><div class="sek-module-title"><div class="sek-centered-module-title">{{modData['title']}}</div></div></div>
                <#
            });//_.each
            #>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  SECTION PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___section_picker">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <#
            var section_collection = ( data.input_data && data.input_data.section_collection ) ? data.input_data.section_collection : [];
            if ( _.isEmpty( section_collection ) ) {
                wp.customize.errare('Error in js template tmpl-nimble-input___section_picker => missing section collection');
                return;
            }

            _.each( section_collection, function( rawSecParams ) {
                //normalizes the params
                var section_type = 'content',
                secParams = $.extend( true, {}, rawSecParams ),
                defaultParams = {
                  'content-id' : '',
                  'thumb' : '',
                  'title' : '',
                  'section_type' : '',
                  'height': ''
                },
                modData = $.extend( defaultParams, secParams );

                if ( ! _.isEmpty( secParams['section_type'] ) ) {
                    section_type = secParams['section_type'];
                }

                var thumbUrl = [ sektionsLocalizedData.baseUrl , '/assets/img/section_assets/thumbs/', secParams['thumb'] ,  '?ver=' , sektionsLocalizedData.nimbleVersion ].join(''),
                styleAttr = 'background: url(' + thumbUrl  + ') 50% 50% / cover no-repeat;';
                if ( !_.isEmpty(secParams['height']) ) {
                    styleAttr = styleAttr + 'height:' + secParams['height'] + ';';
                }

                #>
                <div draggable="true" data-sek-content-type="preset_section" data-sek-content-id="{{secParams['content-id']}}" style="<# print(styleAttr); #>" title="{{secParams['title']}}" data-sek-section-type="{{section_type}}"><div class="sek-overlay"></div></div>
                <#
            });//_.each
            #>
        </div>
      </script>



      <?php
      /* ------------------------------------------------------------------------- *
       *  BACKGROUND POSITION INPUT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___bg_position">
        <div class="sek-bg-pos-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_left">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M14.96 16v-1h-1v-1h-1v-1h-1v-1h-1v-1.001h-1V14h-1v-4-1h5v1h-3v.938h1v.999h1v1h1v1.001h1v1h1V16h-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M14.969 12v-1h-1v-1h-1v7h-1v-7h-1v1h-1v1h-1v-1.062h1V9.937h1v-1h1V8h1v.937h1v1h1v1.001h1V12h-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="top_right">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M9.969 16v-1h1v-1h1v-1h1v-1h1v-1.001h1V14h1v-4-1h-1-4v1h3v.938h-1v.999h-1v1h-1v1.001h-1v1h-1V16h1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="left">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M11.469 9.5h-1v1h-1v1h7v1h-7v1h1v1h1v1h-1.063v-1h-1v-1h-1v-1h-.937v-1h.937v-1h1v-1h1v-1h1.063v1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="center">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M12 9a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="right">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M12.469 14.5h1v-1h1v-1h-7v-1h7v-1h-1v-1h-1v-1h1.062v1h1v1h1v1h.938v1h-.938v1h-1v1h-1v1h-1.062v-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_left">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M14.969 9v1h-1v1h-1v1h-1v1h-1v1.001h-1V11h-1v5h5v-1h-3v-.938h1v-.999h1v-1h1v-1.001h1v-1h1V9h-1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M9.969 13v1h1v1h1V8h1v7h1v-1h1v-1h1v1.063h-1v.999h-1v1.001h-1V17h-1v-.937h-1v-1.001h-1v-.999h-1V13h1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
          <label class="sek-bg-pos">
            <input type="radio" name="sek-bg-pos" value="bottom_right">
            <span>
              <svg width="24" height="24">
                <path id="sek-pth" fill-rule="evenodd" d="M9.969 9v1h1v1h1v1h1v1h1v1.001h1V11h1v5h-1-4v-1h3v-.938h-1v-.999h-1v-1h-1v-1.001h-1v-1h-1V9h1z" class="sek-svg-bg-pos">
                </path>
              </svg>
            </span>
          </label>
        </div><?php // sek-bg-pos-wrapper ?>
      </script>

      <?php
      /* ------------------------------------------------------------------------- *
       *  BUTTON CHOICE
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___buttons_choice">
        <div class="sek-button-choice-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div aria-label="<?php _e('unit', 'text_doma'); ?>" class="sek-ui-button-group sek-float-right" role="group">
              <#
                var input_data = data.input_data;
                if ( _.isEmpty( input_data.choices ) || !_.isObject( input_data.choices ) ) {
                    wp.customize.errare( 'Error in buttons_choice js tmpl => missing or invalid input_data.choices');
                } else {
                    _.each( input_data.choices, function( label, choice ) {
                        #><button type="button" aria-pressed="false" class="sek-ui-button" title="{{label}}" data-sek-choice="{{choice}}">{{label}}</button><#
                    });
                }
              #>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  MULTISELECT, CATEGORY PICKER
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___multiselect">
        <select multiple="multiple" data-czrtype="{{data.input_id}}"></select>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  GRID LAYOUT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___grid_layout">
        <div class="sek-grid-layout-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div class="sek-grid-icons">
            <div data-sek-grid-layout="list" title="<?php _e('List layout', 'text_doma'); ?>"><i class="material-icons">view_list</i></div>
            <div data-sek-grid-layout="grid" title="<?php _e('Grid layout', 'text_doma'); ?>"><i class="material-icons">view_module</i></div>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  VERTICAL ALIGNMENT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___v_alignment">
        <div class="sek-v-align-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <div class="sek-align-icons">
            <div data-sek-align="top" title="<?php _e('Align top', 'text_doma'); ?>"><i class="material-icons">vertical_align_top</i></div>
            <div data-sek-align="center" title="<?php _e('Align center', 'text_doma'); ?>"><i class="material-icons">vertical_align_center</i></div>
            <div data-sek-align="bottom" title="<?php _e('Align bottom', 'text_doma'); ?>"><i class="material-icons">vertical_align_bottom</i></div>
          </div>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  RESET BUTTON
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___reset_button">
        <div class="sek-button-choice-wrapper">
          <input data-czrtype="{{data.input_id}}" type="hidden"/>
          <button type="button" aria-pressed="false" class="sek-ui-button sek-float-right" title="<?php _e('Reset', 'text_doma'); ?>" data-sek-reset-scope="{{data.input_data.scope}}"><?php _e('Reset', 'text_doma'); ?></button>
        </div>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  REVISION HISTORY / HIDDEN
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___revision_history">
        <input data-czrtype="{{data.input_id}}" type="hidden"/>
      </script>


      <?php
      /* ------------------------------------------------------------------------- *
       *  IMPORT / EXPORT
      /* ------------------------------------------------------------------------- */
      ?>
      <script type="text/html" id="tmpl-nimble-input___import_export">
        <div class="sek-export-btn-wrap">
          <div class="customize-control-title width-100"><?php //_e('Export', 'text_doma'); ?></div>
          <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-export"><?php _e('Export', 'text_doma' ); ?></button>
        </div>
        <div class="sek-import-btn-wrap">
          <div class="customize-control-title width-100"><?php _e('IMPORT', 'text_doma'); ?></div>
          <span class="czr-notice"><?php _e('Select the file to import and click on Import button.', 'text_doma' ); ?></span>
          <span class="czr-notice"><?php _e('Be sure to import a file generated with Nimble Builder export system.', 'text_doma' ); ?></span>
          <div class="czr-import-dialog notice notice-info">
              <div class="czr-import-message"><?php _e('Some of the imported sections need a location that is not active on this page. Sections in missing locations will not be rendered. You can continue importing or assign those sections to a contextually active location.', 'text_doma' ); ?></div>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-import-as-is"><?php _e('Import without modification', 'text_doma' ); ?></button>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-import-assign"><?php _e('Import in existing locations', 'text_doma' ); ?></button>
              <button type="button" class="button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-cancel-import"><?php _e('Cancel import', 'text_doma' ); ?></button>
          </div>
          <div class="sek-uploading"><?php _e( 'Uploading...', 'text_doma' ); ?></div>
          <input type="file" name="sek-import-file" class="sek-import-file" />
          <input type="hidden" name="sek-skope" value="{{data.input_data.scope}}" />
          <button type="button" class="button disabled" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{data.input_id}}" data-czr-action="sek-pre-import"><?php _e('Import', 'text_doma' ); ?></button>

        </div>
        <input data-czrtype="{{data.input_id}}" type="hidden" value="{{data.value}}"/>
      </script>
      <?php
}//sek_print_nimble_input_templates() @hook 'customize_controls_print_footer_scripts'



?>