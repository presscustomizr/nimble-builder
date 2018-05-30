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
            //base custom CSS bootstrap inspired
            wp_enqueue_style(
                'sek-base',
                sprintf(
                    '%1$s/assets/front/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    defined('CZR_DEV') && true === CZR_DEV ? 'sek-base.css' : 'sek-base.min.css'
                ),
                array(),
                NIMBLE_ASSETS_VERSION,
                'all'
            );


            // wp_register_script(
            //     'sek-front-fmk-js',
            //     NIMBLE_BASE_URL . '/assets/front/js/_front_js_fmk.js',
            //     array( 'jquery', 'underscore'),
            //     time(),
            //     true
            // );
            wp_enqueue_script(
                'sek-main-js',
                NIMBLE_BASE_URL . '/assets/front/js/sek-main.js',
                array( 'jquery'),
                NIMBLE_ASSETS_VERSION,
                true
            );
            // wp_localize_script(
            //     'sek-main-js',
            //     'sekFrontLocalized',
            //     array(
            //         'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
            //         'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            //         'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) ),
            //     )
            // );
        }

        // enqueue / print customize preview assets
        // hook : 'customize_preview_init'
        function sek_schedule_customize_preview_assets() {
            // Load preview ui js tmpl
            add_action( 'wp_footer', array( $this, 'sek_print_ui_tmpl' ) );

            wp_enqueue_style(
                'sek-preview',
                sprintf(
                    '%1$s/assets/czr/sek/css/%2$s' ,
                    NIMBLE_BASE_URL,
                    defined('CZR_DEV') && true === CZR_DEV ? 'sek-preview.css' : 'sek-preview.min.css'
                ),
                array( 'sek-base' ),
                NIMBLE_ASSETS_VERSION,
                'all'
            );
            wp_enqueue_style(
                'font-awesome',
                NIMBLE_BASE_URL . '/assets/front/fonts/css/fontawesome-all.min.css',
                array(),
                NIMBLE_ASSETS_VERSION,
                $media = 'all'
            );
            // Communication between preview and customizer panel
            wp_enqueue_script(
                'sek-customize-preview',
                sprintf(
                    '%1$s/assets/czr/sek/js/%2$s' ,
                    NIMBLE_BASE_URL,
                    defined('CZR_DEV') && true === CZR_DEV ? 'ccat-sek-preview.js' : 'ccat-sek-preview.min.js'
                ),
                array( 'customize-preview', 'underscore'),
                NIMBLE_ASSETS_VERSION,
                true
            );

            wp_localize_script(
                'sek-customize-preview',
                'sekPreviewLocalized',
                array(
                    'i18n' => array(
                        "You've reached the maximum number of columns allowed in this section." => __( "You've reached the maximum number of columns allowed in this section.", 'text_domain_to_be_replaced'),
                        'Something went wrong, please refresh this page.' => __('Something went wrong, please refresh this page.', 'text_domain_to_be_replaced')
                    ),
                    'isDevMode' => ( defined('WP_DEBUG') && true === WP_DEBUG ) || ( defined('CZR_DEV') && true === CZR_DEV ),
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'frontNonce' => array( 'id' => 'SEKFrontNonce', 'handle' => wp_create_nonce( 'sek-front-nonce' ) )
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
                      <button title="<?php _e('Insert a new section', 'text_domain_to_be_replaced' ); ?> <# if ( data.location ) { #>( hook : {{data.location}} )<# } #>" data-sek-click-on="add-content" data-sek-add="section" class="sek-add-content-btn" style="--sek-add-content-btn-width:83px;">
                        <span class="sek-click-on-button-icon sek-click-on">+</span><span class="action-button-text"><?php _e('Insert a new section', 'text_domain_to_be_replaced' ); ?></span>
                      </button>
                    </div>
                  </div>
              </script>

              <?php
                  $icon_right_side_class = is_rtl() ? 'sek-dyn-left-icons' : 'sek-dyn-right-icons';
                  $icon_left_side_class = is_rtl() ? 'sek-dyn-right-icons' : 'sek-dyn-left-icons';
              ?>

              <script type="text/html" id="sek-dyn-ui-tmpl-section">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <# //console.log( 'data', data ); #>
                  <div class="sek-dyn-ui-wrapper sek-section-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <?php // if this is a nested section, it has the is_nested property set to true. We don't want to make it movable for the moment. @todo ?>
                        <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                          <i class="sek-to-json fas fa-code"></i>
                        <?php endif; ?>
                        <# if ( ! data.is_last_possible_section ) { #>
                          <i class="fas fa-ellipsis-v sek-move-section" title="<?php _e( 'Move section', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Section options', 'sek-builder' ); ?>">settings</i>
                        <# if ( data.can_have_more_columns ) { #>
                          <i data-sek-click-on="add-column" class="material-icons sek-click-on" title="<?php _e( 'Add Column', 'sek-builder' ); ?>">add</i>
                        <# } #>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate section', 'sek-builder' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove section', 'sek-builder' ); ?>">delete_forever</i>
                      </div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-column">
                  <?php //<# console.log( 'data', data ); #> ?>
                  <div class="sek-dyn-ui-wrapper sek-column-dyn-ui">
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-ellipsis-v sek-move-column" title="<?php _e( 'Move column', 'sek-builder' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Columns options', 'sek-builder' ); ?>">settings</i>
                        <i data-sek-click-on="pick-module" class="material-icons sek-click-on" title="<?php _e( 'Add Module', 'sek-builder' ); ?>">add</i>
                        <# if ( data.parent_can_have_more_columns ) { #>
                          <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate column', 'sek-builder' ); ?>">filter_none</i>
                        <# } #>
                        <# if ( ! data.parent_is_last_allowed_nested ) { #>
                          <i data-sek-click-on="add-section" class="fas far fa-plus-square sek-click-on" title="<?php _e( 'Add a nested section', 'sek-builder' ); ?>"></i>
                        <# } #>
                        <# if ( ! data.parent_is_single_column ) { #>
                          <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove column', 'sek-builder' ); ?>">delete_forever</i>
                        <# } #>
                      </div>
                    </div>

                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div>
              </script>

              <script type="text/html" id="sek-dyn-ui-tmpl-module">
                  <div class="sek-dyn-ui-wrapper sek-module-dyn-ui">
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
                    <div class="sek-dyn-ui-inner <?php echo $icon_left_side_class; ?>">
                      <div class="sek-dyn-ui-icons">
                        <i class="fas fa-ellipsis-v sek-move-module" title="<?php _e( 'Move module', 'sek-builder' ); ?>"></i>
                        <i data-sek-click-on="edit-module" class="fas fa-pencil-alt sek-tip sek-click-on" title="<?php _e( 'Edit Module', 'sek-builder' ); ?>"></i>
                        <i data-sek-click-on="edit-options" class="material-icons sek-click-on" title="<?php _e( 'Module options', 'sek-builder' ); ?>">settings</i>
                        <i data-sek-click-on="duplicate" class="material-icons sek-click-on" title="<?php _e( 'Duplicate module', 'sek-builder' ); ?>">filter_none</i>
                        <i data-sek-click-on="remove" class="material-icons sek-click-on" title="<?php _e( 'Remove module', 'sek-builder' ); ?>">delete_forever</i>
                      </div>
                    </div>
                    <?php if ( defined( 'CZR_DEV' ) && CZR_DEV ) : ?>
                      <!-- <div class="dev-level-data">{{ data.level}} : {{ data.id }}</div> -->
                    <?php endif; ?>
                  </div><?php // .sek-dyn-ui-inner ?>
              </script>
            <?php
        }
    }//class
endif;
?>