
( function ( api, $, _ ) {
      // BASE
      // BASE : Extends some constructors with the events manager
      $.extend( CZRBaseControlMths, api.Events );
      $.extend( api.Control.prototype, api.Events );//ensures that the default WP control constructor is extended as well
      $.extend( CZRModuleMths, api.Events );
      $.extend( CZRItemMths, api.Events );
      $.extend( CZRModOptMths, api.Events );

      // BASE : Add the DOM helpers (addAction, ...) to the Control Base Class + Input Base Class
      $.extend( CZRBaseControlMths, api.CZR_Helpers );
      $.extend( CZRInputMths, api.CZR_Helpers );
      $.extend( CZRModuleMths, api.CZR_Helpers );

      // BASE INPUTS => used as constructor when creating the collection of inputs
      api.CZRInput                  = api.Value.extend( CZRInputMths );
      // Declare all available input type as a map
      api.czrInputMap = api.czrInputMap || {};

      // input_type => callback fn to fire in the Input constructor on initialize
      // the callback can receive specific params define in each module constructor
      // For example, a content picker can be given params to display only taxonomies
      // the default input_event_map can also be overriden in this callback
      $.extend( api.czrInputMap, {
            text      : '',
            textarea  : '',
            check     : 'setupIcheck',
            checkbox     : 'setupIcheck',
            //gutencheck : 'setupGutenCheck', // DEPRECATED since april 2nd 2019
            nimblecheck : '',//setupNimbleCheck',
            select    : 'setupSelect',
            radio     : 'setupRadio',
            number    : 'setupStepper',
            upload    : 'setupImageUploaderSaveAsId',
            upload_url : 'setupImageUploaderSaveAsUrl',
            color     : 'setupColorPicker',
            wp_color_alpha : 'setupColorPickerAlpha',
            wp_color  : 'setupWPColorPicker',//not used for the moment
            content_picker : 'setupContentPicker',
            password : '',
            range : 'setupSimpleRange',
            range_slider : 'setupRangeSlider',
            hidden : '',
            h_alignment : 'setupHAlignement',
            h_text_alignment : 'setupHAlignement',
            inactive : '' // introduced sept 2020 for https://github.com/presscustomizr/nimble-builder-pro/issues/67
      });



      // BASE ITEMS => used as constructor when creating the collection of models
      api.CZRItem                   = api.Value.extend( CZRItemMths );

      // BASE MODULE OPTIONS => used as constructor when creating module options
      api.CZRModOpt                 = api.Value.extend( CZRModOptMths );

      // BASE MODULES => used as constructor when creating the collection of modules
      api.CZRModule                 = api.Value.extend( CZRModuleMths );
      api.CZRDynModule              = api.CZRModule.extend( CZRDynModuleMths );

      // BASE CONTROLS
      api.CZRBaseControl            = api.Control.extend( CZRBaseControlMths );
      api.CZRBaseModuleControl      = api.CZRBaseControl.extend( CZRBaseModuleControlMths );

      $.extend( api.controlConstructor, { czr_module : api.CZRBaseModuleControl });
})( wp.customize, jQuery, _ );