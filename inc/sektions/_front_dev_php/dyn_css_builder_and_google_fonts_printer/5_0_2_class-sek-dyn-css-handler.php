<?php
/**
 *  Sek Dyn CSS Handler: class responsible for enqueuing/writing CSS file or enqueuing/printing inline CSS
 */
class Sek_Dyn_CSS_Handler {

    /**
     * CSS files base dir constant
     * Relative dir in the WordPress uploads dir
     *
     * @access public
     */
    const CSS_BASE_DIR = NIMBLE_CSS_FOLDER_NAME;

    /**
     * Functioning mode constant
     * @access public
     */
    const MODE_INLINE  = 'inline';

    /**
     * Functioning mode constant
     * @access public
     */
    const MODE_FILE    = 'file';

    /**
     * CSS resource ID
     *
     * Holds the CSS resource ID
     * Will be used to generate both the file name and the CSS handle when enqueued_or_printed
     * Usually set to skope_id
     *
     * @access private
     * @var string
     */
    private $id;



    /**
     * Requested skope_id
     *
     * Will be used as id
     * Must be provided
     *
     * @access private
     * @var string
     */
    private $skope_id;

    // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
    private $is_global_stylesheet;

    /**
     * the CSS
     *
     * Holds the CSS string: whether to inline print or to write in the proper file
     *
     * @access private
     * @var string
     */
    private $css_string_to_enqueue_or_print = '';


    /**
     * CSS enqueuing / inline printing status
     *
     * Hold the enqueuing status
     *
     * @access private
     * @var bool
     */
    private $enqueued_or_printed = false;



    /**
     * Enqueuing hook
     *
     * Holds the wp action hook name at whose occurrence the CSS will be enqueued_or_printed
     *
     * @access private
     * @var string
     */
    private $hook;


    /**
     * Enqueuing hook priority
     *
     * Holds the wp action hook priority at whose occurrence the CSS will be enqueued
     * (see the $hook param)
     *
     * @var int
     */
    private $priority = 10;



    /**
     * Enqueuing dependencies
     *
     * Holds the style dependencies for this CSS
     *
     * @access private
     * @var array
     */
    private $dep = array();



    /**
     * Functioning mode
     *
     * Holds the object functioning mode: MODE_FILE or MODE_INLINE
     *
     * @access private
     * @var string
     */
    private $mode;

    /**
     * File writing flag
     *
     * Indicates if we need to only write, not print or enqueuing
     * This is used when saving the customizer options + writing the css file.
     *
     * @access private
     * @var bool
     */
    private $customizer_save = false;


    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing if the file doesn't exist
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_write = false;



    /**
     * File writing flag
     *
     * Holds whether or not the file writing should be forced before enqueuing even if the file exists
     * This is valid only when $mode == MODE_FILE
     *
     * @access private
     * @var bool
     */
    private $force_rewrite = false;


    /**
     * File status
     *
     * Holds the file existence status (true|false)
     *
     * @access private
     * @var bool
     */
    private $file_exists = false;



    /**
     * CSS file base PATH
     *
     * Holds the CSS relative base path
     * This is simply CSS_BASE_DIR in single sites, while its structure takes in account network and site id in multisites
     *
     * @access private
     * @var string
     */
    private $relative_base_path;



    /**
     * CSS file base URI
     *
     * Holds the CSS folder URI
     *
     * @access private
     * @var string
     */
    private $base_uri;


    /**
     * CSS file base URL
     *
     * Holds the CSS folder URL
     *
     * @access private
     * @var string
     */
    private $base_url;



    /**
     * CSS file URL
     *
     * Holds the CSS file URL
     *
     * @access private
     * @var string
     */
    private $url;




    /**
     * CSS file URI
     *
     * Holds the CSS file URI
     *
     * @access private
     * @var string
     */
    private $uri;

    private $builder;//will hold the Sek_Dyn_CSS_Builder instance

    public $sek_model = 'no_set';


