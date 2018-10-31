<?php
/*
* This approach has been inspired by the excellent https://github.com/xwp/wp-customize-posts
*/

add_action( 'customize_register', '\Nimble\sek_register_tiny_mce_editor_tmpl_and_scripts');
function sek_register_tiny_mce_editor_tmpl_and_scripts() {
    add_action( 'customize_controls_print_footer_scripts', '\Nimble\sek_print_tiny_mce_editor_template', 0 );
    add_action( 'customize_controls_init', '\Nimble\sek_enqueue_tiny_mce_editor' );
}

// @hook customize_controls_print_footer_scripts
function sek_print_tiny_mce_editor_template() {
    global $wp_customize;

    ?>
      <div id="czr-customize-content_editor-pane">
        <div data-czr-action="close-tinymce-editor" class="czr-close-editor"><i class="fas fa-arrow-circle-down" title="<?php _e( 'Hide Editor', 'text_domain_to_be_replaced' ); ?>"></i>&nbsp;<span><?php _e( 'Hide Editor', 'text_domain_to_be_replaced');?></span></div>
        <div id="czr-customize-content_editor-dragbar" title="<?php _e('Resize the editor', 'text_domain'); ?>">
          <span class="screen-reader-text"><?php _e( 'Resize the editor', 'nimble-builder' ); ?></span>
          <i class="czr-resize-handle fas fa-arrows-alt-v"></i>
        </div>

        <?php
          // The settings passed in here are inspired from edit-form-advanced.php.
          wp_editor( '', 'czr-customize-content_editor', array(
              '_content_editor_dfw' => false,
              'drag_drop_upload' => true,
              'tabfocus_elements' => 'content-html,save-post',
              'editor_height' => 200,
              'default_editor' => 'tinymce',
              'tinymce' => array(
                  'resize' => false,
                  'wp_autoresize_on' => false,
                  'add_unload_trigger' => false,
              ),
          ) );
        ?>
      </div>
    <?php
}

/**
 * Enqueue a WP Editor instance we can use for rich text editing.
 */
// @hook customize_controls_init
function sek_enqueue_tiny_mce_editor() {
    //add_action( 'customize_controls_print_footer_scripts', 'render_editor' , 0 );
    // @todo These should be included in \_WP_Editors::editor_settings()
    if ( false === has_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) ) ) {
        add_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) );
    }
}

?>