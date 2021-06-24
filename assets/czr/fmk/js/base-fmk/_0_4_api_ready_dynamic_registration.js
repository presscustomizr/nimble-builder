
( function ( api, $, _ ) {
      /*****************************************************************************
      *
      *****************************************************************************/
      api.bind('ready', function() {
          // do we have dynamic registration candidates
          var dynRegistrationCandidates = serverControlParams.paramsForDynamicRegistration || [];
          if ( ! _.isObject( serverControlParams.paramsForDynamicRegistration ) ) {
                api.errorLog( 'serverControlParams.paramsForDynamicRegistration should be an array');
          }

          _.each( serverControlParams.paramsForDynamicRegistration, function( dynParams, setId ) {
                // The dynamic registration should be explicitely set
                if ( dynParams.module_registration_params && true === dynParams.module_registration_params.dynamic_registration ) {
                      if ( serverControlParams.isDevMode ) {
                            registerDynamicModuleSettingControl( dynParams );
                      } else {
                            try { registerDynamicModuleSettingControl( dynParams ); } catch( er ) {
                                  api.errorLog( er );
                            }
                      }
                }
          });

      });//api.bind('ready', function()


      // Register the relevant setting and control based on the current skope ids
      // @return the setting id
      var registerDynamicModuleSettingControl = function( args ) {
            args = _.extend( {
                  'setting_id' : '',
                  'module_type' : '',
                  'option_value'  : [],
                  // 'setting' => array(
                  //     'type' => 'option',
                  //     'default'  => array(),
                  //     'transport' => 'refresh',
                  //     'setting_class' => '',//array( 'path' => '', 'name' => '' )
                  //     'sanitize_callback' => '',
                  //     'validate_callback' => '',
                  // ),
                  'setting' : {},
                  'section' : { id : '', title : '' },
                  'control' : {},
                  //'setting_type' : 'option'

            }, args );

            // we must have not empty setting_id, module_type
            if ( _.isEmpty( args.setting_id ) || _.isEmpty( args.module_type ) ) {
                  api.errare( 'registerDynamicModuleSettingControl => args', args );
                  throw new Error( 'registerDynamicModuleSettingControl => missing params when registrating a setting');
            }

            // the option value must be an array
            if ( ! _.isArray( args.option_value ) && ! _.isObject( args.option_value ) ) {
                  throw new Error( 'registerDynamicModuleSettingControl => the module values must be an array or an object');
            }

            var settingId =  args.setting_id,
                settingArgs = args.setting;

            api.CZR_Helpers.register( {
                  what : 'setting',
                  id : settingId,
                  dirty : ! _.isEmpty( args.option_value ),
                  value : args.option_value,
                  transport : settingArgs.transport || 'refresh',
                  type : settingArgs.type || 'option',
                  track : false// <= don't add it in any registered collection @see Nimble or Contextualizer
            });

            // MAYBE REGISTER THE SECTION
            var sectionArgs = args.section;
            if ( ! _.isEmpty( sectionArgs ) ) {
                  // Check if we have a correct section
                  if ( ! _.has( sectionArgs, 'id' ) ){
                        throw new Error( 'registerDynamicModuleSettingControl => missing section id for the section of setting : ' + settingId );
                  }

                  api.CZR_Helpers.register({
                        what : 'section',
                        id : sectionArgs.id,
                        title: sectionArgs.title || sectionArgs.id,
                        panel : _.isEmpty( sectionArgs.panel ) ? '' : sectionArgs.panel,
                        priority : sectionArgs.priority || 10,
                        track : false// <= don't add it in any registered collection @see Nimble or Contextualizer => this will prevent this container to be removed when cleaning the registered
                  });
            }

            // REGISTER THE CONTROL
            var controlId = settingId,
                controlArgs = args.control,
                ctrlSectionId;

            // Do we have a section ?
            if ( ! _.isEmpty( args.section ) ) {
                  ctrlSectionId = args.section.id;
            } else {
                  ctrlSectionId = controlArgs.section;
            }

            if ( _.isEmpty( ctrlSectionId ) ) {
                  api.errare( 'registerDynamicModuleSettingControl => missing section id for the control', args );
                  throw new Error( 'registerDynamicModuleSettingControl => missing section id for the section of setting : ' + settingId );
            }
            api.CZR_Helpers.register({
                  what : 'control',
                  id : controlId,
                  label : controlArgs.label || controlId,
                  type : 'czr_module',
                  module_type : args.module_type,//'czr_background',
                  section : ctrlSectionId,//'contx_body_bg',
                  priority : controlArgs.priority || 10,
                  settings : { default : settingId },
                  track : false// <= don't add it in any registered collection @see Nimble or Contextualizer => this will prevent this container to be removed when cleaning the registered
            });

            // if the currently expanded section is the one of the dynamic control
            // Awake the module => fire ready
            if ( api.section.has( ctrlSectionId ) && api.section( ctrlSectionId ).expanded() ) {
                  api.control( controlId ).trigger( 'set-module-ready' );
            }

            return settingId;
      };//registerDynamicModuleSettingControl
})( wp.customize , jQuery, _);