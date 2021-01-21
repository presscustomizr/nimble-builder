//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
/* ------------------------------------------------------------------------- *
 *  CONTENT TYPE SWITCHER
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
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
            sek_content_type_switcher_module : {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_content_type_switcher_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_content_type_switcher_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );





/* ------------------------------------------------------------------------- *
 *  MODULE PICKER MODULE
/* ------------------------------------------------------------------------- */
//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
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
            sek_module_picker_module : {
                  //mthds : ModulePickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_module_picker_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : true,
                  defaultItemModel :  _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_module_picker_module' )
                  )
            },
      });
})( wp.customize , jQuery, _ );



/* ------------------------------------------------------------------------- *
 *  SECTION PICKER MODULES
/* ------------------------------------------------------------------------- */
( function ( api, $, _ ) {
      //provides a description of each module
      //=> will determine :
      //1) how to initialize the module model. If not crud, then the initial item(s) model shall be provided
      //2) which js template(s) to use : if crud, the module template shall include the add new and pre-item elements.
      //   , if crud, the item shall be removable
      //3) how to render : if multi item, the item content is rendered when user click on edit button.
      //    If not multi item, the single item content is rendered as soon as the item wrapper is rendered.
      //4) some DOM behaviour. For example, a multi item shall be sortable.
      api.czrModuleMap = api.czrModuleMap || {};
      // var section_modules = [
      //       'sek_intro_sec_picker_module',
      //       'sek_features_sec_picker_module',
      //       'sek_contact_sec_picker_module',
      //       'sek_column_layouts_sec_picker_module',
      //       'sek_header_sec_picker_module',
      //       'sek_footer_sec_picker_module'
      // ];

      var section_modules = sektionsLocalizedData.presetSectionsModules;
      if ( ! _.isArray( section_modules ) || _.isEmpty( section_modules ) ) {
            api.errare( 'api.czrModuleMap => error when adding section modules');
            return;
      }

      _.each( section_modules, function( module_type ) {
            api.czrModuleMap[ module_type ] = {
                  //mthds : SectionPickerModuleConstructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( module_type, 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : _.extend(
                        { id : '', title : '' },
                        api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( module_type )
                  )
            };
      });
})( wp.customize , jQuery, _ );






