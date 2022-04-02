<?php
/* ------------------------------------------------------------------------- *
*  CUSTOMIZE PREVIEW : export skope data and send skope to the panel
/* ------------------------------------------------------------------------- */
if ( !class_exists( 'Flat_Export_Skope_Data_And_Send_To_Panel' ) ) :
    class Flat_Export_Skope_Data_And_Send_To_Panel extends Flat_Skop_Register_And_Load_Control_Assets {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_export_skope_data_and_schedule_sending_to_panel() {
              add_action( 'wp_head', array( $this, 'skp_print_server_skope_data') , 30 );
          }


          //hook : 'wp_footer'
          public function skp_print_server_skope_data() {
              if ( !skp_is_customize_preview_frame() )
                  return;

              global $wp_query, $wp_customize;
              $_meta_type = skp_get_skope( 'meta_type', true );

              // $_czr_scopes = array( );
              $_czr_skopes            = $this->_skp_get_json_export_ready_skopes();
              $_czr_query_data        = $this->_skp_get_json_export_ready_query_data();
            
              ob_start();
              ?>
                    var _doSend = function() {
                        // December 2020 : it may happen that the 'sync' event was already sent and that we missed it
                      // Typically when the site is slow.
                      // So we need to check if the "sync" event has fired already ( see customize-base.js, ::bind method )
                      // For more security, let's introduce a marker and attempt to re-sent after a moment if needed
                      window.czr_skopes_sent = false;
                      var _send = function() {
                            wp.customize.preview.send( 'czr-new-skopes-synced', {
                                czr_new_skopes : _wpCustomizeSettings.czr_new_skopes || [],
                                czr_stylesheet : _wpCustomizeSettings.czr_stylesheet || '',
                                isChangesetDirty : _wpCustomizeSettings.isChangesetDirty || false,
                                skopeGlobalDBOpt : _wpCustomizeSettings.skopeGlobalDBOpt || [],
                            } );
                            window.czr_skopes_sent = true;
                      };

                        jQuery( function() {
                            if ( wp.customize.preview.topics && wp.customize.preview.topics.sync && wp.customize.preview.topics.sync.fired() ) {
                                _send();
                            } else {
                                wp.customize.preview.bind( 'sync', function( events ) {
                                    _send();
                                });
                            }
                            setTimeout( function() {
                                    if ( !window.czr_skopes_sent ) {
                                        _send();
                                    }
                            }, 2500 );
                        });
                    };
                    
                    
                    // recursively try to load jquery every 200ms during 6s ( max 30 times )
                    var _doWhenCustomizeSettingsReady = function( attempts ) {
                        attempts = attempts || 0;
                        if ( typeof undefined !== typeof window._wpCustomizeSettings ) {
                            _wpCustomizeSettings.czr_new_skopes        = <?php echo wp_json_encode( $_czr_skopes ); ?>;
                            _wpCustomizeSettings.czr_stylesheet    = '<?php echo get_stylesheet(); ?>';
                            _wpCustomizeSettings.czr_query_params  = <?php echo wp_json_encode($_czr_query_data); ?>;
                            _doSend();
                        } else if ( attempts < 30 ) {
                            setTimeout( function() {
                                attempts++;
                                _doWhenCustomizeSettingsReady( attempts );
                            }, 20 );
                        } else {
                            if ( window.console && window.console.log ) {
                                console.log('Nimble Builder problem : _wpCustomizeSettings is not defined');
                            }
                        }
                    };

                    _doWhenCustomizeSettingsReady();
              <?php
              $script = ob_get_clean();
              wp_register_script( 'nb_print_skope_data_js', '');
              wp_enqueue_script( 'nb_print_skope_data_js' );
              wp_add_inline_script( 'nb_print_skope_data_js', $script );
          }


          // introduced in october 2019 for https://github.com/presscustomizr/nimble-builder/issues/401
          private function _skp_get_json_export_ready_query_data() {
              global $wp_query;
              global $authordata;
              add_filter('get_the_archive_title_prefix', '__return_false');
              $archive_title = get_the_archive_title();
              remove_filter('get_the_archive_title_prefix', '__return_false');
              return [
                'is_singular' => $wp_query->is_singular,
                'is_archive' => $wp_query->is_archive,
                'is_search' => $wp_query->is_search,
                'is_attachment' => $wp_query->is_attachment,
                'is_front_page' => is_front_page(),
                'the_archive_title' => $archive_title,
                'the_archive_description' => get_the_archive_description(),
                'the_previous_post_link' => is_singular() ? get_previous_post_link( $format = '%link' ) : '',
                'the_next_post_link' => is_singular() ? get_next_post_link( $format = '%link' ) : '',
                'the_search_query' => get_search_query(),
                'the_search_results_nb' => (int) $wp_query->found_posts,
                'the_author_id' => isset( $authordata->ID ) ? $authordata->ID : 0,
                'post_id' => get_the_ID(),
                'query_vars' => $wp_query->query_vars
              ];
          }

          /* ------------------------------------------------------------------------- *
              *  CUSTOMIZE PREVIEW : BUILD SKOPES JSON
          /* ------------------------------------------------------------------------- */
          //generates the array of available scopes for a given context
          //ex for a single post tagged #tag1 and #tag2 and categroized #cat1 :
          //global
          //all posts
          //local
          //posts tagged #tag1
          //posts tagged #tag2
          //posts categorized #cat1
          //@return array()
          //
          //skp_get_skope_title() takes the following default args
          //array(
          //  'level'       =>  '',
          //  'meta_type'   => null,
          //  'long'        => false,
          //  'is_prefixed' => true
          //)
          private function _skp_get_json_export_ready_skopes() {
              $skopes = array();
              $_meta_type = skp_get_skope( 'meta_type', true );

              //default properties of the scope object
              $defaults = skp_get_default_skope_model();
              //global and local and always sent
              $skopes[] = wp_parse_args(
                  array(
                      'title'       => skp_get_skope_title( array( 'level' => 'global' ) ),
                      'long_title'  => skp_get_skope_title( array( 'level' => 'global', 'meta_type' => null, 'long' => true ) ),
                      'ctx_title'   => skp_get_skope_title( array( 'level' => 'global', 'meta_type' => null, 'long' => true, 'is_prefixed' => false ) ),
                      'skope'       => 'global',
                      'level'       => '_all_'
                  ),
                  $defaults
              );


              //SPECIAL GROUPS
              //@todo


              //GROUP
              //Do we have a group ? => if yes, then there must be a meta type
              if ( skp_get_skope('meta_type') ) {
                  $skopes[] = wp_parse_args(
                      array(
                          'title'       => skp_get_skope_title( array( 'level' => 'group', 'meta_type' => $_meta_type  ) ),
                          'long_title'  => skp_get_skope_title( array( 'level' => 'group', 'meta_type' => $_meta_type, 'long' => true ) ),
                          'ctx_title'   => skp_get_skope_title( array( 'level' => 'group', 'meta_type' => $_meta_type, 'long' => true, 'is_prefixed' => false ) ),
                          'skope'       => 'group',
                          'level'       => 'all_' . skp_get_skope('type'),
                          'skope_id'    => skp_get_skope_id( 'group' )
                      ),
                      $defaults
                  );
              }


              //LOCAL
              $skopes[] = wp_parse_args(
                  array(
                      'title'       => skp_get_skope_title( array( 'level' => 'local', 'meta_type' => $_meta_type ) ),
                      'long_title'  => skp_get_skope_title( array( 'level' => 'local', 'meta_type' => $_meta_type, 'long' => true ) ),
                      'ctx_title'   => skp_get_skope_title( array( 'level' => 'local', 'meta_type' => $_meta_type, 'long' => true, 'is_prefixed' => false ) ),
                      'skope'       => 'local',
                      'level'       => skp_get_skope(),
                      'obj_id'      => skp_get_skope('id'),
                      'skope_id'    => skp_get_skope_id( 'local' )
                  ),
                  $defaults
              );
              return apply_filters( 'skp_json_export_ready_skopes', $skopes );
          }
    }//class
endif;

?>