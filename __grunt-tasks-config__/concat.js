module.exports = {
  options: {
    separator: '',
  },

  //
  //------------------------- CUSTOMIZER BASE FMK CONTROL JS
  //
  czr_fmk_control_js:{
    src: [
      //BASE ! COMPAT AND LIBS : BEFORE API READY
      '<%= paths.global_js %>oldBrowserCompat.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/icheck.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/selecter.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/stepper.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/czrSelect2.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/rangeslider.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/czr-alpha-colorpicker.min.js',


      //BASE : BEFORE API READY
      '<%= paths.czr_assets %>fmk/js/base-fmk/_0_0_pre_previewed_device_event.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/_0_1_pre_helpers.js',

      //'<%= paths.czr_assets %>fmk/js/base-fmk/_0_pre_base.js',
      //BASE : ON API READY
      '<%= paths.czr_assets %>fmk/js/base-fmk/_0_2_api_ready_themes_section_visibility.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/_0_3_api_ready_watch_section_panel_expansion.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/_0_4_api_ready_dynamic_registration.js',

      //BASE : API OVERRIDES : BEFORE API READY
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_2_api_override/_0_0_0_pre_900_Value_prototype.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_2_api_override/_0_0_0_pre_908_Setting_prototype.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_2_api_override/_0_0_0_pre_990_various_overrides.js',


      //BASE : HELPERS
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_0_0_pre_97_api_helpers_setup_input_from_dom.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_0_1_pre_97_api_helpers_get_module_template.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_0_2_pre_97_api_helpers_register_utils.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_0_9_pre_97_api_helpers_various.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_1_0_pre_98_api_helpers_setup_dom_listeners.js',


      '<%= paths.czr_assets %>fmk/js/base-fmk/0_4_preview_listeners/_0_0_0_pre_99_preview_listeners.js',


      //BASE : INPUT / ITEM / MODULE / CONTROL
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_0_input/_0_0_1_input_0_init.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_0_input/_0_0_1_input_1_img_upload.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_0_input/_0_0_1_input_4_content_picker.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_0_input/_0_0_1_input_5_text_editor.js',

      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_1_item_and_modopt/1_1_0_item/_0_0_2_item_0_init.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_1_item_and_modopt/1_1_0_item/_0_0_2_item_2_model.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_1_item_and_modopt/1_1_0_item/_0_0_2_item_3_view.js',

      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_1_item_and_modopt/1_1_1_module_options/_0_0_2_mod_opt_0_init.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_1_item_and_modopt/1_1_1_module_options/_0_0_2_mod_opt_2_view.js',

      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_3_module/_0_0_3_module_0_init.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_3_module/_0_0_3_module_1_collection.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_3_module/_0_0_3_module_2_model.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_3_module/_0_0_3_module_3_view.js',

      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_3_module/_0_0_4_dyn_module_0_init.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_3_module/_0_0_4_dyn_module_1_pre_item_views.js',

      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_4_control/_0_1_0_base_control.js',

      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_4_control/base_module_control/_0_1_0_base_module_control_init.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_4_control/base_module_control/_0_1_1_base_module_control_instantiate.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_4_control/base_module_control/_0_1_2_base_module_control_collection.js',

      // '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_4_control/multi_module_control/_0_2_0_multi_module_control_init.js',
      // '<%= paths.czr_assets %>fmk/js/base-fmk/1_0_input_item_module_control/1_4_control/multi_module_control/_0_2_1_multi_module_control_mod_extender.js',

      //BASE : EXTEND API
      '<%= paths.czr_assets %>fmk/js/base-fmk/_0_9_extend_api_base.js',

      //BASE : VARIOUS DOM READY SCHEDULED ACTIONS
      '<%= paths.czr_assets %>fmk/js/base-fmk/_7_various_fmk_dom_ready.js'
    ],
    dest: '<%= paths.czr_base_fmk %>assets/js/_0_ccat_czr-base-fmk.js',
  },


  czr_theme_control_js:{
    src: [
      //THEMES : BEFORE AND WHEN API READY
      '<%= paths.czr_assets %>fmk/js/themes/_0_4_pre_section_panel_ubiquity.js',
      '<%= paths.czr_assets %>fmk/js/themes/_0_5_pre_pro_section.js',

      //THEME : SPECIFICS CONTROLS
      '<%= paths.czr_assets %>fmk/js/themes/_2_1_multiplepicker_control.js',
      '<%= paths.czr_assets %>fmk/js/themes/_2_2_cropped_image_control.js',
      '<%= paths.czr_assets %>fmk/js/themes/_2_3_upload_control.js',
      '<%= paths.czr_assets %>fmk/js/themes/_2_4_layout_control.js',

      //THEME : EXTEND API
      '<%= paths.czr_assets %>fmk/js/themes/_2_extend_api_theme_controls.js',

      //THEME : SPECIFIC DEPENDENCY MANAGEMENT + DOM READY
      '<%= paths.czr_assets %>fmk/js/themes/_6_control_dependencies.js',
      '<%= paths.czr_assets %>fmk/js/themes/_8_various_theme_dom_ready.js'
    ],
    dest: '<%= paths.czr_base_fmk %>assets/js/_1_ccat_czr-theme-fmk.js',
  },

  //
  //------------------------- CUSTOMIZER BASE FMK CONTROL CSS
  //
  czr_control_css:{
    src:[
      '<%= paths.czr_assets %>fmk/css/parts/czr-control-common.css',
      '<%= paths.czr_assets %>fmk/css/parts/czr-control-modules.css',
      '<%= paths.czr_assets %>fmk/css/parts/czr-control-input-types.css',
      //'<%= paths.czr_assets %>fmk/css/parts/czr-control-footer.css',
      //'<%= paths.czr_assets %>fmk/css/parts/czr-control-sektion.css',
      //'<%= paths.czr_assets %>fmk/css/parts/czr-control-skope.css'
    ],
    dest : '<%= paths.czr_base_fmk %>assets/css/czr-ccat-control-base.css',
  },

  //
  //------------------------- CUSTOMIZER BASE FMK PHP
  //
  czr_base_fmk_php : {
    src: [
      '<%= paths.czr_base_fmk %>_dev_php/0_0_czr-base-fmk-construct.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_1_czr-base-fmk-load_resources.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_2_czr-base-fmk-ajax_filter.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_3_czr-base-fmk-tmpl_builder.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_4_czr-base-fmk-dynamic-setting-registration.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_5_czr-base-fmk-dynamic-module-registration.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_6_czr-base-fmk-content_picker-ajax_actions.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_9_czr-base-fmk-functions.php',
    ],
    dest: '<%= paths.czr_base_fmk %>czr-base-fmk.php',
  },




  //
  //------------------------- SKOPE PHP AND JS
  //
  czr_flat_skope_php : {
    src: [
      '<%= paths.flat_skope_php %>_dev/0_0_1_skop_functions_skope_helpers.php',
      '<%= paths.flat_skope_php %>_dev/0_1_0_skop_base_class.php',
      '<%= paths.flat_skope_php %>_dev/1_0_0_skop_customizer_register_and_load_control_assets.php',
      '<%= paths.flat_skope_php %>_dev/1_1_0_skop_customizer_preview_load_assets.php',
      '<%= paths.flat_skope_php %>_dev/3_0_0_skop_clean_skope_for_deleted_objects.php'
    ],
    dest: '<%= paths.flat_skope_php %>index.php',
  },

  czr_flat_skope_js : {
    src: [
      '<%= paths.flat_skope_czr_js %>_dev/_8_0_skopebase_initialize.js',
      '<%= paths.flat_skope_czr_js %>_dev/_8_1_skopebase_helpers_utilities.js',
      '<%= paths.flat_skope_czr_js %>_dev/_8_2_skopebase_collection_populate_and_react.js',
      '<%= paths.flat_skope_czr_js %>_dev/_9_9_skopebase_instantiate.js'
    ],
    dest: '<%= paths.flat_skope_czr_js %>czr-skope-base.js',
  },











  //
  //------------------------- SEKTIONS FRONT JS
  //
  sektions_front_js : {
    src: [
      '<%= paths.front_assets %>js/_dev_front/0_0_0_front_underscore.js',
      '<%= paths.front_assets %>js/_dev_front/1_0_0_front_fittext.js',
      '<%= paths.front_assets %>js/_dev_front/1_0_1_front_smartload.js',
      '<%= paths.front_assets %>js/_dev_front/1_0_2_front_parallax_background.js',
      '<%= paths.front_assets %>js/_dev_front/9_9_9_front_fire.js'
    ],
    dest: '<%= paths.front_assets %>js/ccat-nimble-front.js',
  },


  //
  //------------------------- SEKTIONS CONTROL JS
  //
  czr_sektions_customizer_control_js : {
    src: [
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_0_sektions_initialize.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_1_1_sektions_topbar.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_1_2_sektions_navigate_history.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_1_3_sektions_level_tree.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_1_5_sektions_save_ui.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_1_8_sektions_revision_history.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_2_sektions_setup_collection_setting.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_4_sektions_react_to_preview.js',

      // UI Generators
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_6_0_sektions_generate_UI_base.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_6_1_sektions_generate_UI_content_pickers.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_6_2_sektions_generate_UI_front_modules.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_6_3_sektions_generate_UI_level_options_modules.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_6_4_sektions_generate_UI_local_skope_options.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_1_6_5_sektions_generate_UI_global_options.js',

      '<%= paths.czr_assets %>sek/js/_dev_control/_1_7_sektions_update_API_setting.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_2_0_sektions_register_utils.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_2_1_sektions_various_utils.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_4_1_sektions_setup_and_react_to_drop.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_7_1_sektions_tinyMceEditor.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/_8_0_sektions_instantiate.js',

      // Inputs
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_0_spacing_input.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_1_bg_position_input.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_2_vert_and_horiz_alignment_inputs.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_3_font_size_input.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_4_line_height_input.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_5_font_picker_input.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_6_font_awesome_icon_picker.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_7_code_editor.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_8_range_simple.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_7_0_9_range_with_unit_picker.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_8_0_9_range_with_unit_picker_device_switcher.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_8_1_0_borders.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_8_1_1_border_radius.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_8_1_2_buttons_choice.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_8_1_3_reset_button.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/inputs/_8_1_4_revision_history.js',

      // Content pickers
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_10_0_0_UI_module_and_section_pickers.js',

      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_anchor_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_bg_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_border_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_breakpoint_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_height_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_visibility_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_width_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_level_width_section.js',
      //'<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_0_UI_sek_section_layout_module.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_1_level/_9_0_1_UI_spacing.js',

      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_0_UI_local_template.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_1_UI_local_widths.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_2_UI_local_custom_css.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_3_UI_local_reset.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_4_UI_local_performances.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_5_UI_local_header_footer.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_2_local/_9_1_6_UI_local_revisions.js',

      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_3_global/_9_1_1_UI_global_breakpoint.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_3_global/_9_1_2_UI_global_widths.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_3_global/_9_1_3_UI_global_performances.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_3_global/_9_1_4_UI_global_header_footer.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_3_global/_9_1_5_UI_global_google_recaptcha.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/ui/_3_global/_9_1_9_UI_global_beta_features.js',

      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_0_FRONT_image.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_1_FRONT_tiny_mce_editor.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_2_FRONT_simple_html.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_3_FRONT_featured_pages.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_4_FRONT_icon.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_5_FRONT_heading.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_6_FRONT_divider.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_7_FRONT_spacer.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_8_FRONT_map.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_1_9_FRONT_quote.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_2_0_FRONT_button.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_2_1_FRONT_menu.js',

      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/simple_form/_9_2_11_FRONT_simple_form_fields.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/simple_form/_9_2_12_FRONT_simple_form_design.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/simple_form/_9_2_13_FRONT_simple_form_button.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/simple_form/_9_2_14_FRONT_simple_form_fonts.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/simple_form/_9_2_15_FRONT_simple_form_submission.js',

      // CHILD MODULES
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_5_0_FRONT_font_child.js',
      '<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_5_1_FRONT_widget_area.js',

      // PRO MODULES
      //'<%= paths.czr_assets %>sek/js/_dev_control/modules/front/_9_5_2_FRONT_nimble_special_image.js'
    ],
    dest: '<%= paths.czr_assets %>sek/js/ccat-sek-control.js',
  },


  //
  //------------------------- SEKTIONS PREVIEW JS
  //
  czr_sektions_customizer_preview_js : {
     src: [
      '<%= paths.czr_assets %>sek/js/_dev_preview/_1_0_preview_initialize.js',

      '<%= paths.czr_assets %>sek/js/_dev_preview/_2_0_preview_sortable_setup.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_2_1_preview_resizable_setup.js',

      '<%= paths.czr_assets %>sek/js/_dev_preview/_2_2_preview_ui_hover_visibility_setup.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_2_3_preview_ui_click_actions_setup.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_2_4_preview_ui_setup_loader.js',

      '<%= paths.czr_assets %>sek/js/_dev_preview/_4_0_preview_panel_reactions_setup.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_4_1_preview_panel_ajax_sections.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_4_2_preview_panel_ajax_columns.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_4_3_preview_panel_ajax_modules.js',
      '<%= paths.czr_assets %>sek/js/_dev_preview/_4_4_preview_panel_react_dynamic_style.js',

      '<%= paths.czr_assets %>sek/js/_dev_preview/_9_0_preview_utilities.js',

      '<%= paths.czr_assets %>sek/js/_dev_preview/_9_9_preview_instantiate.js'
    ],
    dest: '<%= paths.czr_assets %>sek/js/ccat-sek-preview.js',
  },




  //
  //------------------------- SEKTIONS FRONT PHP
  //
  czr_sektions_front_php : {
    src: [
      '<%= paths.sektions %>_front_dev_php/0_5_0_sektions_functions_definitions.php',
      '<%= paths.sektions %>_front_dev_php/0_6_0_sektions_nimble_customize_link.php',
      '<%= paths.sektions %>_front_dev_php/0_7_0_sektions_retro_compatibilities.php',
      '<%= paths.sektions %>_front_dev_php/0_9_0_sektions_functions_seks_post_set_get.php',
      '<%= paths.sektions %>_front_dev_php/0_9_1_sektions_functions_saved_sektions.php',
      '<%= paths.sektions %>_front_dev_php/0_9_2_sektions_functions_revision_history.php',

      // MODULE REGISTRATION
      '<%= paths.sektions %>_front_dev_php/module_registration/10_0_0_sek_module_helpers_for_css_rules_generation.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/4_0_0_sek_register_modules_after_setup_theme_50.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/ui/10_0_1_sek_register_module_and_section_pickers.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_3_sek_register_background.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_3_sek_register_border.php',
      //'<%= paths.sektions %>_front_dev_php/module_registration/ui/4_0_3_sek_register_section_layout.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_3_sek_register_height.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_4_sek_register_spacing.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_5_sek_register_width_module.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_5_sek_register_width_section.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_6_sek_register_anchor.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_7_sek_register_visibility.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_1_level/4_0_8_sek_register_breakpoint.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_0_sek_register_local_template.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_1_sek_register_local_widths.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_2_sek_register_local_custom_css.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_3_sek_register_local_reset.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_4_sek_register_local_performances.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_5_sek_register_local_header_footer.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_2_local/4_1_6_sek_register_local_revisions.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_1_sek_register_global_breakpoint.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_2_sek_register_global_widths.php',
      //'<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_3_sek_register_global_reset.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_4_sek_register_global_performances.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_5_sek_register_global_header_footer.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_6_sek_register_global_google_recaptcha.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/ui/_3_global/4_1_9_sek_register_beta_features.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_0_5_sek_register_simple_html.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_0_6_sek_register_tiny_mce_editor.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_0_7_sek_register_image.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_0_8_sek_register_featured_pages.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_0_sek_register_heading.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_1_sek_register_spacer.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_2_sek_register_divider.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_3_sek_register_icon.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_4_sek_register_map.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_5_sek_register_quote.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_6_sek_register_button.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_70_sek_register_simple_form.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_1_8_sek_register_menu.php',

      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_2_0_sek_register_font_child.php',
      '<%= paths.sektions %>_front_dev_php/module_registration/front/4_2_1_sek_register_widget_area.php',

      // PRO MODULES
      //'<%= paths.sektions %>_front_dev_php/module_registration/front/4_3_0_sek_register_nimble_special_image.php',

      // DYN CSS
      '<%= paths.sektions %>_front_dev_php/dyn_css_builder_and_google_fonts_printer/5_0_1_class-sek-dyn-css-builder.php',
      '<%= paths.sektions %>_front_dev_php/dyn_css_builder_and_google_fonts_printer/5_0_2_class-sek-dyn-css-handler.php',
      '<%= paths.sektions %>_front_dev_php/dyn_css_builder_and_google_fonts_printer/5_0_3_add_rules_for_generic_input_types.php',

      // SEK_Front_Render()
      '<%= paths.sektions %>_front_dev_php/8_0_0_sektions_front_class_construct.php',
      '<%= paths.sektions %>_front_dev_php/8_0_1_sektions_front_class_ajax.php',
      '<%= paths.sektions %>_front_dev_php/8_2_0_sektions_front_class_front_and_preview_assets.php',
      '<%= paths.sektions %>_front_dev_php/8_4_0_sektions_front_class_render.php',
      '<%= paths.sektions %>_front_dev_php/8_4_1_sektions_front_class_render_css.php',

      '<%= paths.sektions %>_front_dev_php/_simple_form_classes/8_5_0_sektions_front_class_simple_forms_main_class.php',
      '<%= paths.sektions %>_front_dev_php/_simple_form_classes/8_5_1_sektions_front_class_simple_forms_form_and_field.php',
      '<%= paths.sektions %>_front_dev_php/_simple_form_classes/8_5_2_sektions_front_class_simple_forms_inputs.php',
      '<%= paths.sektions %>_front_dev_php/_simple_form_classes/8_5_3_sektions_front_class_simple_forms_mailer.php',

      '<%= paths.sektions %>_front_dev_php/8_9_0_sektions_front_class_instantiate.php'
    ],
    dest: '<%= paths.sektions %>ccat-sektions.php',
  },


  //
  //------------------------- SEKTIONS CUSTOMIZER PHP
  //
  czr_sektions_customizer_php : {
      src: [
        '<%= paths.sektions %>_customizer_dev_php/1_0_0_sektions_customizer_assets.php',
        '<%= paths.sektions %>_customizer_dev_php/2_0_0_sektions_customizer_dynamic_registration.php',
        '<%= paths.sektions %>_customizer_dev_php/9_0_0_sektions_wp_5_0_gutenberg_compat.php',
        '<%= paths.sektions %>_customizer_dev_php/seks_tiny_mce_editor_actions.php',

        // INPUT TEMPLATES
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_1_sek_input_tmpl_base_filter.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_2_sek_input_tmpl_module_sektion_picker.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_3_sek_input_tmpl_spacing.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_4_sek_input_tmpl_bg_position.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_5_sek_input_tmpl_horizontal_alignment.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_6_sek_input_tmpl_vertical_alignment.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_71_sek_input_tmpl_fa_icon_picker.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_7_sek_input_tmpl_font_picker.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_8_sek_input_tmpl_font_size_line_height.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_0_9_sek_input_tmpl_code_editor.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_1_sek_input_tmpl_range_simple.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_2_sek_input_tmpl_range_with_unit_picker.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_3_sek_input_tmpl_range_with_unit_picker_device_switcher.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_4_sek_input_tmpl_borders.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_5_sek_input_tmpl_border_radius.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_6_sek_input_tmpl_buttons_choice.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_7_sek_input_tmpl_reset_button.php',
        '<%= paths.sektions %>_customizer_dev_php/input_tmpl/3_1_8_sek_input_tmpl_revision_history.php'
      ],
      dest: '<%= paths.sektions %>ccat-czr-sektions.php',
  },

  // czr_sektions_front_fmk_js : {
  //   src: [
  //     // LIBS
  //     // '<%= paths.front_assets %>js/libs/oldBrowserCompat.js',

  //     // '<%= paths.front_assets %>js/libs/jquery-plugins/jqueryimgOriginalSizes.js',
  //     // '<%= paths.front_assets %>js/libs/jquery-plugins/jqueryimgSmartLoad.js',
  //     // '<%= paths.front_assets %>js/libs/jquery-plugins/jqueryCenterImages.js',
  //     // '<%= paths.front_assets %>js/libs/jquery-plugins/jqueryParallax.js',

  //     // '<%= paths.front_assets %>js/libs/requestAnimationFramePolyfill.js',
  //     // '<%= paths.front_assets %>js/libs/matchMediaPolyfill.js',

  //     // '<%= paths.front_assets %>js/libs/waypoints.js',

  //     // FMK
  //     '<%= paths.front_assets %>js/_front_js_fmk/_main_base_0_utilities.part.js',
  //     '<%= paths.front_assets %>js/_front_js_fmk/_main_base_1_fmk.part.js',

  //     // APP MAPS CALLBACK
  //     // '<%= paths.front_assets %>js/_parts/_main_base_2_initialize.part.js',
  //     // '<%= paths.front_assets %>js/_parts/_main_jquery_plugins.part.js',

  //     // FMK FIRE
  //     '<%= paths.front_assets %>js/_front_js_fmk/_main_xfire_0.part.js',

  //     // APP MAP FIRE
  //     // '<%= paths.front_assets %>js/_parts/_z_main_xfire_app_map.part.js'
  //   ],
  //   dest: '<%= paths.front_assets %>js/_front_js_fmk.js',
  // }
};