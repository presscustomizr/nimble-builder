
(function ( api, $, _ ) {
//provides a description of each module
      //=> will determine :
      //1) how to initialize the module model. If not crud, then the initial item(s) model shall be provided
      //2) which js template(s) to use : if crud, the module template shall include the add new and pre-item elements.
      //   , if crud, the item shall be removable
      //3) how to render : if multi item, the item content is rendered when user click on edit button.
      //    If not multi item, the single item content is rendered as soon as the item wrapper is rendered.
      //4) some DOM behaviour. For example, a multi item shall be sortable.
      api.czrModuleMap = api.czrModuleMap || {};
      $.extend( api.czrModuleMap, {
            // czr_sektion_module : {
            //       mthds : CZRSektionMths,
            //       crud : true,
            //       name : 'Sections'
            // },
            // czr_fp_module : {
            //       mthds : CZRFeaturedPageModuleMths,
            //       crud : true,
            //       name : 'Featured Pages'
            // },
            // czr_slide_module : {
            //       mthds : CZRSlideModuleMths,
            //       crud : true,
            //       name : 'Slider',
            //       has_mod_opt : true
            // },
            // czr_related_posts_module : {
            //       mthds : CZRRelatedPostsModMths,
            //       crud : false,
            //       multi_item : false,
            //       name : 'Related Posts',
            //       has_mod_opt : false
            // },
            // czr_text_module : {
            //       mthds : CZRTextModuleMths,
            //       crud : false,
            //       multi_item : false,
            //       name : 'Simple Text'
            // },
            // czr_text_editor_module : {
            //       mthds : CZRTextEditorModuleMths,
            //       crud : false,
            //       multi_item : false,
            //       name : 'WP Text Editor'
            // }
      });
})( wp.customize, jQuery, _ );