/* ------------------------------------------------------------------------- *
 *  MY SECTIONS MODULE
/* ------------------------------------------------------------------------- */
//global sektionsLocalizedData, serverControlParams
//extends api.CZRDynModule
( function ( api, $, _ ) {
      var Constructor = {
            initialize: function( id, options ) {
                  var module = this;

                  // EXTEND THE DEFAULT CONSTRUCTORS FOR INPUT
                  module.inputConstructor = api.CZRInput.extend({
                        initialize : function( name, options ) {
                              var input = this;
                              api.CZRInput.prototype.initialize.call( input, name, options );
                              input.isReady.then( function() {
                                    input.renderUserSavedSections();
                                    input.attachDomEvents();
                              });
                        },

                        // Ajax fetch the user section collection
                        // or return the already cached collection
                        getUserSavedSections : function() {
                              var _dfd_ = $.Deferred();
                              if ( !_.isEmpty( api.czr_sektions.userSavedSections ) ) {
                                    _dfd_.resolve( api.czr_sektions.userSavedSections );
                              } else {
                                    api.czr_sektions.getSavedSectionCollection().done( function( sec_collection ) {
                                           _dfd_.resolve( sec_collection );
                                    });
                              }
                              return _dfd_.promise();
                        },

                        renderUserSavedSections : function() {
                              var input = this,
                                  html = '',
                                  $wrapper = input.container.find('.sek-content-type-wrapper'),
                                  creation_date = '';
                                  // https://stackoverflow.com/questions/3552461/how-to-format-a-javascript-date
                                  // formatDate = function(date) {
                                  //     var monthNames = [
                                  //         "January", "February", "March",
                                  //         "April", "May", "June", "July",
                                  //         "August", "September", "October",
                                  //         "November", "December"
                                  //     ];

                                  //     var day = date.getDate(),
                                  //         monthIndex = date.getMonth(),
                                  //         year = date.getFullYear(),
                                  //         hours = date.getHours(),
                                  //         minutes = date.getMinutes(),
                                  //         seconds = date.getSeconds();

                                  //     return [
                                  //           day,
                                  //           monthNames[monthIndex],
                                  //           year
                                  //           //[hours,minutes,seconds].join(':')
                                  //     ].join(' ');
                                  // };

                              var _refreshUserSectionView = function( sec_collection ) {
                                    // clean
                                    $wrapper.find('.sek-user-section-wrapper').remove();
                                    
                                    // Write
                                    if ( _.isEmpty( sec_collection ) ) {
                                        var _placeholdImgUrl = [ sektionsLocalizedData.baseUrl , '/assets/admin/img/save_section_notice.png',  '?ver=' , sektionsLocalizedData.nimbleVersion ].join(''),
                                          doc_url = 'https://docs.presscustomizr.com/article/417-how-to-save-and-reuse-sections-with-nimble-builder';
                                        html = [
                                              '<div class="sek-user-section-wrapper">',
                                                '<img src="'+ _placeholdImgUrl +'" />',
                                                '<br/><a href="'+ doc_url +'" target="_blank" rel="noreferrer nofollow">'+ doc_url +'</a>',
                                              '</div>'
                                        ].join('');
                                        $wrapper.append( html );
                                        input.module.container.find('.czr-item-content .customize-control-title').html(sektionsLocalizedData.i18n['You did not save any section yet.']);
                                    } else {
                                        var _thumbUrl = [ sektionsLocalizedData.baseUrl , '/assets/admin/img/nb_sec_pholder.png',  '?ver=' , sektionsLocalizedData.nimbleVersion ].join(''),
                                        styleAttr = 'background: url(' + _thumbUrl  + ') 50% 50% / cover no-repeat;';
                                        _.each( sec_collection, function( secData, secKey ) {
                                              // try { creation_date = formatDate( new Date( secData.creation_date.replace( /-/g, '/' ) ) ); } catch( er ) {
                                              //       api.errare( '::renderUserSavedSections => formatDate => error', er );
                                              // }
                                              if( !_.isEmpty( secData.description ) ) {
                                                  _titleAttr = [ secData.title, secData.last_modified_date, secData.description ].join(' | ');
                                              } else {
                                                  _titleAttr = [ secData.title, secData.last_modified_date ].join(' | ');
                                              }
                                              html = [
                                                    '<div class="sek-user-section-wrapper">',
                                                      '<div draggable="true" data-sek-is-user-section="true" data-sek-section-type="content" data-sek-content-type="preset_section" data-sek-content-id="' + secKey +'" style="" title="' + secData.title + '">',
                                                        '<div class="sek-sec-thumb" style="'+ styleAttr +'"></div>',//<img src="'+ _thumbUrl +'"/>
                                                        '<div class="sek-overlay"></div>',
                                                        '<div class="sek-sec-info" title="'+ _titleAttr +'">',
                                                          '<h3 class="sec-title">' + secData.title + '</h3>',
                                                          '<p class="sec-date"><i>' + [ sektionsLocalizedData.i18n['Last modified'], ' : ', secData.last_modified_date ].join(' ') + '</i></p>',
                                                          '<p class="sec-desc">' + secData.description + '</p>',
                                                          '<i class="material-icons edit-user-sec" title="'+ sektionsLocalizedData.i18n['Edit this template'] +'">edit</i>',
                                                          '<i class="material-icons remove-user-sec" title="'+ sektionsLocalizedData.i18n['Remove this template'] +'">delete_forever</i>',
                                                          //'<div class="sek-overlay"></div>',
                                                          //'<div class="sek-saved-section-description">' + secData.description + '</div>',
                                                          //! _.isEmpty( creation_date ) ? ( '<div class="sek-saved-section-date"><i class="far fa-calendar-alt"></i> Created : ' + creation_date + '</div>' ) : '',
                                                        '</div>',
                                                      '</div>',
                                                    '</div>'
                                              ].join('');
                                              $wrapper.append( html );
                                        });//_.each
                                    }
                                    // Remove the loader previously added
                                    $wrapper.find('.czr-css-loader').remove();

                                    // Make section draggable now
                                    api.czr_sektions.trigger( 'sek-refresh-dragzones', { type : 'preset_section', input_container : input.container } );
                              };//_refreshUserSectionView

                              // on input instantiation, render the collection
                              // print a loader
                              $wrapper.append('<div class="czr-css-loader czr-mr-loader" style="display:block"><div></div><div></div><div></div></div>');
                              input.getUserSavedSections().done( function( sec_collection ) {
                                    _refreshUserSectionView( sec_collection );
                              });

                              // when the collection is modified : save, update, remove actions => NB refreshes the collection
                              api.czr_sektions.allSavedSections.bind( function( sec_collection ) {
                                    _refreshUserSectionView( sec_collection );
                              });
                        },//renderUserSavedSections

                        // with delegation
                        attachDomEvents : function() {
                              // Attach events
                              this.container
                                    .on('click', '.sek-sec-info .remove-user-sec', function(evt) {
                                          evt.preventDefault();
                                          var self = api.czr_sektions;
                                          self.saveSectionDialogVisible(false);
                                          // Close section dialog if it was open 
                                          //self.saveSectionDialogMode( 'hidden' );
                                          var _focusOnRemoveCandidate = function( mode ) {
                                                self.saveSectionDialogMode( 'remove' );
                                                // self unbind
                                                self.saveSectionDialogMode.unbind( _focusOnRemoveCandidate );
                                          };
                                          self.userSectionToRemove = $(this).closest("[data-sek-content-id]").data('sek-content-id');
                                          self.saveSectionDialogMode.bind( _focusOnRemoveCandidate );
                                          self.saveSectionDialogVisible(true);
                                    })
                                    .on('click', '.sek-sec-info .edit-user-sec', function(evt) {
                                          evt.preventDefault();
                                          var self = api.czr_sektions;
                                          self.saveSectionDialogVisible(false);
                                          //self.saveSectionDialogMode( 'hidden' );
                                          var _focusOnEditCandidate = function( mode ) {
                                                self.saveSectionDialogMode( 'edit' );
                                                // self unbind
                                                self.saveSectionDialogMode.unbind( _focusOnEditCandidate );
                                          };
                                          self.userSectionToEdit = $(this).closest("[data-sek-content-id]").data('sek-content-id');
                                          self.saveSectionDialogMode.bind( _focusOnEditCandidate );
                                          self.saveSectionDialogVisible(true);
                                    });
                        }
                  });//module.inputConstructor

                  // run the parent initialize
                  // Note : must be always invoked always after the input / item class extension
                  // Otherwise the constructor might be extended too early and not taken into account. @see https://github.com/presscustomizr/nimble-builder/issues/37
                  api.CZRDynModule.prototype.initialize.call( module, id, options );

                  // module.embedded.then( function() {
                  //       console.log('MODULE READY=> lets dance',  module.container,  module.container.find('.sek-ctrl-accordion-title') );
                  // });
            },//initialize
      };


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
            sek_my_sections_sec_picker_module : {
                  mthds : Constructor,
                  crud : false,
                  name : api.czr_sektions.getRegisteredModuleProperty( 'sek_my_sections_sec_picker_module', 'name' ),
                  has_mod_opt : false,
                  ready_on_section_expanded : false,
                  ready_on_control_event : 'sek-accordion-expanded',// triggered in ::scheduleModuleAccordion()
                  defaultItemModel : api.czr_sektions.getDefaultItemModelFromRegisteredModuleData( 'sek_my_sections_sec_picker_module' )
            },
      });
})( wp.customize , jQuery, _ );