    /**
     * Sek Dyn CSS Handler constructor.
     *
     * Initializing the object.
     *
     * @access public
     * @param array $args Optional.
     *
     */
    public function __construct( $args = array() ) {

        $defaults = array(
            'id'                              => 'sek-'.rand(),
            'skope_id'                        => '',
            // property "is_global_stylesheet" has been added when fixing https://github.com/presscustomizr/nimble-builder/issues/273
            'is_global_stylesheet'            => false,
            'mode'                            => self::MODE_FILE,
            'css_string_to_enqueue_or_print'  => $this->css_string_to_enqueue_or_print,
            'dep'                             => $this->dep,
            'hook'                            => '',
            'priority'                        => $this->priority,
            'customizer_save'                 => false,//<= used when saving the customizer settins => we want to write the css file on Nimble_Collection_Setting::update()
            'force_write'                     => $this->force_write,
            'force_rewrite'                   => $this->force_rewrite
        );

        $args = wp_parse_args( $args, $defaults );

        //normalize some parameters
        $args[ 'dep' ]          = is_array( $args[ 'dep' ] ) ? $args[ 'dep' ]  : array();
        $args[ 'priority']      = is_numeric( $args[ 'priority' ] ) ? $args[ 'priority' ] : $this->priority;

        //turn $args into object properties
        foreach ( $args as $key => $value ) {
            if ( property_exists( $this, $key ) && array_key_exists( $key, $defaults) ) {
                    $this->$key = $value;
            }
        }

        if ( empty( $this->skope_id ) ) {
            sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' => __construct => skope_id not provided' );
            return;
        }

        //build no parameterized properties
        $this->_sek_dyn_css_set_properties();

