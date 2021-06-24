
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
            czr_widget_areas_module : {
                  mthds : CZRWidgetAreaModuleMths,
                  crud : true,
                  sektion_allowed : false,
                  name : 'Widget Areas'
            },
            czr_background : {
                  mthds : CZRBodyBgModuleMths,
                  crud : false,
                  multi_item : false,
                  name : 'Slider'
            }
      });
})( wp.customize, jQuery, _ );