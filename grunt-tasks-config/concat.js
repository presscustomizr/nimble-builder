module.exports = {
  options: {
    separator: '',
  },

  //
  //------------------------- CUSTOMIZER PANE JS
  //
  czr_fmk_control_js:{
    src: [
      //BASE ! COMPAT AND LIBS : BEFORE API READY
      '<%= paths.global_js %>oldBrowserCompat.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/icheck.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/selecter.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/stepper.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/select2.min.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/lib/rangeslider.min.js',


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
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_0_0_pre_97_api_helpers_various.js',
      '<%= paths.czr_assets %>fmk/js/base-fmk/0_3_api_helpers/_0_0_0_pre_98_api_helpers_dom.js',

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
  //------------------------- CUSTOMIZER PANE CSS
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

  czr_base_fmk_php : {
    src: [
      '<%= paths.czr_base_fmk %>_dev_php/0_0_czr-base-fmk-construct.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_1_czr-base-fmk-load_resources.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_2_czr-base-fmk-ajax_filter.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_3_czr-base-fmk-tmpl_builder.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_4_czr-base-fmk-dynamic-setting-registration.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_5_czr-base-fmk-dynamic-module-registration.php',
      '<%= paths.czr_base_fmk %>_dev_php/0_9_czr-base-fmk-functions.php',
    ],
    dest: '<%= paths.czr_base_fmk %>czr-base-fmk.php',
  },

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


  czr_contextualizer_php : {
    src: [
      '<%= paths.contextualizer_php %>_dev/0_0_0_contx_functions_definitions.php',
      '<%= paths.contextualizer_php %>_dev/0_0_2_contx_functions_skope_post_set_get.php',
      '<%= paths.contextualizer_php %>_dev/1_0_0_contx_class_constructor.php',
      '<%= paths.contextualizer_php %>_dev/1_1_0_contx_filter_options.php',
      '<%= paths.contextualizer_php %>_dev/1_2_0_contx_customize_register_actions.php',
      '<%= paths.contextualizer_php %>_dev/1_3_0_contx_dynamic_setting_registration.php',
      '<%= paths.contextualizer_php %>_dev/1_4_0_contx_customizer_js_css_assets.php'
    ],
    dest: '<%= paths.contextualizer_php %>ccat-contualizer.php',
  },

  czr_contextualizer_js : {
    src: [
      '<%= paths.contextualizer_czr_js %>_dev/_0_0_initialize_module.js',
      '<%= paths.contextualizer_czr_js %>_dev/_0_1_initialize_module_setup_pre_item_inputs.js',
      '<%= paths.contextualizer_czr_js %>_dev/_0_2_initialize_module_helpers.js',
      '<%= paths.contextualizer_czr_js %>_dev/_1_0_input_constructor_module.js',
      '<%= paths.contextualizer_czr_js %>_dev/_1_1_input_constructor_module_pre_item_select.js',
      '<%= paths.contextualizer_czr_js %>_dev/_2_0_item_constructor_module.js',
      '<%= paths.contextualizer_czr_js %>_dev/_8_0_skopeReact_initialize.js',
      '<%= paths.contextualizer_czr_js %>_dev/_8_1_skopeReact_helpers_utilities.js',
      '<%= paths.contextualizer_czr_js %>_dev/_8_2_skopeReact_scheduleSkopeReactions.js',
      '<%= paths.contextualizer_czr_js %>_dev/_8_3_skopeReact_bottomControlSkopeNote.js',
      '<%= paths.contextualizer_czr_js %>_dev/_8_4_skopeReact_setup_dynamic_settings_controls.js',
      '<%= paths.contextualizer_czr_js %>_dev/_9_9_extend_czrModuleMap_and_instantiate_skopeReact.js'
    ],
    dest: '<%= paths.contextualizer_czr_js %>contextualizer-control.js',
  },

  czr_sektions_php : {
    src: [
      '<%= paths.sektions %>_dev_php/0_5_0_sektions_functions_definitions.php',
      '<%= paths.sektions %>_dev_php/0_9_0_sektions_functions_seks_post_set_get.php',
      '<%= paths.sektions %>_dev_php/1_0_0_sektions_customizer_assets.php',
      '<%= paths.sektions %>_dev_php/2_0_0_sektions_customizer_dynamic_registration.php',
      '<%= paths.sektions %>_dev_php/3_0_0_sektions_register_modules_after_setup_theme_50.php',
      '<%= paths.sektions %>_dev_php/3_0_1_sektions_add_input_templates.php',

      '<%= paths.sektions %>_dev_php/5_0_0_class-sek-dyn-css-builder.php',
      '<%= paths.sektions %>_dev_php/5_0_1_class-sek-dyn-css-handler.php',

      '<%= paths.sektions %>_dev_php/8_0_0_sektions_front_class_construct.php',
      '<%= paths.sektions %>_dev_php/8_0_1_sektions_front_class_ajax.php',
      '<%= paths.sektions %>_dev_php/8_2_0_sektions_front_class_front_and_preview_assets.php',
      '<%= paths.sektions %>_dev_php/8_4_0_sektions_front_class_render.php',
      '<%= paths.sektions %>_dev_php/8_4_1_sektions_front_class_render_css.php',
      '<%= paths.sektions %>_dev_php/8_9_0_sektions_front_class_instantiate.php'
    ],
    dest: '<%= paths.sektions %>ccat-sektions.php',
  },

  czr_sektions_customizer_js : {
    src: [
      '<%= paths.sektions %>assets/czr/js/_dev_control/_1_0_sektions_itinialize.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_1_5_sektions_setup_collection_setting.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_1_6_sektions_generate_UI.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_1_7_sektions_update_API_setting.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_4_0_sektions_react_to_preview.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_4_1_sektions_setup_and_react_to_drop.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_6_0_sektions_register_utils.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_7_0_sektions_various_utils.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_7_1_sektions_tinyMceEditor.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/_8_0_sektions_instantiate.js',

      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/ui/_9_0_0_UI_sek_level_layout_bg_module.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/ui/_9_0_1_UI_spacing.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/ui/_9_0_2_UI_module_picker.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/ui/_9_0_3_UI_section_picker.js',

      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/front/_9_1_0_FRONT_image.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/front/_9_1_1_FRONT_tiny_mce_editor.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/front/_9_1_2_FRONT_simple_html.js',
      '<%= paths.sektions %>assets/czr/js/_dev_control/modules/front/_9_1_3_FRONT_featured_pages.js'

      // DEPRECATED
      //'<%= paths.sektions %>assets/czr/js/_dev/_1_3_sektions_setup_server_collection.js',
      //'<%= paths.sektions %>assets/czr/js/_dev/_1_8_sektions_update_UI.js',
      // '<%= paths.sektions %>assets/czr/js/_dev/_3_0_sektions_add_new_sektion.js',
      // '<%= paths.sektions %>assets/czr/js/_dev/_3_1_sektions_add_new_column.js',
      // '<%= paths.sektions %>assets/czr/js/_dev/_3_2_sektions_add_new_module.js',
    ],
    dest: '<%= paths.sektions %>assets/czr/js/ccat-sektions.js',
  },

  czr_sektions_customizer_preview_js : {
     src: [
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_1_0_preview_itinialize.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_2_0_preview_sortable_setup.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_2_1_preview_resizable_setup.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_2_2_preview_ui_hover_visibility_setup.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_2_3_preview_ui_click_actions_setup.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_4_0_preview_panel_react_ajax_setup.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_4_1_preview_panel_ajax_sections.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_4_2_preview_panel_ajax_columns.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_4_3_preview_panel_ajax_modules.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_4_4_preview_panel_react_dynamic_style.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_9_0_preview_utilities.js',
      '<%= paths.sektions %>assets/czr/js/_dev_preview/_9_9_preview_instantiate.js'
    ],
    dest: '<%= paths.sektions %>assets/czr/js/sek-preview.js',
  },

  czr_sektions_front_fmk_js : {
    src: [
      '<%= paths.sektions %>assets/front/js/_front_js_fmk/_main_base_0_utilities.part.js',
      '<%= paths.sektions %>assets/front/js/_front_js_fmk/_main_base_1_fmk.part.js',
      '<%= paths.sektions %>assets/front/js/_front_js_fmk/_main_xfire_0.part.js',

      // DEPRECATED
      //'<%= paths.sektions %>assets/czr/js/_dev/_1_3_sektions_setup_server_collection.js',
      //'<%= paths.sektions %>assets/czr/js/_dev/_1_8_sektions_update_UI.js',
      // '<%= paths.sektions %>assets/czr/js/_dev/_3_0_sektions_add_new_sektion.js',
      // '<%= paths.sektions %>assets/czr/js/_dev/_3_1_sektions_add_new_column.js',
      // '<%= paths.sektions %>assets/czr/js/_dev/_3_2_sektions_add_new_module.js',
    ],
    dest: '<%= paths.sektions %>assets/front/js/_front_js_fmk.js',
  },


  // //THEMES : FREE MODULES
  // //'<%= paths.czr_assets %>fmk/js/themes/5_0_module_list/free/_2_7_socials_module.js',
  // '<%= paths.czr_assets %>fmk/js/themes/5_0_module_list/free/_2_6_widget_areas_module.js',
  // '<%= paths.czr_assets %>fmk/js/themes/5_0_module_list/free/_3_2_body_background_module.js',

  // '<%= paths.czr_assets %>fmk/js/themes/5_0_module_list/5_0_0_free_modules_map.js',


// czr_skope_control_js:{
//     src: [
//       //SKOPE : REQUIRED API OVERRIDE
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/0_0_0_api_override/_0_0_0_pre_901_query.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/0_0_0_api_override/_0_0_0_pre_902_save.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/0_0_0_api_override/_0_0_0_pre_904_synchronizer.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/0_0_0_api_override/_0_0_0_pre_905_refresh.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/0_0_0_api_override/_0_0_0_pre_906_dirtyValues.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/0_0_0_api_override/_0_0_0_pre_907_requestChangesetUpdate.js',

//       //SKOPE : CORE
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_000_skope_schedule_fire_on_ready.js',

//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_100_skope_base_init.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_101_skope_base_server_notification.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_102_skope_base_top_notification.js',

//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_103_skope_base_bind_api_settings.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_104_skope_base_react_on_skopes_sync.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_105_skope_base_section_panel_react.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_106_skope_base_paint_wash.js',

//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_107_skope_base_bottom_informations.js',

//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_111_skope_base_helpers_utilities.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_112_skope_base_helpers_priority_inheritance.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_200_skope_base_current_skopes_collection.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_210_skope_base_active_skope_react.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_300_skope_base_silent_update.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_301_skope_base_special_silent_updates.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_400_skope_base_control_setup.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_401_skope_base_control_reset.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_402_skope_base_control_skope_notice.js',

//       //<@wp4.9compat>
//       //=> the skope saving is handled server side when the customize_changeset post transitions to "publish"
//       // '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_501_skope_save_initialize.js',
//       // '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_502_skope_save_submit_promise.js',
//       // '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_503_skope_save_recursive_calls.js',
//       // '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_504_skope_save_postprocessing.js',
//       //</@wp4.9compat>

//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_600_skope_reset.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_700_skope_widget_sidebar_specifics.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_910_skope_model_init.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_920_skope_model_view.js',
//       '<%= paths.czr_assets %>fmk/js/control_dev/0_1_skope/_0_0_0_pre_930_skope_model_reset.js',


//       //SKOPE : EXTEND API
//       '<%= paths.czr_assets %>fmk/js/control_dev/7_0_extend_api/_1_extend_api_skope.js',
//     ],
//     dest: '<%= paths.czr_assets %>fmk/js/_part_1_czr-control-skope.js',
//   },












  // czr_pro_modules_control_js:{
  //   src: [
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/_2_9_fps_module.js',

  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/_3_0_text_module.js',

  //     //'<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/_3_1_slider_module.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/slider/_3_1_0_slid_mod_init.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/slider/_3_1_1_slid_mod_input_ctors.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/slider/_3_1_2_slid_mod_item_ctors.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/slider/_3_1_3_slid_mod_modopt_ctors.js',

  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/related_posts/_4_1_0_related_mod_init.js',

  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/_3_15_text_editor_module.js',

  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_0_init.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_1_sektion_item_extend.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_2_sektion_column.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_3_dragula.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_4_modules_panel.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_5_column_class_init.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_6_column_class_collection.js',
  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/pro/sektion/_2_8_sektions_module_7_settings_panel.js',

  //     '<%= paths.czr_assets %>fmk/js/control_dev/5_0_module_list/5_0_1_pro_modules_map.js',
  //   ],
  //   dest: '<%= paths.czr_assets %>fmk/js/czr-pro-modules-control.js'
  // },

  // czr_base_control_js: {
  //   src: [
  //     '<%= paths.czr_assets %>fmk/js/_part_0_czr-control-fmk.js',
  //     '<%= paths.czr_assets %>fmk/js/_part_1_czr-control-skope.js',
  //     '<%= paths.czr_assets %>fmk/js/_part_2_czr-control-themes.js'
  //   ],
  //   dest : '<%= paths.czr_assets %>fmk/js/czr-control-base.js'
  // },


  // czr_pro_control_js: {
  //   src: [
  //     '<%= paths.czr_assets %>fmk/js/_part_0_czr-control-fmk.js',
  //     '<%= paths.czr_assets %>fmk/js/_part_1_czr-control-skope.js',
  //     '<%= paths.czr_assets %>fmk/js/_part_2_czr-control-themes.js',
  //     '<%= paths.czr_assets %>fmk/js/czr-pro-modules-control.js'
  //   ],
  //   dest : '<%= paths.czr_assets %>fmk/js/czr-control-full.js'
  // },

};