        // Introduced March 2021 for #478
        if ( 'delete' !==  $this->mode ) {
            // Possible scenarii :
            // 1) customizing :
            //    the css is always printed inline. If there's already an existing css file for this skope_id, it's not enqueued.
            // 2) saving in the customizer :
            //    the css file is written in a "force_rewrite" mode, meaning that any existing css file gets re-written.
            //    There's no enqueing scheduled, 'customizer_save' mode.
            // 3) front, user logged in + 'customize' capabilities :
            //    the css file is re-written on each page load + enqueued. If writing a css file is not possible, we fallback on inline printing.
            // 4) front, user not logged in :
            //    the default behavior is that the css file is enqueued.
            //    It should have been written when saving in the customizer. If no file available, we try to write it. If writing a css file is not possible, we fallback on inline printing.
            if ( is_customize_preview() || !$this->_sek_dyn_css_file_exists_is_readable_and_has_content() || $this->force_rewrite || $this->customizer_save ) {
                $this->sek_model = sek_get_skoped_seks( $this->skope_id );

                //  on front, when no stylesheet is available, the fallback hook must be set to wp_head, because the hook property might be empty
                // fixes https://github.com/presscustomizr/nimble-builder/issues/328
                if ( !is_customize_preview() && !$this->_sek_dyn_css_file_exists_is_readable_and_has_content() ) {
                    $this->hook = 'wp_head';
                }

                //build stylesheet
                $this->builder = new Sek_Dyn_CSS_Builder( $this->sek_model, $this->is_global_stylesheet );

                // now that the stylesheet is ready let's cache it
                // Note that esc_html() cannot be used because `div &gt; span` is not interpreted properly.
                $this->css_string_to_enqueue_or_print = (string)strip_tags($this->builder->get_stylesheet());
            }

            // Do we have any rules to print / enqueue ?
            // If yes, print in the dom or enqueue depending on the current context ( customization or front )
            // If not, delete any previouly created stylesheet

            //hook setup for printing or enqueuing
            //bail if "customizer_save" == true, typically when saving the customizer settings @see Nimble_Collection_Setting::update()
            if ( !$this->customizer_save ) {
                // when not customizing, we write and enqueue :
                // - if the file already exists,
                // - or if we just have generated the CSS because the file had been deleted
                if ( !empty($this->css_string_to_enqueue_or_print) || $this->_sek_dyn_css_file_exists_is_readable_and_has_content() ) {
                    $this->_schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook();
                } else {
                    $this->sek_dyn_css_delete_file_if_empty();
                }
            } else {
                //sek_error_log( __CLASS__ . '::' . __FUNCTION__ .' ?? => $this->css_string_to_enqueue_or_print => ', $this->css_string_to_enqueue_or_print );
                if ( !empty($this->css_string_to_enqueue_or_print) ) {
                    $this->sek_dyn_css_maybe_write_css_file();
                } else {
                    // When customizing, the stylesheet is always generated.
                    // So if it is empty, it means we have to delete it
                    $this->sek_dyn_css_delete_file();
                }
            }

            // Maybe update global inline style now with a filter
            // This CSS is the one generated by global options like global text, global width, global breakpoint
            // It is printed @wp_head inline
            // for better performances on front, NB only wants to re-generate this style when customizing, and we user is logged in ( force_rewrite )
            // see https://github.com/presscustomizr/nimble-builder/issues/750
            if ( $this->is_global_stylesheet ) {
                if ( is_customize_preview() || $this->force_rewrite || $this->customizer_save ) {
                    $global_style = Nimble_Manager()->sek_build_global_options_inline_css();
                    //sek_error_log('SOO GLOBAL INLINE CSS?', $global_style );
                    update_option( NIMBLE_OPT_FOR_GLOBAL_CSS, $global_style, 'no' );
                }
            }
        }//if 'delete' !==  $this->mode
    }//__construct





    /*
    * Private methods
    */

    /**
     *
     * Build these instance properties based on the params passed on instantiation
     * called in the constructor
     *
     * @access private
     *
     */
    private function _sek_dyn_css_set_properties() {
        $this->_sek_dyn_css_require_wp_filesystem();

        $this->relative_base_path   = $this->_sek_dyn_css_build_relative_base_path();

        $this->base_uri             = $this->_sek_dyn_css_build_base_uri();
        $this->base_url             = $this->_sek_dyn_css_build_base_url();

        $this->uri                  = $this->_sek_dyn_css_build_uri();
        $this->url                  = $this->_ssl_maybe_fix_url( $this->_sek_dyn_css_build_url() );

        $this->file_exists          = $this->_sek_dyn_css_file_exists_is_readable_and_has_content();

        if ( self::MODE_FILE == $this->mode ) {
            if ( !$this->_sek_dyn_css_write_file_is_possible() ) {
                $this->mode = self::MODE_INLINE;
            }
        }

        // July 2020 remove previous folder
        // see https://github.com/presscustomizr/nimble-builder/issues/727
        // if ( 'done' != get_transient( 'nimble_update_css_folder_name_0720' ) ) {
        //     set_transient( 'nimble_update_css_folder_name_0720', 'done', 30 * YEAR_IN_SECONDS );
        // }
        $upload_dir = wp_get_upload_dir();
        $prev_folder_path = $this->_sek_dyn_css_build_relative_base_path( NIMBLE_DEPREC_ONE_CSS_FOLDER_NAME );
        $previous_folder_one = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $prev_folder_path );
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $previous_folder_one ) ) {
            $wp_filesystem->rmdir( $previous_folder_one, true );
        }

        // October 2020 remove previous folder when implementing dynamic module stylesheet concatenation
        $prev_folder_path = $this->_sek_dyn_css_build_relative_base_path( NIMBLE_DEPREC_TWO_CSS_FOLDER_NAME );
        $previous_folder_two = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $prev_folder_path );
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $previous_folder_two ) ) {
            $wp_filesystem->rmdir( $previous_folder_two, true );
        }
    }


    /**
    * replace http: URL with https: URL
    * @fix https://github.com/presscustomizr/nimble-builder/issues/188
    * @param string $url
    * @return string
    */
    private function _ssl_maybe_fix_url($url) {
      // only fix if source URL starts with http://
      if ( is_ssl() && is_string($url) && stripos($url, 'http://') === 0 ) {
        $url = 'https' . substr($url, 4);
      }

      return $url;
    }


    /**
     *
     * Maybe setup hooks
     * called in the constructor
     *
     * @access private
     *
     */
    private function _schedule_css_and_fonts_enqueuing_or_printing_maybe_on_custom_hook() {
        if ( $this->hook ) {
            add_action( $this->hook, array( $this, 'sek_dyn_css_enqueue_or_print_and_google_fonts_print' ), $this->priority );
        } else {
            //enqueue or print
            $this->sek_dyn_css_enqueue_or_print_and_google_fonts_print();
        }
    }




    /**
     * Enqueue CSS.
     *
     * Either enqueue the CSS file or add inline style, depending on the object mode property.
     * The inline enqueuing is also the fall-back if anything goes wrong while trying to enqueuing the file.
     *
     * This method can also write the file under some circumstances (see when the object force_write || force_rewrite are enabled)
     *
     * @access public
     * @return void()
     */
    public function sek_dyn_css_enqueue_or_print_and_google_fonts_print() {
        // CSS FILE
        //case enqueue file : front end + user with customize caps not logged in
        if ( self::MODE_FILE == $this->mode ) {
            //in case we need to write the file before enqueuing
            //1) $this->css_string_to_enqueue_or_print must exists
            //2) we might need to force the rewrite even if the file exists or to write it if the file doesn't exist
            if ( $this->css_string_to_enqueue_or_print ) {
                if ( $this->force_rewrite || ( !$this->file_exists && $this->force_write ) ) {
                    $this->file_exists = $this->sek_dyn_css_maybe_write_css_file();
                }
            }

            //if the file exists
            if ( $this->file_exists ) {
                //this resource version is built upon the file last modification time
                wp_enqueue_style( "sek-dyn-{$this->id}", $this->url, $this->dep, filemtime($this->uri) );

                $this->enqueued_or_printed = true;
            }
        }// if ( self::MODE_FILE )

        //if $this->mode != 'file' or the file enqueuing didn't go through (fall back)
        //print inline style
        if ( $this->css_string_to_enqueue_or_print && !$this->enqueued_or_printed ) {
            $dep =  array_pop( $this->dep );

            if ( !$dep || wp_style_is( $dep, 'done' ) || !wp_style_is( $dep, 'done' ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
                // only fired when doing ajax during customization in order to return a refreshed partial stylesheet
                printf( '<style id="sek-%1$s" media="all">%2$s</style>',
                    esc_attr($this->id),
                    // Note that esc_html() cannot be used because `div &gt; span` is not interpreted properly.
                    strip_tags($this->css_string_to_enqueue_or_print)
                );
            } else {
                wp_add_inline_style( $dep , $this->css_string_to_enqueue_or_print );
            }

            $this->mode     = self::MODE_INLINE;
            $this->enqueued_or_printed = true;
        }
    }


    /*
    * Public 'actions'
    */
    /**
     *
     * Write the CSS to the disk, if we can
     *
     * @access public
     *
     * @return bool TRUE if the CSS file has been written, FALSE otherwise
     */
    public function sek_dyn_css_maybe_write_css_file() {
        global $wp_filesystem;

        $error = false;

        $base_uri = $this->base_uri;

        // Can we create the folder?
        if ( !$wp_filesystem->is_dir( $base_uri ) ) {
            $error = !wp_mkdir_p( $base_uri );
        }

        if ( $error ) {
            return false;
        }

        if ( !file_exists( $index_path = wp_normalize_path( trailingslashit( $base_uri ) . 'index.php' ) ) ) {
            // predefined mode settings for WP files
            // Make sure NB uses the proper FS_CHMOD_DIR value
            // fixes https://github.com/presscustomizr/nimble-builder/issues/862
            // doc : https://wordpress.org/support/article/editing-wp-config-php/#override-of-default-file-permissions
            // doc : https://wordpress.stackexchange.com/questions/253274/use-of-undefined-constant-fs-chmod-dir-assumed-fs-chmod-dir
            $chmod_dir = ( 0755 & ~ umask() );
            if ( defined( 'FS_CHMOD_DIR' ) ) {
                $chmod_dir = FS_CHMOD_DIR;
            }
            $wp_filesystem->put_contents( $index_path, "<?php\n// Silence is golden.\n", $chmod_dir );
        }


        if ( !wp_is_writable( $base_uri ) ) {
            return false;
        }

        //actual write try and update the file_exists status
        $this->file_exists = $wp_filesystem->put_contents(
            $this->uri,
            $this->css_string_to_enqueue_or_print,//secured earlier with strip_tags()
            // predefined mode settings for WP files
            FS_CHMOD_FILE
        );

        //return whether or not the writing succeeded
        return $this->file_exists;
    }



    /**
     *
     * Maybe remove the CSS file from the disk, if it exists and if empty
     * Note : July 2020 => function updated for https://github.com/presscustomizr/nimble-builder/issues/727
     *
     * @return bool TRUE if the CSS file has been deleted (or didn't exist already), FALSE otherwise
     */
    public function sek_dyn_css_delete_file_if_empty() {
        global $wp_filesystem;
        if ( $this->_sek_dyn_css_file_exists_and_is_empty() ) {
            $this->file_exists != $wp_filesystem->delete( $this->uri );
            return !$this->file_exists;
        }
        return !$this->file_exists;
    }


    /**
     *
     * Remove the CSS file from the disk, if it exists, and even if not empty
     * Note : July 2020 => function updated for https://github.com/presscustomizr/nimble-builder/issues/727
     *
     * @return bool TRUE if the CSS file has been deleted (or didn't exist already), FALSE otherwise
     */
    public function sek_dyn_css_delete_file() {
        global $wp_filesystem;
        if ( $this->file_exists ) {
            $this->file_exists != $wp_filesystem->delete( $this->uri );
            //sek_error_log('CSS HANDLER => REMOVE FILE => ' . $this->uri);
            return !$this->file_exists;
        }
        return !$this->file_exists;
    }


    /*
    * Private helpers
    */

    /**
     *
     * Retrieve the actual CSS file existence on the file system
     *
     * @access private
     *
     * @return bool TRUE if the CSS file exists, FALSE otherwise
     */
    private function _sek_dyn_css_file_exists_is_readable_and_has_content() {
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $this->uri ) ) {
            $file_content = $wp_filesystem->get_contents( $this->uri );
            return $wp_filesystem->is_readable( $this->uri ) && !empty( $file_content );
        } else {
            return false;
        }
    }

    // Note : July 2020 => function introduced for https://github.com/presscustomizr/nimble-builder/issues/727
    private function _sek_dyn_css_file_exists_and_is_empty() {
        global $wp_filesystem;
        if ( $wp_filesystem->exists( $this->uri ) ) {
            $file_content = $wp_filesystem->get_contents( $this->uri );
            return empty( $file_content );
        } else {
            return false;
        }
    }


    /**
     *
     * Build normalized URI of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URI
     */
    private function _sek_dyn_css_build_uri() {
        if ( !isset( $this->base_uri ) ) {
            $this->_sek_dyn_css_build_base_uri();
        }
        //sek_error_log('///////////////////ALORS CSS FILE NAME ??', $this->id );
        return wp_normalize_path( trailingslashit( $this->base_uri ) . "{$this->id}.css" );
    }




    /**
     *
     * Build the URL of the CSS file
     *
     * @access private
     *
     * @return string The absolute CSS file URL
     */
    private function _sek_dyn_css_build_url() {
        if ( !isset( $this->base_url ) ) {
            $this->_sek_dyn_css_build_base_url();
        }
        return trailingslashit( $this->base_url ) . "{$this->id}.css";
    }




    /**
     *
     * Build the URI of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URI
     */
    private function _sek_dyn_css_build_base_uri() {
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . $relative_base_path );
    }




    /**
     *
     * Build the URL of the CSS base directory
     *
     * @access private
     *
     * @return string The absolute CSS base directory URL
     */
    private function _sek_dyn_css_build_base_url() {
        //since 4.5.0
        $upload_dir         = wp_get_upload_dir();

        $relative_base_path = isset( $this->relative_base_path ) ? $this->relative_base_path : $this->_sek_dyn_css_build_relative_base_path();
        return set_url_scheme( trailingslashit( $upload_dir['baseurl'] ) . $relative_base_path );
    }




    /**
     *
     * Retrieve the relative path (to the 'uploads' dir ) of the CSS base directory
     * July 2020 => added a $base_dir param for #727
     *
     * @access private
     *
     * @return string The relative path (to the 'uploads' dir) of the CSS base directory
     */
    private function _sek_dyn_css_build_relative_base_path( $base_dir = null ) {
        $css_base_dir     = is_null($base_dir) ? self::CSS_BASE_DIR : $base_dir;

        if ( is_multisite() ) {
            $site        = get_site();
            $network_id  = $site->site_id;
            $site_id     = $site->blog_id;
            $css_dir     = trailingslashit( $css_base_dir ) . trailingslashit( $network_id ) . $site_id;
        }

        return $css_base_dir;
    }




    /**
     *
     * Checks whether or not we can write to the disk
     *
     * @access private
     *
     * @return bool Whether or not we have filesystem credentials
     */
    //TODO: try to extend this to other methods e.g. FTP when FTP credentials are already defined
    private function _sek_dyn_css_write_file_is_possible() {
        $upload_dir      = wp_get_upload_dir();
        //Note: if the 'uploads' dir has not been created, this check will not pass, hence no file will never be created
        //unless something else creates the 'uploads' dir
        if ( 'direct' === get_filesystem_method( array(), $upload_dir['basedir'] ) ) {
            $creds = request_filesystem_credentials( '', '', false, false, array() );

            /* initialize the API */
            if ( !WP_Filesystem($creds) ) {
                /* any problems and we exit */
                return false;
            }
            return true;
        }

        return false;
    }



    /**
     *
     * Simple helper to require the WordPress filesystem relevant file
     *
     * @access private
     */
    private function _sek_dyn_css_require_wp_filesystem() {
        global $wp_filesystem;

        // Initialize the WordPress filesystem.
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            WP_Filesystem();
        }
    }

}

?>