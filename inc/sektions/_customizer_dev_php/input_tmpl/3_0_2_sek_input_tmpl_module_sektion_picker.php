<?php

/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___content_type_switcher( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
            <div aria-label="<?php _e( 'Content type', 'text_domain'); ?>" class="sek-ui-button-group" role="group">
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a section', 'text_domain');?>" data-sek-content-type="section"><?php _e('Pick a section', 'text_domain');?></button>
                <button type="button" aria-pressed="false" class="sek-ui-button" title="<?php _e('Pick a module', 'text_domain');?>" data-sek-content-type="module"><?php _e('Pick a module', 'text_domain');?></button>
            </div>
        </div>
  <?php
}


/* ------------------------------------------------------------------------- *
 *  MODULE PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___module_picker( $input_id, $input_data ) {
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            $content_collection = sek_get_module_collection();
            $defaults =  array(
                'content-type' => 'module',
                'content-id' => '',
                'title' => '',
                'icon' => '',
                'font_icon' => '',
                'active' => true
            );
            $i = 0;
            foreach( $content_collection as $_module_params ) {
                // if ( $i % 2 == 0 ) {
                //   //printf('<div class="sek-module-raw"></div');
                // }
                $_module_params = wp_parse_args( $_module_params, $defaults );

                $icon_img_html = '<i style="color:red">Missing Icon</i>';
                if ( !empty( $_module_params['icon'] ) ) {
                    $icon_img_src = NIMBLE_MODULE_ICON_PATH . $_module_params['icon'];
                    $icon_img_html = '<img draggable="false" title="'. $_module_params['title'] . '" alt="'. $_module_params['title'] . '" class="nimble-module-icons" src="' . $icon_img_src .'"/>';
                } else if ( !empty( $_module_params['font_icon'] ) ) {
                    $icon_img_html = $_module_params['font_icon'];
                }

                printf('<div draggable="%7$s" data-sek-content-type="%1$s" data-sek-content-id="%2$s" title="%5$s"><div class="sek-module-icon %6$s">%3$s</div><div class="sek-module-title"><div class="sek-centered-module-title">%4$s</div></div></div>',
                      $_module_params['content-type'],
                      $_module_params['content-id'],
                      $icon_img_html,
                      $_module_params['title'],
                      true === $_module_params['active'] ? __('Drag and drop or double-click to insert in your chosen target element.', 'text_doma' ) : __('Available soon ! This module is currently in beta, you can activate it in Site Wide Options > Beta features', 'text_doma'),
                      !empty( $_module_params['font_icon'] ) ? 'is-font-icon' : '',
                      true === $_module_params['active'] ? 'true' : 'false'
                );
            }
          ?>
        </div>
    <?php
}









/* ------------------------------------------------------------------------- *
 *  SECTION PICKER INPUT
/* ------------------------------------------------------------------------- */
// @fired from  sek_set_input_tmpl_content( $input_type, $input_id, $input_data )
function sek_set_input_tmpl___section_picker( $input_id, $input_data ) {
    //sek_error_log('$input_data ? for input_id ' . $input_id, $input_data );
    //sek_error_log('CURRENT MODULE PARAMS ?', CZR_Fmk_Base()->current_module_params_when_ajaxing );
    ?>
        <input data-czrtype="<?php echo $input_id; ?>" type="hidden"/>
        <div class="sek-content-type-wrapper">
          <?php
            if ( !is_array( $input_data['section_collection'] ) || empty( $input_data['section_collection'] ) ) {
                $current_module = is_array( CZR_Fmk_Base()->current_module_params_when_ajaxing ) ? CZR_Fmk_Base()->current_module_params_when_ajaxing['module_type'] : 'undefined';
                sek_error_log( __FUNCTION__ . ' => missing section_collection param for module ' . $current_module );
                return;
            }
            $content_collection = $input_data['section_collection'];

            foreach( $content_collection as $_section_params ) {
                $section_type = 'content';
                // Section type has to be specified for header and footer sections
                // Otherwise bug : https://github.com/presscustomizr/nimble-builder/issues/454
                if ( !empty($_section_params['section_type']) ) {
                    $section_type = $_section_params['section_type'];
                }

                printf('<div draggable="true" data-sek-content-type="%1$s" data-sek-content-id="%2$s" style="%3$s" title="%4$s" data-sek-section-type="%5$s"><div class="sek-overlay"></div></div>',
                    'preset_section',
                    $_section_params['content-id'],
                    sprintf( 'background: url(%1$s) 50% 50% / cover no-repeat;%2$s',
                        // v1.4.2 : added the ?ver param to make sure we always display the latest shot of the section
                        // May 21st, v1.7.5 => back to the local data
                        // after problem was reported when fetching data remotely : https://github.com/presscustomizr/nimble-builder/issues/445
                        NIMBLE_BASE_URL . '/assets/img/section_assets/thumbs/' . $_section_params['thumb'] . '?ver=' . NIMBLE_VERSION,

                        //$_section_params['thumb'] . '?ver=' . NIMBLE_VERSION,
                        isset( $_section_params['height'] ) ? 'height:'.$_section_params['height'] : ''
                    ),
                    $_section_params['title'],
                    $section_type
                );
            }
          ?>
        </div><?php //class="sek-content-type-wrapper" ?>
  <?php
}

?>