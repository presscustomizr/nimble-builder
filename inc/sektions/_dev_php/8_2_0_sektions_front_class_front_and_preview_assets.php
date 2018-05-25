<?php
if ( ! class_exists( 'SEK_Front_Assets' ) ) :
    class SEK_Front_Assets extends SEK_Front_Ajax {
        // Fired in __construct()
        function _schedule_front_and_preview_assets_printing() {
            // Load Front Assets
            add_action( 'wp_enqueue_scripts', array( $this, 'sek_enqueue_front_assets' ) );
            // Load customize preview js
            add_action ( 'customize_preview_init' , array( $this, 'sek_schedule_customize_preview_assets' ) );
        }

        // hook : 'wp_enqueue_scripts'
        function sek_enqueue_front_assets() {
            //wp_enqueue_style( 'google-material-icons', '//fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all' );
         /*   wp_register_style(
                'sek-bootstrap',
                NIMBLE_BASE_URL . '/inc/sektions/assets/front/css/custom-bootstrap.css',
                array(),
                time(),
                'all'
            );*/
            //base custom CSS bootstrap inspired
            wp_enqueue_style(
                'sek-base',
                NIMBLE_BASE_URL . '/assets/front/css/sek-base.css',
                array(),
                time(),
                'all'
            );
            wp_enqueue_style(
                'sek-main',
                NIMBLE_BASE_URL . '/assets/front/css/sek-main.css',
                array( 'sek-base' ),
                time(),
                'all'
            );
            wp_enqueue_style(
                'font-awesome',
                NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                array(),
                time(),
                $media = 'all'
            );

            wp_register_script(
                'sek-front-fmk-js',
                NIMBLE_BASE_URL . '/assets/front/js/_front_js_fmk.js',
                array( 'jquery', 'underscore'),
                time(),
                true
            );
            wp_enqueue_script(
                'sek-main-js',
                NIMBLE_BASE_URL . '/assets/front/js/sek-main.js',
                array( 'jquery', 'sek-front-fmk-js'),
                time(),
                true
            );
            wp_localize_script(
                'sek-main-js',
                'sekFrontLocalized',
                array(
                    'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
                )
            );
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                NIMBLE_BASE_URL . '/assets/czr/sek/css/sek-preview.css',
                array( 'sek-main' ),
                time(),
                'all'
            );

            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                NIMBLE_BASE_URL . '/assets/czr/sek/js/sek-preview.js',
                array( 'customize-preview', 'underscore'),
                time(),
                true
            );
            wp_localize_script(
                'sek-customize-preview',
                'sektionsLocalizedData',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
                    )
                )
            );
            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_style(
                'ui-sortable',
                '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css',
                array(),
                null,//time(),
                $media = 'all'
            );
            wp_enqueue_script( 'jquery-ui-resizable' );
        }

        //'wp_footer' in the preview frame
        function sek_print_ui_tmpl() {
            ?>
              <script type="text/html" id="sek-tmpl-add-content-button">
                  <# //console.log( 'data', data ); #>
                  <div class="sek-add-content-button <# if ( data.is_last ) { #>is_last<# } #>">
                    <div class="sek-add-content-button-wrapper">
                      <button title="<?php _e('Insert content here', 'text_domain_to_be_replaced' ); ?> <# if ( data.location ) { #>( hook : {{data.location}} )<# } #>" data-sek-action="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:83px;">
                        <span class="sek-action-button-icon sek-action">+</span><span class="action-button-text"><?php _e('Insert content here', 'text_domain_to_be_replaced' ); ?></span>
                      </button>
                    </div>
                  </div>
              </script>

              <script type="text/html" id="sek-tmpl-overlay-ui-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <# //console.log( 'data', data ); #>
                  <div class="sek-block-overlay sek-section-overlay">
                    <div class="sek-block-overlay-header">
                      <div class="sek-block-overlay-actions">
                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it movable for the moment. @todo ?>
                        <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <# if ( ! data.is_last_possible_section ) { #>
                          <i class="fas fa-arrows-alt sek-move-section" title="<?php _e( 'Move section', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Section options', 'sek-builder' ); ?>"></i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-action="add-column" class="fas fa-plus-circle sek-action" title="<?php _e( 'Add Column', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate section', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove section', 'sek-builder' ); ?>"></i>
                      </div>

                      <div class="sek-clear"></div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div>
              </script>

              <script type="text/html" id="sek-tmpl-overlay-ui-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-block-overlay sek-column-overlay">
                    <div class="sek-block-overlay-header">
                      <div class="sek-block-overlay-actions">
                        <i class="fas fa-arrows-alt sek-move-column" title="<?php _e( 'Move column', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Columns options', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="pick-module" class="fas fa-plus-circle sek-action" title="<?php _e( 'Add Module', 'sek-builder' ); ?>"></i>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate column', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <# if ( ! data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-action="add-section" class="fas far fa-plus-square sek-action" title="<?php _e( 'Add Sektion', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <# if ( ! data.parent_is_single_column ) { #>
                          <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove column', 'sek-builder' ); ?>"></i>
                        <# } #>
                      </div>
                      <div class="sek-clear"></div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div>
              </script>

              <script type="text/html" id="sek-tmpl-overlay-ui-module">
                  <div class="sek-block-overlay sek-module-overlay">
                    <div class="editor-block-settings-menu"><?php // add class  is-visible on hover ?>
                      <div>
                        <div>
                          <button type="button" aria-expanded="false" aria-label="More Options" class="components-button components-icon-button editor-block-settings-menu__toggle">
                            <svg aria-hidden="true" role="img" focusable="false" class="dashicon dashicons-ellipsis" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                              <path d="M5 10c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm12-2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-7 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z">
                              </path>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div><?php // .editor-block-settings-menu ?>
                    <div class="sek-block-overlay-header">
                      <div class="sek-block-overlay-actions">
                        <i class="fas fa-arrows-alt sek-move-module" title="<?php _e( 'Move module', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="edit-module" class="fas fa-pencil-alt sek-tip sek-action" title="<?php _e( 'Edit Module', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="edit-options" class="fas fa-cogs sek-action" title="<?php _e( 'Module options', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="duplicate" class="far fa-clone sek-action" title="<?php _e( 'Duplicate module', 'sek-builder' ); ?>"></i>
                        <i data-sek-action="remove" class="far fa-trash-alt sek-action" title="<?php _e( 'Remove module', 'sek-builder' ); ?>"></i>
                      </div>
                      <div class="sek-clear"></div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div><?php // .sek-block-overlay-header ?>
              </script>
            <?php
        }
    }//class
endif;
?>