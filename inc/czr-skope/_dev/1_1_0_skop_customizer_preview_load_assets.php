<?php
/* ------------------------------------------------------------------------- *
*  CUSTOMIZE PREVIEW : export skope data and send skope to the panel
/* ------------------------------------------------------------------------- */
if ( ! class_exists( 'Flat_Export_Skope_Data_And_Send_To_Panel' ) ) :
    class Flat_Export_Skope_Data_And_Send_To_Panel extends Flat_Skop_Register_And_Load_Control_Assets {
          // Fired in Flat_Skop_Base::__construct()
          public function skp_export_skope_data_and_schedule_sending_to_panel() {
              add_action( 'wp_footer', array( $this, 'skp_print_server_skope_data') , 30 );
          }


          //hook : 'wp_footer'
          public function skp_print_server_skope_data() {
              if ( ! skp_is_customize_preview_frame() )
                  return;

              global $wp_query, $wp_customize;
              $_meta_type = skp_get_skope( 'meta_type', true );

              // $_czr_scopes = array( );
              $_czr_skopes            = $this->_skp_get_json_export_ready_skopes();
              ?>
                  <script type="text/javascript" id="czr-print-skop">
                      (function ( _export ){
                              _export.czr_new_skopes        = <?php echo wp_json_encode( $_czr_skopes ); ?>;
                              _export.czr_stylesheet    = '<?php echo get_stylesheet(); ?>';
                      })( _wpCustomizeSettings );

                      ( function( api, $, _ ) {
                          $( function() {
                                api.preview.bind( 'sync', function( events ) {
                                      api.preview.send( 'czr-new-skopes-synced', {
                                            czr_new_skopes : _wpCustomizeSettings.czr_new_skopes || [],
                                            czr_stylesheet : _wpCustomizeSettings.czr_stylesheet || '',
                                            isChangesetDirty : _wpCustomizeSettings.isChangesetDirty || false,
                                            skopeGlobalDBOpt : _wpCustomizeSettings.skopeGlobalDBOpt || [],
                                      } );
                                });
                          });
                      } )( wp.customize, jQuery, _ );
                  </script>
              <?php
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