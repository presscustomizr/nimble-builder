<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* This approach has been inspired by the excellent https://github.com/xwp/wp-customize-posts */
add_action( 'customize_controls_print_footer_scripts', 'sek_print_tiny_mce_editor_template', 0 );
function sek_print_tiny_mce_editor_template() {
?>
  <div id="czr-customize-content_editor-pane">
    <div data-czr-action="close-tinymce-editor" class="czr-close-editor"><i class="fas fa-arrow-circle-down" title="<?php _e( 'Hide Editor', 'nimble-builder' ); ?>"></i>&nbsp;<span><?php _e( 'Hide Editor', 'nimble-builder');?></span></div>
    <div id="czr-customize-content_editor-dragbar">
      <span class="screen-reader-text"><?php _e( 'Resize Editor', 'nimble-builder' ); ?></span>
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
add_action( 'customize_controls_init', 'sek_enqueue_tiny_mce_editor' );
function sek_enqueue_tiny_mce_editor() {
  //add_action( 'customize_controls_print_footer_scripts', 'render_editor' , 0 );
  // @todo These should be included in \_WP_Editors::editor_settings()
  if ( false === has_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) ) ) {
    add_action( 'customize_controls_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ) );
  }
}