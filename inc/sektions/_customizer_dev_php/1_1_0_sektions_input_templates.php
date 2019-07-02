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
          <div class="czr-input {{input_width}}"><# print( data.input_tmpl( data ) ); #></div>

          <# if ( input_data.notice_after ) { #>
            <span class="czr-notice"><# print(input_data.notice_after); #></span>
          <# } #>
          <# if ( input_data.html_after ) { #>
            <div class="czr-html-after"><# print(input_data.html_after); #></div>
          <# } #>
        </div><?php //css_attr.sub_set_wrapper ?>
      </script>












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
      <script type="text/html" id="tmpl-nimble-input___range_with_unit_picker_device_switcher">
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
        <div class="sek-range-with-unit-picker-wrapper">
            <?php //<# //console.log( 'IN php::sek_set_input_tmpl___range_with_unit_picker_device_switcher() => data range_slide => ', data ); #> ?>
            <div class="sek-range-wrapper">
              <input data-czrtype="{{input_id}}" type="hidden" data-sek-unit="{{unit}}"/>
              <input class="sek-range-input" type="range" {{_step}} {{_saved_unit}} {{_min}} {{_max}}/>
            </div>
            <div class="sek-number-wrapper">
                <input class="sek-pm-input" value="{{ value }}" type="number"  >
            </div>
            <div class="sek-unit-wrapper">
              <div aria-label="{{sektionsLocalizedData.i18n.unit}}" class="sek-ui-button-group" role="group">
                    <button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n.pixels}}" data-sek-unit="px">px</button><button type="button" aria-pressed="false" class="sek-ui-button" title="em" data-sek-unit="em">em</button><button type="button" aria-pressed="false" class="sek-ui-button" title="{{sektionsLocalizedData.i18n.percents}}" data-sek-unit="%">%</button></div>
            </div>
        </div><?php // sek-spacing-wrapper ?>
      </script>




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



      <script type="text/html" id="tmpl-nimble-input___h_alignment">
        <#
          console.log('DATA ???', data );
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
          console.log('DATA ???', data );
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
      <?php
}



?>