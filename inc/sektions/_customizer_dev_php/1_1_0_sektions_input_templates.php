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
            <div aria-label="{{sektionsLocalizedData.i18n.unit}}" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n.pixels}}" data-sek-unit="px">px</button><button type="button" aria-pressed="false" class="sek-ui-button" title="em" data-sek-unit="em">em</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n.percents}}" data-sek-unit="%">%</button></div>
          </div>
      </script>












      <?php
      /* ------------------------------------------------------------------------- *
       * CODE EDITOR
      /* ------------------------------------------------------------------------- */
      ?>
      <?php /////////////////// range_with_unit_picker_device_switcher ?>
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
        <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{input_id}}" data-czr-action="open-tinymce-editor">{{sektionsLocalizedData.i18n['Edit']}}</button>&nbsp;
        <button type="button" class="button text_editor-button" data-czr-control-id="{{ data.control_id }}" data-czr-input-id="{{input_id}}" data-czr-action="close-tinymce-editor">{{sektionsLocalizedData.i18n['Hide editor']}}</button>
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
            <div data-sek-align="left" title="{{sektionsLocalizedData.i18n['Align left']}}"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="{{sektionsLocalizedData.i18n['Align center']}}"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="{{sektionsLocalizedData.i18n['Align right']}}"><i class="material-icons">format_align_right</i></div>
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
            <div data-sek-align="left" title="{{sektionsLocalizedData.i18n['Align left']}}"><i class="material-icons">format_align_left</i></div>
            <div data-sek-align="center" title="{{sektionsLocalizedData.i18n['Align center']}}"><i class="material-icons">format_align_center</i></div>
            <div data-sek-align="right" title="{{sektionsLocalizedData.i18n['Align right']}}"><i class="material-icons">format_align_right</i></div>
            <div data-sek-align="justify" title="{{sektionsLocalizedData.i18n['Justified']}}"><i class="material-icons">format_align_justify</i></div>
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


      <script type="text/html" id="tmpl-nimble-input___font_picker">
        <select data-czrtype="{{data.input_id}}"></select>
      </script>


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
          <div aria-label="{{sektionsLocalizedData.i18n.unit}}" class="sek-ui-button-group sek-float-right" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n.pixels}}" data-sek-unit="px">px</button><button type="button" aria-pressed="false" class="sek-ui-button" title="em" data-sek-unit="em">em</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n.percents}}" data-sek-unit="%">%</button></div>
          </div>
        </div>
      </script>


      <script type="text/html" id="tmpl-nimble-input___wp_color_alpha">
        <#
          var item_model = data.item_model,
              input_id = data.input_id,
              value = _.has( item_model, input_id ) ? item_model[input_id] : null;
        #>
        <input data-czrtype="{{data.input_id}}" class="width-100"  data-alpha="true" type="text" value="{{value}}"></input>
      </script>


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
        </div><?php // sek-spacing-wrapper ?>
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
                <div class="sek-flex-center-stretch" data-sek-spacing="margin-top" title="{{sektionsLocalizedData.i18n['Margin top']}}">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>
              <div class="sek-pm-middle-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch sek-pm-margin-left" data-sek-spacing="margin-left" title="{{sektionsLocalizedData.i18n['Margin left']}}">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>

                <div class="sek-pm-padding-wrapper">
                  <div class="sek-flex-justify-center">
                    <div class="sek-flex-center-stretch" data-sek-spacing="padding-top" title="{{sektionsLocalizedData.i18n['Padding top']}}">
                      <div class="sek-pm-input-parent">
                        <input class="sek-pm-input" value="" type="number"  >
                      </div>
                    </div>
                  </div>
                    <div class="sek-flex-justify-center sek-flex-space-between">
                      <div class="sek-flex-center-stretch" data-sek-spacing="padding-left" title="{{sektionsLocalizedData.i18n['Padding left']}}">
                        <div class="sek-pm-input-parent">
                          <input class="sek-pm-input" value="" type="number"  >
                        </div>
                      </div>
                      <div class="sek-flex-center-stretch" data-sek-spacing="padding-right" title="{{sektionsLocalizedData.i18n['Padding right']}}">
                        <div class="sek-pm-input-parent">
                          <input class="sek-pm-input" value="" type="number"  >
                        </div>
                      </div>
                    </div>
                  <div class="sek-flex-justify-center">
                    <div class="sek-flex-center-stretch" data-sek-spacing="padding-bottom" title="{{sektionsLocalizedData.i18n['Padding bottom']}}">
                      <div class="sek-pm-input-parent">
                        <input class="sek-pm-input" value="" type="number"  >
                      </div>
                    </div>
                  </div>
                </div>

                <div class="sek-flex-center-stretch sek-pm-margin-right" data-sek-spacing="margin-right" title="{{sektionsLocalizedData.i18n['Margin right']}}">
                  <div class="sek-pm-input-parent">
                    <input class="sek-pm-input" value="" type="number"  >
                  </div>
                </div>
              </div>

              <div class="sek-pm-top-bottom-wrap sek-flex-justify-center">
                <div class="sek-flex-center-stretch" data-sek-spacing="margin-bottom" title="{{sektionsLocalizedData.i18n['Margin bottom']}}">
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
            <div aria-label="unit" class="sek-ui-button-group" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="{{sektionsLocalizedData.i18n['All']}}" data-sek-border-type="_all_">{{sektionsLocalizedData.i18n['All']}}</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Left']}}" data-sek-border-type="left">{{sektionsLocalizedData.i18n['Left']}}</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Top']}}" data-sek-border-type="top">{{sektionsLocalizedData.i18n['Top']}}</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Right']}}" data-sek-border-type="right">{{sektionsLocalizedData.i18n['Right']}}</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Bottom']}}" data-sek-border-type="bottom">{{sektionsLocalizedData.i18n['Bottom']}}</button></div>
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
              <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right">{{sektionsLocalizedData.i18n['Reset']}}</button></div>
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
            <div aria-label="unit" class="sek-ui-button-group sek-float-left" role="group"><button type="button" aria-pressed="true" class="sek-ui-button is-selected" title="{{sektionsLocalizedData.i18n['All']}}" data-sek-radius-type="_all_">{{sektionsLocalizedData.i18n['All']}}</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Top left']}}" data-sek-radius-type="top_left"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Top right']}}" data-sek-radius-type="top_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Bottom right']}}" data-sek-radius-type="bottom_right"><i class="material-icons">border_style</i></button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Bottom left']}}" data-sek-radius-type="bottom_left"><i class="material-icons">border_style</i></button></div>
            <div class="sek-reset-button"><button type="button" class="button sek-reset-button sek-float-right">{{sektionsLocalizedData.i18n['Reset']}}</button></div>
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
            <div aria-label="{{sektionsLocalizedData.i18n['Content type']}}" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Pick a section']}}" data-sek-content-type="section">{{sektionsLocalizedData.i18n['Pick a section']}}</button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n['Pick a module']}}" data-sek-content-type="module">{{sektionsLocalizedData.i18n['Pick a module']}}</button>
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
                var title_attr = sektionsLocalizedData.i18n['Drag and drop or double-click to insert in your chosen target element.'],
                    font_icon_class = !_.isEmpty( modData['font_icon'] ) ? 'is-font-icon' : '',
                    is_draggable = true !== modData['active'] ? 'false' : 'true';
                if ( true !== modData['active'] ) {
                    title_attr = sektionsLocalizedData.i18n['Available soon ! This module is currently in beta, you can activate it in Site Wide Options > Beta features'];
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
}



?>