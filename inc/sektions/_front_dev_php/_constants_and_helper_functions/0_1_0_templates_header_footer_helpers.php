<?php
/* ------------------------------------------------------------------------- *
 *   TEMPLATE OVERRIDE HELPERS
/* ------------------------------------------------------------------------- */
// TEMPLATES PATH
// added for #532, october 2019
/**
 * Returns the path to the NIMBLE templates directory
 * inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
 */
function sek_get_templates_dir() {
  return NIMBLE_BASE_PATH . "/tmpl";
}

// added for #532, october 2019
/* Returns the template directory name.
 * inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
*/
function sek_get_theme_template_dir_name() {
  return trailingslashit( apply_filters( 'nimble_templates_dir', 'nimble_templates' ) );
}


// added for #532, october 2019
/**
 * Returns a list of paths to check for template locations
 * inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
 */
function sek_get_theme_template_base_paths() {

  $template_dir = sek_get_theme_template_dir_name();

  $file_paths = array(
    1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
    10 => trailingslashit( get_template_directory() ) . $template_dir
  );

  $file_paths = apply_filters( 'nimble_template_paths', $file_paths );

  // sort the file paths based on priority
  ksort( $file_paths, SORT_NUMERIC );

  return array_map( 'trailingslashit', $file_paths );
}


// @return path string
// added for #400
// @param params = array(
//  'file_name' string 'nimble_template.php',
//  'folder' =>  string 'page-templates', 'header', 'footer'
// )
// @param
function sek_maybe_get_overriden_local_template_path( $params = array() ) {
    if ( empty( $params ) || !is_array( $params ))
      return;
    $params = wp_parse_args( $params, array( 'file_name' => '', 'folder' => 'page-templates' ) );

    if ( !in_array( $params['folder'] , array( 'page-templates', 'header', 'footer' ) ) )
      return;

    $overriden_template_path = '';
    // try locating this template file by looping through the template paths
    // inspîred from /wp-content/plugins/easy-digital-downloads/includes/template-functions.php
    foreach( sek_get_theme_template_base_paths() as $path_candidate ) {
      if( file_exists( $path_candidate . $params['folder'] . '/' . $params['file_name'] ) ) {
        $overriden_template_path = $path_candidate . $params['folder'] . '/' . $params['file_name'];
        break;
      }
    }
    return $overriden_template_path;
}



// @return mixed null || string
function sek_get_locale_template(){
    $template_path = null;
    $local_template_data = sek_get_local_option_value( 'template' );
    if ( !empty( $local_template_data ) && !empty( $local_template_data['local_template'] ) && 'default' !== $local_template_data['local_template'] ) {
        $template_file_name = $local_template_data['local_template'];
        $template_file_name_with_php_extension = $template_file_name . '.php';

        // Set the default template_path first
        $template_path = sek_get_templates_dir() . "/page-templates/{$template_file_name_with_php_extension}";
        // Make this filtrable
        // (this filter is used in Hueman theme to assign a specific template)
        $template_path = apply_filters( 'nimble_get_locale_template_path', $template_path, $template_file_name );

        // Use an override if any
        // Default page tmpl path looks like : NIMBLE_BASE_PATH . "/tmpl/page-template/nimble_template.php",
        $overriden_template_path = sek_maybe_get_overriden_local_template_path( array( 'file_name' => $template_file_name_with_php_extension, 'folder' => 'page-templates' ) );
        if ( !empty( $overriden_template_path ) ) {
            $template_path = $overriden_template_path;
        }

        if ( !file_exists( $template_path ) ) {
            sek_error_log( __FUNCTION__ .' the custom template does not exist', $template_path );
            $template_path = null;
        }
    }
    return $template_path;
}



/* ------------------------------------------------------------------------- *
 *  HEADER FOOTER
/* ------------------------------------------------------------------------- */
// fired by sek_maybe_set_local_nimble_footer() @get_footer()
// fired by sek_maybe_set_local_nimble_header() @get_header()
function sek_page_uses_nimble_header_footer() {
    // cache the properties if not done yet
    Nimble_Manager()->sek_maybe_set_nimble_header_footer();
    return true === Nimble_Manager()->has_local_header_footer || true === Nimble_Manager()->has_global_header_footer;
}


// DEPRECATED SINCE Nimble v1.3.0, november 2018
// was used in the Hueman theme before version 3.4.9
function render_content_sections_for_nimble_template() {
    Nimble_Manager()->render_nimble_locations(
        array_keys( Nimble_Manager()->default_locations ),//array( 'loop_start', 'before_content', 'after_content', 'loop_end'),
        array( 'fallback_location' => 'loop_start' )
    );
}

?>