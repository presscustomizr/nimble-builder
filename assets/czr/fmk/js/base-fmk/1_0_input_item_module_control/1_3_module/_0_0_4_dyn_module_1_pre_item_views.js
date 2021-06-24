//MULTI CONTROL CLASS
//extends api.CZRBaseControl
//
//Setup the collection of items
//renders the module view
//Listen to items collection changes and update the module setting

var CZRDynModuleMths = CZRDynModuleMths || {};
( function ( api, $, _ ) {
$.extend( CZRDynModuleMths, {
      //////////////////////////////////////////////////
      /// PRE ADD MODEL DIALOG AND VIEW
      //////////////////////////////////////////////////
      renderPreItemView : function( obj ) {
              var module = this,
                  dfd = $.Deferred(),
                  pre_add_template;

              //is this view already rendered ?
              if ( _.isObject( module.preItemsWrapper ) && 0 < module.preItemsWrapper.length ) { //was ! _.isEmpty( module.czr_preItem('item_content')() ) )
                    return dfd.resolve( module.preItemsWrapper ).promise();
              }

              var appendAndResolve = function( _tmpl_ ){
                    //console.log('pre_add_template', _tmpl_ );
                    //do we have an html template and a module container?
                    if ( _.isEmpty( _tmpl_ ) || ! module.container ) {
                          dfd.reject( 'renderPreItemView => Missing html template for module : '+ module.id );
                    }

                    var $_pre_add_el = $('.' + module.control.css_attr.pre_add_item_content, module.container );

                    $_pre_add_el.prepend( $('<div>', { class : 'pre-item-wrapper'} ) );
                    $_pre_add_el.find('.pre-item-wrapper').append( _tmpl_ );

                    //say it
                    dfd.resolve( $_pre_add_el.find('.pre-item-wrapper') ).promise();
              };

              // do we have view template script ?
              // if yes, let's use it <= Old way
              // Otherwise let's fetch the html template from the server
              if ( ! _.isEmpty( module.itemPreAddEl ) ) {
                    if ( 1 > $( '#tmpl-' + module.itemPreAddEl ).length ) {
                          dfd.reject( 'renderPreItemView => Missing itemPreAddEl or template in module '+ module.id );
                    }
                    // parse the html
                    appendAndResolve( wp.template( module.itemPreAddEl )() );
              } else {
                    api.CZR_Helpers.getModuleTmpl( {
                          tmpl : 'pre-item',
                          module_type: module.module_type,
                          module_id : module.id,
                          control_id : module.control.id
                    } ).done( function( _serverTmpl_ ) {
                          //console.log( 'success response =>', _serverTmpl_);
                          appendAndResolve( api.CZR_Helpers.parseTemplate( _serverTmpl_ )() );
                    }).fail( function( _r_ ) {
                          //console.log( 'fail response =>', _r_);
                          dfd.reject( [ 'renderPreItemView for module : ', module.id , _r_ ].join(' ') );
                    });
              }
              return dfd.promise();
      },

      //@return $ el of the pre Item view
      _getPreItemView : function() {
              var module = this;
              return $('.' +  module.control.css_attr.pre_add_item_content, module.container );
      },


      //callback of module.preItemExpanded
      //@_is_expanded = boolean.
      _togglePreItemViewExpansion : function( _is_expanded ) {
              var module = this,
                $_pre_add_el = $( '.' +  module.control.css_attr.pre_add_item_content, module.container );

              //toggle it
              $_pre_add_el.slideToggle( {
                    duration : 200,
                    done : function() {
                          var $_btn = $( '.' +  module.control.css_attr.open_pre_add_btn, module.container );

                          $(this).toggleClass('open' , _is_expanded );
                          //switch icons
                          if ( _is_expanded )
                            $_btn.find('.fas').removeClass('fa-plus-square').addClass('fa-minus-square');
                          else
                            $_btn.find('.fas').removeClass('fa-minus-square').addClass('fa-plus-square');

                          //set the active class to the btn
                          $_btn.toggleClass( 'active', _is_expanded );

                          //set the adding_new class to the module container wrapper
                          $( module.container ).toggleClass(  module.control.css_attr.adding_new, _is_expanded );
                          //make sure it's fully visible
                          module._adjustScrollExpandedBlock( $(this), 120 );
                  }//done
              } );
      },


      toggleSuccessMessage : function( status ) {
              var module = this,
                  _message = module.itemAddedMessage,
                  $_pre_add_wrapper = $('.' + module.control.css_attr.pre_add_wrapper, module.container );
                  $_success_wrapper = $('.' + module.control.css_attr.pre_add_success, module.container );

              if ( 'on' == status ) {
                  //write message
                  $_success_wrapper.find('p').text(_message);

                  //set various properties
                  $_success_wrapper.css('z-index', 1000001 )
                    .css('height', $_pre_add_wrapper.height() + 'px' )
                    .css('line-height', $_pre_add_wrapper.height() + 'px');
              } else {
                  $_success_wrapper.attr('style','');
              }
              module.container.toggleClass('czr-model-added', 'on' == status );
              return this;
      }
});//$.extend//CZRBaseControlMths
})( wp.customize , jQuery, _ );