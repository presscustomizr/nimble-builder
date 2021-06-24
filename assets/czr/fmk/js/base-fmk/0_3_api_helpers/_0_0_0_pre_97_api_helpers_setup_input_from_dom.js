
(function (api, $, _) {
api.CZR_Helpers = api.CZR_Helpers || {};
//////////////////////////////////////////////////
/// ACTIONS AND DOM LISTENERS
//////////////////////////////////////////////////
//adds action to an existing event map
//@event map = [ {event1}, {event2}, ... ]
//@new_event = {  trigger   : event name , actions   : [ 'cb1', 'cb2', ... ] }
api.CZR_Helpers = $.extend( api.CZR_Helpers, {
      //This method is now statically accessed by item and modopt instances because it does the same job for both.
      //=> It instantiates the inputs based on what it finds in the DOM ( item or mod opt js templates )
      //
      //Fired on 'contentRendered' for items and on user click for module options (mod opt)
      //creates the inputs based on the rendered parent item or mod option
      //inputParentInst can be an item instance or a module option instance
      setupInputCollectionFromDOM : function() {
            var inputParentInst = this;//<= because fired with .call( inputParentInst )
            if ( ! _.isFunction( inputParentInst ) ) {
                  throw new Error( 'setupInputCollectionFromDOM => inputParentInst is not valid.' );
            }
            var module = inputParentInst.module,
                is_mod_opt = _.has( inputParentInst() , 'is_mod_opt' );

            //bail if already done
            //_.has( inputParentInst, 'czr_Input')
            if ( ! _.isEmpty( inputParentInst.inputCollection() ) )
              return;

            //INPUTS => Setup as soon as the view content is rendered
            //the inputParentInst is a collection of inputs, each one has its own view module.
            inputParentInst.czr_Input = inputParentInst.czr_Input || new api.Values();

            //IS THE PARENT AN ITEM OR A MODULE OPTION ?
            //those default constructors (declared in the module init ) can be overridden by extended item or mod opt constructors inside the modules
            inputParentInst.inputConstructor = is_mod_opt ? module.inputModOptConstructor : module.inputConstructor;

            var _defaultInputParentModel = is_mod_opt ? inputParentInst.defaultModOptModel : inputParentInst.defaultItemModel;

            if ( _.isEmpty( _defaultInputParentModel ) || _.isUndefined( _defaultInputParentModel ) ) {
                  throw new Error( 'setupInputCollectionFromDOM => No default model found in item or mod opt ' + inputParentInst.id + '.' );
            }

            //prepare and sets the inputParentInst value on api ready
            //=> triggers the module rendering + DOM LISTENERS
            var inputParentInst_model = $.extend( true, {}, inputParentInst() );

            if ( ! _.isObject( inputParentInst_model ) ) {
                  inputParentInst_model = _defaultInputParentModel;
            } else {
                  inputParentInst_model = $.extend( _defaultInputParentModel, inputParentInst_model );
            }

            var dom_inputParentInst_model = {};

            if ( $( '.' + module.control.css_attr.sub_set_wrapper, inputParentInst.container).length < 1 ) {
                  api.errare( 'setupInputCollectionFromDOM => no input elements found in the DOM' );
            }

            //creates the inputs based on the rendered item or mod opt
            $( '.' + module.control.css_attr.sub_set_wrapper, inputParentInst.container).each( function( _index ) {
                  var _id = $(this).find('[data-czrtype]').attr( 'data-czrtype' ),
                      _value = _.has( inputParentInst_model, _id ) ? inputParentInst_model[ _id ] : '';

                  //console.log('/// ID /// => ', _id );
                  //skip if no valid input data-czrtype is found in this node
                  if ( _.isUndefined( _id ) || _.isEmpty( _id ) ) {
                        api.errare( 'setupInputCollectionFromDOM => missing data-czrtype id for input type ' + $(this).data( 'input-type' ) + ' in module ' + module.id + '. Check that the server input template is properly declared.' );
                        return;
                  }
                  //check if this property exists in the current inputParentInst model
                  if ( ! _.has( inputParentInst_model, _id ) ) {
                        throw new Error('setupInputCollectionFromDOM => The item or mod opt property : ' + _id + ' has been found in the DOM but not in the item or mod opt model : '+ inputParentInst.id + '. The input can not be instantiated.');
                  }

                  //Do we have a specific set of options defined in the parent module for this inputConstructor ?
                  var _inputType      = $(this).data( 'input-type' ),
                      _inputTransport = $(this).data( 'transport' ) || 'inherit',//<= if no specific transport ( refresh or postMessage ) has been defined in the template, inherits the control transport
                      _inputOptions   = _.has( module.inputOptions, _inputType ) ? module.inputOptions[ _inputType ] : {},
                      _inputArgs = {
                            id            : _id,
                            type          : _inputType,
                            transport     : _inputTransport,
                            input_value   : _value,
                            input_options : _inputOptions,//<= a module can define a specific set of option
                            container     : $(this),
                            input_parent  : inputParentInst,
                            is_mod_opt    : is_mod_opt,
                            module        : module
                      };

                  // ALLOW PLUGINS TO FILTER THE INPUT ARGS BEFORE INSTANTIATION
                  api.trigger( 'input-args-before-instantiation', _inputArgs );

                  // INSTANTIATE THE INPUT
                  inputParentInst.czr_Input.add( _id, new inputParentInst.inputConstructor( _id, _inputArgs ) );

                  // FIRE THE INPUT
                  // fires ready once the input Value() instance is initialized
                  inputParentInst.czr_Input( _id ).ready();

                  // POPULATES THE PARENT INPUT COLLECTION
                  dom_inputParentInst_model[ _id ] = _value;
                  // shall we trigger a specific event when the input collection from DOM has been populated ?
            });//each

            //stores the collection
            inputParentInst.inputCollection( dom_inputParentInst_model );

            //chain
            return inputParentInst;
      }
});//$.extend
  // $( window ).on( 'message', function( e, o) {
  //   api.consoleLog('WHAT ARE WE LISTENING TO?', e, o );
  // });
})( wp.customize , jQuery, _);