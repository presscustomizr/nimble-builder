// global sekFrontLocalized, nimbleListenTo
// jQuery plugin
// Fired in ...front_fire.js
(function(w, d){
      var callbackFunc = function() {
          (function ( $, window ) {
              //defaults
              var pluginName = 'nimbleLoadVideoBg',
                  defaults = {
                        bgVideoContainerClass: 'sek-bg-video-wrapper',
                        bgYoutubeVideoContainerClass : 'sek-bg-youtube-video-wrapper',
                        bgLocalVideoContainerClass: 'sek-background-video-local',
                        bgLoadingClass: 'sek-bg-loading',
                        loop:true,
                        delayBeforeStart:null,
                        activeOnMobile:false,
                        startAt:null,
                        endAt:null,
                        lazyLoad:false
                        //enableCentering : true,
                        // onresize : true,
                        // onInit : true,//<= shall we smartload on init or wait for a custom event, typically smartload ?
                        // oncustom : [],//list of event here
                        // $containerToListen : null,//<= we might want to listen to custom event trigger to a parent container.Should be a jQuery obj
                        // imgSel : 'img',
                        // defaultCSSVal : { width : 'auto' , height : 'auto' },
                        // leftAdjust : 0,
                        // zeroLeftAdjust : 0,
                        // topAdjust : 0,
                        // zeroTopAdjust : -2,//<= top ajustement for sek-h-centrd
                        // useImgAttr:false,//uses the img height and width attributes if not visible (typically used for the customizr slider hidden images)
                        // setOpacityWhenCentered : false,//this can be used to hide the image during the time it is centered
                        // addCenteredClassWithDelay : 0,//<= a small delay can be required when we rely on the sek-v-centrd or sek-h-centrd css classes to set the opacity for example
                        // opacity : 1
                  };

              function Plugin( element, options ) {
                    var self = this;
                    this.$element   = $(element);
                    this.$window    = nb_.cachedElements.$window;
                    this._defaults  = defaults;
                    this._name      = pluginName;
                    this.options    = nb_.isObject( options ) ? options : {};

                    this.videoPlayer = false;//<= will hold the video player object

                    if ( this.options.lazyLoad ) {
                          //the scroll event gets throttled with the requestAnimationFrame
                          nb_.cachedElements.$window.scroll( nb_.throttle( function( _evt ) {
                                if ( nb_.elOrFirstVisibleParentIsInWindow( self.$element ) && !self.$element.data('sek-player-instantiated') ) {
                                      // Are we already instantiated ?
                                      if ( false === self.videoPlayer ) {
                                            self.initSetup();
                                      }
                                }
                          }, 50 ) );

                          //debounced resize event
                          nb_.cachedElements.$window.resize( nb_.debounce( function( _evt ) {
                                if ( nb_.elOrFirstVisibleParentIsInWindow( self.$element ) && !self.$element.data('sek-player-instantiated') ) {
                                      // Are we already instantiated ?
                                      if ( false === self.videoPlayer ) {
                                            self.initSetup();
                                      }
                                }
                          }, 100 ) );

                          if ( nb_.elOrFirstVisibleParentIsInWindow( self.$element ) && !self.$element.data('sek-player-instantiated') ) {
                                // Are we already instantiated ?
                                if ( false === self.videoPlayer ) {
                                      self.initSetup();
                                }
                          }
                    } else {
                          // Are we already instantiated ?
                          if ( false === self.videoPlayer ) {
                                self.initSetup();
                          }
                    }
              }

              Plugin.prototype.initSetup = function() {
                    var self = this;

                    // set options from data attributes
                    if ( ! nb_.isUndefined( self.$element.data('sek-video-bg-loop') ) ) {
                        this.options.loop = self.$element.data('sek-video-bg-loop');
                    }
                    this.options.delayBeforeStart = self.$element.data('sek-video-delay-before');
                    this.options.delayBeforeStart = Math.abs( self.options.delayBeforeStart ? parseInt( self.options.delayBeforeStart, 10 ) : 0 );

                    if ( ! nb_.isUndefined( self.$element.data('sek-video-bg-on-mobile') ) ) {
                        this.options.activeOnMobile = self.$element.data('sek-video-bg-on-mobile');
                    }
                    this.options.startAt = self.$element.data('sek-video-start-at');
                    this.options.startAt = Math.abs( self.options.startAt ? parseInt( self.options.startAt, 10 ) : 0 );

                    this.options.endAt = self.$element.data('sek-video-end-at');
                    this.options.endAt = Math.abs( self.options.endAt ? parseInt( self.options.endAt, 10 ) : 0 );

                    // make sure the video fragment is consistent
                    // endAt > startAt, ...
                    this.isFragmentedVideo = false;
                    var startAt = this.options.startAt,
                        endAt = this.options.endAt;

                    if ( startAt || endAt ) {
                        if ( startAt && endAt < 1 ) {
                              this.isFragmentedVideo = true;
                        } else if ( startAt && endAt && endAt > startAt ) {
                              this.isFragmentedVideo = true;
                        } else if ( startAt < 1 && endAt ) {
                              this.isFragmentedVideo = true;
                        }
                    }

                    this.options    = $.extend( {}, defaults, this.options );

                    var _doInit = function() {
                          // init now
                          self.init();
                          self.$element.on( 'refresh-video-dimensions', nb_.debounce( function() {
                              nb_.delay( function() {
                                  self.updatePlayerDimensions();
                              }, 300 );
                          }, 200 ) );

                          // Flag
                          self.$element.data('sek-player-instantiated', true );
                    };

                    // maybe delay init if set by user
                    nb_.delay( _doInit, self.options.delayBeforeStart * 1000 );
              };

              /*
              * @param : array of $img
              * @param : current event
              * @return : void
              * scroll event performance enhancer => avoid browser stack if too much scrolls
              */
              Plugin.prototype._better_scroll_event_handler = function( $_Elements , _evt ) {
                    var self = this;
                    if ( ! this.doingAnimation ) {
                          this.doingAnimation = true;
                          window.requestAnimationFrame(function() {
                                self._maybe_trigger_load( $_Elements , _evt );
                                self.doingAnimation = false;
                          });
                    }
              };

              //can access this.element and this.option
              //@return void
              Plugin.prototype.init = function () {
                    var self = this;

                    // Always fire when customzing
                    if ( !nb_.isCustomizing() && !this.options.activeOnMobile && nb_.isMobile() ) {
                      return;
                    }

                    // set video url prop
                    this.videoUrl = self.$element.data('sek-video-bg-src');

                    // set videoOrigin
                    if ( -1 !== this.videoUrl.indexOf('vimeo.com') ) {
                        this.videoOrigin = 'vimeo';
                        this.videoApiHelpers = this.getVideoApiHelpers( 'vimeo' );
                    } else if ( this.videoUrl.match(/^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com)/ ) ) {
                        this.videoOrigin = 'youtube';
                        this.videoApiHelpers = this.getVideoApiHelpers( 'youtube' );
                    }


                    // inject video container if not done yet
                    if ( nb_.isUndefined(self.$backgroundVideoContainer) || self.$backgroundVideoContainer.length < 1 ) {
                        self.$element.children().first().before( $('<div>', { class : self.options.bgVideoContainerClass} ) );
                        self.$backgroundVideoContainer = $( '.' + self.options.bgVideoContainerClass, self.$element );
                    }

                    if ( this.videoApiHelpers ) {
                        self.$backgroundVideoContainer.append( $('<div>', { class : self.options.bgYoutubeVideoContainerClass } ) );
                        this.videoId = this.videoApiHelpers.getVideoIDFromURL( this.videoUrl );
                        if ( this.videoId ) {
                              this.videoApiHelpers.onApiReady( function ( apiInstance ) {
                                    self.apiInstance = apiInstance;
                                    if ('youtube' === self.videoOrigin) {
                                          self.setupYtubeVideo();
                                    }
                                    if ('vimeo' === self.videoOrigin) {
                                          self.setupVimeoVideo();
                                    }
                              });
                        }
                    } else {
                        this.videoOrigin = 'local';
                        self.setupLocalVideo();
                    }

                    // update video dimension for all players on resize
                    this.$window.on( 'resize', nb_.debounce( function() { self.updatePlayerDimensions(); }, 200 ) );
              };



              Plugin.prototype.setupLocalVideo = function() {
                    var self = this;
                    var _attributes = ['autoplay', 'muted', 'playsinline'];
                    if ( self.options.loop ) {
                        _attributes.push('loop');
                    }
                    _attributes = _attributes.join(' ');

                    if ( self.$backgroundVideoContainer.find('video').length < 1 ) {
                        self.$backgroundVideoContainer.append( '<video ' + _attributes + ' class="' + self.options.bgLocalVideoContainerClass +'"></video>');
                    }
                    self.$backgroundVideoLocal = $( '.' + self.options.bgLocalVideoContainerClass, self.$element );
                    self.videoPlayer = self.$backgroundVideoLocal;

                    var startAt = self.options.startAt,
                        endAt = self.options.endAt;

                    // Fragment video if conditions are met
                    if ( this.isFragmentedVideo ) {
                          if ( startAt && endAt < 1 ) {
                                this.videoUrl += '#t=' + startAt;
                          } else if ( startAt && endAt && endAt > startAt ) {
                                this.videoUrl += '#t=' + startAt + ',' + endAt;
                          } else if ( startAt < 1 && endAt ) {
                                this.videoUrl += '#t=0,' + endAt;
                          }
                    }

                    self.$backgroundVideoContainer
                        .find( '.' + self.options.bgLocalVideoContainerClass )
                        .attr('src', this.videoUrl )
                        .one( 'canplay', function() {
                              self.updatePlayerDimensions();
                              self.$backgroundVideoContainer.css('opacity', 1);
                        });

                    // if video is fragmented and should be looped, we need to hack because when fragmented, looping won't be done automatically
                    var isVideoUrlSetupForFragment = -1 !== this.videoUrl.indexOf('#t=');
                    if ( self.options.loop && isVideoUrlSetupForFragment ) {
                          // solution found here : https://stackoverflow.com/questions/23304021/loop-video-with-media-fragments
                          self.videoPlayer.on( 'timeupdate', nb_.throttle( function () {
                                if( this.currentTime > endAt ) {
                                      this.currentTime = startAt;
                                      this.play();
                                }
                          }, 100 ) );
                    } else {
                          // If not looped, remove video after play
                          self.videoPlayer.on( 'ended', function () {
                                self.videoPlayer.hide();
                          });
                    }
              };

              Plugin.prototype.setupYtubeVideo = function() {
                    var self = this;

                    // params : { initial : true, videoPlayer : self.videoPlayer }
                    var _setupYtubeLoopWhenFragmented = function( params ) {
                        var _self_ = this;

                        // container still exists?
                        if ( !params.videoPlayer.getIframe().contentWindow )
                          return;

                        var startAt = self.options.startAt,
                            endAt = self.options.endAt;

                        if ( !self.options.loop && !params.initial ) {
                            params.videoPlayer.stopVideo();
                            return;
                        }

                        params.videoPlayer.seekTo( startAt );

                        if ( endAt ) {
                            var fragmentLength = endAt - startAt + 1;
                            setTimeout(function () {
                                _setupYtubeLoopWhenFragmented( { initial : false , videoPlayer : params.videoPlayer } );
                            }, fragmentLength * 1000 );
                        }
                    };

                    // Chrome browser may not fire the `PLAYING` state on starttime
                    // if ( window.chrome ) {
                    //       startStateCode = self.apiInstance.PlayerState.UNSTARTED;
                    // }
                    // Updated in January 2020
                    // fixes https://github.com/presscustomizr/nimble-builder/issues/574
                    var startStateCode = self.apiInstance.PlayerState.UNSTARTED;

                    self.$backgroundVideoContainer.addClass( self.options.bgLoadingClass );
                    this.videoPlayer = new self.apiInstance.Player( self.$backgroundVideoContainer.find( '.' + self.options.bgYoutubeVideoContainerClass )[0], {
                          videoId: self.videoId,
                          events: {
                                onReady: function onReady() {
                                      self.videoPlayer.mute();
                                      self.updatePlayerDimensions();
                                      if ( self.isFragmentedVideo ) {
                                          _setupYtubeLoopWhenFragmented( { initial : true, videoPlayer : self.videoPlayer } );
                                      }
                                      self.videoPlayer.playVideo();
                                },
                                onStateChange: function onStateChange(event) {
                                      switch (event.data) {
                                            case startStateCode:
                                                  self.$backgroundVideoContainer.removeClass( self.options.bgLoadingClass );
                                                  self.$backgroundVideoContainer.css('opacity', 1);
                                            break;

                                            case self.apiInstance.PlayerState.ENDED:
                                                  self.videoPlayer.seekTo( self.options.startAt );

                                                  if ( !self.options.loop ) {
                                                        self.videoPlayer.destroy();
                                                  }
                                            break;
                                      }
                                }
                          },
                          playerVars: {
                                controls: 0,
                                rel: 0
                          }
                    });
              };//setupYtubeVideo


              Plugin.prototype.setupVimeoVideo = function() {
                    var self = this;

                    var _setupVimeoFragment = function() {
                          var _self_ = this;
                          var startAt = self.options.startAt,
                              endAt = self.options.endAt;
                          // If a start time is defined, set the start time
                          if ( startAt ) {
                              self.videoPlayer.on( 'play', function ( data ) {
                                  if ( 0 === data.seconds ) {
                                      self.videoPlayer.setCurrentTime( startAt );
                                  }
                              });
                          } // If an end time is defined, handle ending the video


                          self.videoPlayer.on( 'timeupdate', function (data) {
                                if ( endAt && endAt < data.seconds ) {
                                    if ( ! self.options.loop ) {
                                        // Stop at user-defined end time if not loop
                                        self.videoPlayer.pause();
                                    } else {
                                        // Go to start time if loop
                                        self.videoPlayer.setCurrentTime( startAt );
                                    }
                                } // If start time is defined but an end time is not, go to user-defined start time at video end.
                                // Vimeo JS API has an 'ended' event, but it never fires when infinite loop is defined, so we
                                // get the video duration (returns a promise) then use duration-0.5s as end time


                                self.videoPlayer.getDuration().then( function ( duration ) {
                                    if ( startAt && !endAt && data.seconds > duration - 0.5 ) {
                                        self.videoPlayer.setCurrentTime( startAt );
                                    }
                                });
                          });
                    };

                    self.$backgroundVideoContainer.addClass( self.options.bgLoadingClass );
                    // doc : https://github.com/vimeo/player.js
                    this.videoPlayer = new self.apiInstance.Player( self.$backgroundVideoContainer, {
                          id: self.videoId,
                          width: self.$backgroundVideoContainer.outerWidth().width,
                          autoplay: true,
                          loop: self.options.loop,
                          transparent: false,
                          playsinline: false,
                          background: true,
                          muted: true,
                          controls:false//<= hide all elements in the player (play bar, sharing buttons, etc)
                    } ); // Handle user-defined start/end times

                    if ( this.isFragmentedVideo ) {
                        _setupVimeoFragment();
                    }

                    this.videoPlayer.ready().then(function () {
                          $( self.videoPlayer.element ).addClass('sek-background-vimeo-element');
                          self.updatePlayerDimensions();
                          nb_.delay( function() {
                              self.$backgroundVideoContainer.removeClass( self.options.bgLoadingClass );
                              self.$backgroundVideoContainer.css('opacity', 1);
                          }, 200 );
                    });
              };//setupVimeoVideo



              Plugin.prototype.updatePlayerDimensions = function() {
                    var self = this;
                    if ( 'local' !== this.videoOrigin && !this.videoPlayer )
                      return;

                    var $playerElement;
                    if ('youtube' === this.videoOrigin) {
                        $playerElement = $( this.videoPlayer.getIframe() );
                    } else if ('vimeo' === this.videoOrigin ) {
                        $playerElement = $( this.videoPlayer.element );
                    } else if ('local' === this.videoOrigin ) {
                        $playerElement = self.videoPlayer;
                    }

                    if ( !$playerElement )
                      return;

                    var aspectRatioSetting = '16:9';

                    if ('vimeo' === this.videoOrigin) {
                          aspectRatioSetting = $playerElement[0].width + ':' + $playerElement[0].height;
                    }

                    var containerWidth = this.$backgroundVideoContainer.outerWidth(),
                        containerHeight = this.$backgroundVideoContainer.outerHeight(),
                        aspectRatioArray = aspectRatioSetting.split(':'),
                        aspectRatio = aspectRatioArray[0] / aspectRatioArray[1],
                        ratioWidth = containerWidth / aspectRatio,
                        ratioHeight = containerHeight * aspectRatio,
                        isWidthFixed = containerWidth / containerHeight > aspectRatio;

                    $playerElement
                        .width( isWidthFixed ? containerWidth : ratioHeight )
                        .height( isWidthFixed ? ratioWidth : containerHeight );
              };



              Plugin.prototype.getVideoApiHelpers = function( videoOrigin ) {
                  var self = this;
                  return {
                        insertAPI : function() {
                              $('script:first').before( $('<script>', {
                                    src : 'youtube' === videoOrigin ? 'https://www.youtube.com/iframe_api' : 'https://player.vimeo.com/api/player.js',
                                    id  : 'sek-' + videoOrigin + '-api'
                              }));
                        },
                        //see /wp-includes/js/mediaelement/renderers/vimeo.js
                        getVideoIDFromURL : function(url) {
                              if (url === undefined || url === null) {
                                  return null;
                              }
                              if ( 'youtube' === videoOrigin ) {
                                    var videoIDParts = url.match( /^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^?&"'>]+)/ );
                                    return videoIDParts && videoIDParts[1];
                              } else {
                                    var parts = url.split('?');
                                    url = parts[0];
                                    return parseInt(url.substring(url.lastIndexOf('/') + 1), 10);
                              }
                        },
                        onApiReady: function(callback) {
                              var self = this;
                              if ( $('#' + 'sek-' + videoOrigin + '-api').length < 1 ) {
                                  this.insertAPI();
                              }
                              var _isPlayerRemoteApiLoaded = 'youtube' === videoOrigin ? ( window.YT && YT.loaded ) : window.Vimeo;
                              if ( _isPlayerRemoteApiLoaded ) {
                                  callback( 'youtube' === videoOrigin ? YT : Vimeo );
                              } else {
                                  // If not ready check again by timeout..
                                  setTimeout(function () {
                                    self.onApiReady(callback);
                                  }, 350);
                              }
                        }
                  };//defaults
              };//getVideoApiHelpers


              // prevents against multiple instantiations
              $.fn[pluginName] = function ( options ) {
                    return this.each(function () {
                        if (!$.data(this, 'plugin_' + pluginName)) {
                            $.data(this, 'plugin_' + pluginName,
                            new Plugin( this, options ));
                        }
                    });
              };

          })( jQuery, window );


          /* ------------------------------------------------------------------------- *
           *  VIDEO BACKGROUND FOR SECTIONS AND COLUMNS
          /* ------------------------------------------------------------------------- */
          // - insert bg video container
          // - inject player api script
          // - print video iframe
          // - on api ready, do stuff
          jQuery( function($){
              var _maybeInstantiatePlayers = function() {
                    $('[data-sek-video-bg-src]').each(function() {
                        if ( 'section' === $(this).data('sek-level') || 'column' === $(this).data('sek-level') ) {
                            if ( ! $(this).data('sek-player-instantiated') ) {
                                $(this).nimbleLoadVideoBg( { lazyLoad: sekFrontLocalized.video_bg_lazyload_enabled } );
                            }
                        }
                    });
              };

              // on page load
              _maybeInstantiatePlayers();

              // WHEN CUSTOMIZING
              // on various nimble events when customizing
              nb_.cachedElements.$body.on('sek-section-added sek-columns-refreshed', function( evt, params ){
                    _maybeInstantiatePlayers();
              });

              // On module refreshed inside a section, simply trigger a dimensions update
              nb_.cachedElements.$body.on('sek-modules-refreshed', function( evt, params ){
                    $('[data-sek-video-bg-src]').each(function() {
                        $(this).trigger('refresh-video-dimensions');
                    });
              });

              // when customizing the level
              nb_.cachedElements.$body.on('sek-level-refreshed', function( evt, params ){
                    // when removing a level => no params.id
                    if ( !params || !nb_.isObject( params ) || !params.id )
                      return;

                    var $levelRefreshed = $( '[data-sek-id="'+ params.id +'"][data-sek-video-bg-src]' );
                    if ( $levelRefreshed.length > 0 && !$levelRefreshed.data('sek-player-instantiated') ) {
                        $levelRefreshed.nimbleLoadVideoBg( { lazyLoad: sekFrontLocalized.video_bg_lazyload_enabled } );
                    }
              });
          });

      };/////////////// callbackFunc

      // on 'nb-app-ready', jQuery is loaded
      nb_.listenTo('nb-app-ready', function(){
          callbackFunc();
          nb_.emit('nb-videobg-parsed');
      });
}(window, document));

