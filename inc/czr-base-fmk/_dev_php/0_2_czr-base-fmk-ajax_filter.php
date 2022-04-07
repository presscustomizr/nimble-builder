<?php
////////////////////////////////////////////////////////////////
// CZR_Fmk_Base
if ( !class_exists( 'CZR_Fmk_Base_Ajax_Filter' ) ) :
    class CZR_Fmk_Base_Ajax_Filter extends CZR_Fmk_Base_Load_Resources {

        // fired in the constructor
        function czr_setup_ajax_tmpl() {
            // this dynamic filter is declared on wp_ajax_ac_get_template
            // It allows us to populate the server response with the relevant module html template
            // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
            add_filter( "ac_set_ajax_czr_tmpl___all_modules", array( $this, 'ac_get_all_modules_tmpl' ), 10, 3 );

            // fetch templates
            add_action( 'wp_ajax_ac_get_template', array( $this, 'ac_set_ajax_czr_tmpl' ) );
        }

        // hook : 'wp_ajax_ac_get_template'
        function ac_set_ajax_czr_tmpl() {
            if ( !is_user_logged_in() ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => unauthenticated' );
            }
            if ( !current_user_can( 'customize' ) ) {
                status_header( 403 );
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => customize_not_allowed' );
            } else if ( !isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
                status_header( 405 );
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => bad_method' );
            }
            $action = 'save-customize_' . get_stylesheet();
            if ( !check_ajax_referer( $action, 'nonce', false ) ) {
                 wp_send_json_error( array(
                  'code' => 'invalid_nonce',
                  'message' => __( 'ac_set_ajax_czr_tmpl => Security check failed.' ),
                ) );
            }

            if ( !isset( $_POST['module_type'] ) || empty( $_POST['module_type'] ) ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => missing module_type property in posted data' );
            }
            if ( !isset( $_POST['tmpl'] ) || empty( $_POST['tmpl'] ) ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => missing tmpl property in posted data' );
            }
            $tmpl = sanitize_text_field($_POST['tmpl']);
            $module_type = sanitize_text_field($_POST['module_type']);

            ///////////////////////////////////////////////////////////////////////
            // @param $tmpl = 'item-inputs'
            //
            // @param $_POST = {
            // [tmpl] => item-inputs
            // [module_type] => czr_heading_child
            // [module_id] => __nimble__51b2f35191b3__main_settings_czr_module
            // [cache] => true
            // [nonce] => b4b0aea848
            // [control_id] => __nimble__51b2f35191b3__main_settings
            // [item_model] => Array
            //     (
            //         [id] => czr_heading_child_0
            //         [title] =>
            //         [heading_text] => This is a heading.
            //         [heading_tag] => h1
            //         [h_alignment_css] => Array
            //             (
            //                 [desktop] => center
            //             )

            //         [heading_title] =>
            //         [link-to] =>
            //         [link-custom-url] =>
            //         [link-target] =>
            //     )

            // [action] => ac_get_template
            // }
            $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl, $_POST );
            ///////////////////////////////////////////////////////////////////////////

            if ( empty( $html ) ) {
                wp_send_json_error( 'ac_set_ajax_czr_tmpl => module ' . $module_type . ' => template empty for requested tmpl : ' . $tmpl );
            } else {
                wp_send_json_success( apply_filters( 'tmpl_results', $html, $tmpl ) );
            }
        }


        // hook : ac_set_ajax_czr_tmpl___all_modules
        // this dynamic filter is declared on wp_ajax_ac_get_template
        // It allows us to populate the server response with the relevant module html template
        // $html = apply_filters( "ac_set_ajax_czr_tmpl___{$module_type}", '', $tmpl );
        //
        // For all modules, there are 3 types of templates :
        // 1) the pre-item, rendered when adding an item
        // 2) the module meta options, or mod-opt
        // 3) the item input options
        function ac_get_all_modules_tmpl( $html, $requested_tmpl = '', $posted_params = array() ) {
            $css_attr = $this->czr_css_attr;
            if ( empty( $requested_tmpl ) ) {
                wp_send_json_error( 'ac_get_all_modules_tmpl => the requested tmpl is empty' );
            }
            ob_start();
            switch ( $requested_tmpl ) {
                case 'crud-module-part' :
                    ?>
                      <button class="<?php echo esc_attr($css_attr['open_pre_add_btn']); ?>"><?php _e('Add New', 'text_doma'); ?> <span class="fas fa-plus-square"></span></button>
                      <div class="<?php echo esc_attr($css_attr['pre_add_wrapper']); ?>">
                        <div class="<?php echo esc_attr($css_attr['pre_add_success']); ?>"><p></p></div>
                        <div class="<?php echo esc_attr($css_attr['pre_add_item_content']); ?>">

                          <span class="<?php echo esc_attr($css_attr['cancel_pre_add_btn']); ?> button"><?php _e('Cancel', 'text_doma'); ?></span> <span class="<?php echo esc_attr($css_attr['add_new_btn']); ?> button"><?php _e('Add it', 'text_doma'); ?></span>
                        </div>
                      </div>
                    <?php
                break;
                case 'rud-item-part' :
                    ?>
                      <div class="<?php echo esc_attr($css_attr['item_header']); ?> czr-custom-model">
                        <# if ( ( true === data.is_sortable ) ) { #>
                          <div class="<?php echo esc_attr($css_attr['item_title']); ?> <?php echo esc_attr($css_attr['item_sort_handle']); ?>"><h4>{{ data.title }}</h4></div>
                        <# } else { #>
                          <div class="<?php echo esc_attr($css_attr['item_title']); ?>"><h4>{{ data.title }}</h4></div>
                        <# } #>
                        <div class="<?php echo esc_attr($css_attr['item_btns']); ?>"><a title="<?php _e('Edit', 'text_doma'); ?>" href="javascript:void(0);" class="fas fa-pencil-alt <?php echo esc_attr($css_attr['edit_view_btn']); ?>"></a>&nbsp;<a title="<?php _e('Remove', 'text_doma'); ?>" href="javascript:void(0);" class="fas fa-trash <?php echo esc_attr($css_attr['display_alert_btn']); ?>"></a></div>
                        <div class="<?php echo esc_attr($css_attr['remove_alert_wrapper']); ?>"></div>
                      </div>
                    <?php
                break;

                case 'rud-item-alert-part' :
                    ?>
                      <p class="czr-item-removal-title"><?php _e('Are you sure you want to remove : <strong>{{ data.title }} ?</strong>', 'text_doma'); ?></p>
                      <span class="<?php echo esc_attr($css_attr['remove_view_btn']); ?> button"><?php _e('Yes', 'text_doma'); ?></span> <span class="<?php echo esc_attr($css_attr['cancel_alert_btn']); ?> button"><?php _e('No', 'text_doma'); ?></span>
                    <?php
                break;

                // this template is used in setupImageUploaderSaveAsId and setupImageUploaderSaveAsUrl
                case 'img-uploader' :
                    ?>
                      <?php // case when a regular attachement object is provided, fetched from an id with wp.media.attachment( id ) ?>
                      <# if ( ( data.attachment && data.attachment.id ) ) { #>
                        <div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">
                          <div class="thumbnail thumbnail-{{ data.attachment.type }}">
                            <# if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.medium ) { #>
                              <img class="attachment-thumb" src="{{ data.attachment.sizes.medium.url }}" draggable="false" alt="" />
                            <# } else if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.full ) { #>
                              <img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false" alt="" />
                            <# } #>
                          </div>
                          <div class="actions">
                            <# if ( data.canUpload ) { #>
                            <button type="button" class="button remove-button">{{ data.button_labels.remove }}</button>
                            <button type="button" class="button upload-button control-focus" id="{{ data.settings['default'] }}-button">{{ data.button_labels.change }}</button>
                            <div style="clear:both"></div>
                            <# } #>
                          </div>
                        </div>
                      <?php // case when an url is provided ?>
                      <# } else if ( !_.isEmpty( data.fromUrl ) ) { #>
                        <div class="attachment-media-view">
                          <div class="thumbnail thumbnail-thumb">
                              <img class="attachment-thumb" src="{{ data.fromUrl }}" draggable="false" alt="" />
                          </div>
                          <div class="actions">
                            <# if ( data.canUpload ) { #>
                            <button type="button" class="button remove-button">{{ data.button_labels.remove }}</button>
                            <button type="button" class="button upload-button control-focus" id="{{ data.settings['default'] }}-button">{{ data.button_labels.change }}</button>
                            <div style="clear:both"></div>
                            <# } #>
                          </div>
                        </div>
                      <?php // case when neither attachement or url are provided => placeholder ?>
                      <# } else { #>
                        <div class="attachment-media-view">
                          <div class="placeholder">
                            {{ data.button_labels.placeholder }}
                          </div>
                          <div class="actions">
                            <# if ( data.canUpload ) { #>
                            <button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button">{{ data.button_labels.select }}</button>
                            <# } #>
                            <div style="clear:both"></div>
                          </div>
                        </div>
                      <# } #>
                    <?php
                break;
            }//switch

            $html = ob_get_clean();
            if ( empty( $html ) ) {
                wp_send_json_error( 'ac_get_all_modules_tmpl => no template was found for tmpl => ' . $requested_tmpl );
            }

            return $html;//will be sent by wp_send_json_success() in ::ac_set_ajax_czr_tmpl()
        }
    }//class
endif;

?>