// global sekFrontLocalized, nimbleFront
// jQuery plugin
// Fired in ...front_fire.js
(function ( $, window ) {
      //defaults
      var pluginName = 'nimbleLoadVideoBg',
          defaults = {
                backgroundVideoContainer: '.sek-background-video-container',
                backgroundVideoEmbed: '.sek-background-video-embed',
                loop:true,
                activeOnMobile:false,
                startAt:null,
                endAt:null
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
            this.$window    = nimbleFront.cachedElements.$window;
            this._defaults  = defaults;
            this._name      = pluginName;
            this.options    = options || {};

            // set options from data attributes
            if ( ! _utils_.isUndefined( self.$element.data('sek-video-bg-loop') ) ) {
                this.options.loop = self.$element.data('sek-video-bg-loop');
            }
            if ( ! _utils_.isUndefined( self.$element.data('sek-video-bg-on-mobile') ) ) {
                this.options.activeOnMobile = self.$element.data('sek-video-bg-on-mobile');
            }
            if ( ! _utils_.isUndefined( self.$element.data('sek-video-start-at') ) ) {
                this.options.startAt = self.$element.data('sek-video-start-at');
            }
            if ( ! _utils_.isUndefined( self.$element.data('sek-video-end-at') ) ) {
                this.options.endAt = self.$element.data('sek-video-end-at');
            }

            this.options    = $.extend( {}, defaults, this.options);
            console.log('ALORS CES OPTIONS ', this.options , this.$element.data('sek-video-end-at'));
            this.init();
      }

      //can access this.element and this.option
      //@return void
      Plugin.prototype.init = function () {
            var self = this;
            if ( !this.options.activeOnMobile && nimbleFront.isMobile() ) {
              return;
            }

            // set video url prop
            this.videoUrl = self.$element.data('sek-video-bg-src');

            // set videoType
            if ( -1 !== this.videoUrl.indexOf('vimeo.com') ) {
                this.videoType = 'vimeo';
                this.apiProvider = this.getApiProvider( 'vimeo' );
            } else if ( this.videoUrl.match(/^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com)/ ) ) {
                this.videoType = 'youtube';
                this.apiProvider = this.getApiProvider( 'youtube' );
            }


            // inject video container if not done yet
            if ( _utils_.isUndefined(self.$backgroundVideoContainer) || self.$backgroundVideoContainer.length < 1 ) {
                self.$element.children().first().before( $('<div>', { class : 'sek-background-video-container'} ) );
                self.$backgroundVideoContainer = $( self.options.backgroundVideoContainer, self.$element );
            }

            if ( this.apiProvider ) {
                self.$backgroundVideoContainer.append( $('<div>', { class : 'sek-background-video-embed' } ) );
                this.videoId = this.apiProvider.getVideoIDFromURL( this.videoUrl );
                this.apiProvider.onApiReady(function (apiObject) {
                      self.apiObject = apiObject;
                      if ('youtube' === self.videoType) {
                            self.prepareYTVideo();
                      }
                      if ('vimeo' === self.videoType) {
                            self.prepareVimeoVideo();
                      }
                });
            } else {
                this.videoType = 'hosted';
                var _attributes = ['autoplay', 'muted', 'playsinline'];
                if ( self.options.loop ) {
                    _attributes.push('loop');
                }
                _attributes = _attributes.join(' ');
                self.$backgroundVideoContainer.append( '<video ' + _attributes + ' class="sek-background-video-hosted sek-html5-video"></video>');
                self.$backgroundVideoHosted = $( '.sek-background-video-hosted', self.$element );

                var startTime = self.options.startAt ? parseInt( self.options.startAt, 10 ) : 0,
                    endTime = self.options.endAt ? parseInt( self.options.endAt, 10 ) : 0;

                // Fragment video if conditions are met
                if ( startTime || endTime ) {
                      if ( startTime && endTime < 1 ) {
                            this.videoUrl += '#t=' + startTime;
                      } else if ( startTime && endTime && endTime > startTime ) {
                            this.videoUrl += '#t=' + startTime + ',' + endTime;
                      } else if ( startTime < 1 && endTime ) {
                            this.videoUrl += '#t=0,' + endTime;
                      }
                }

                self.$backgroundVideoContainer.find('.sek-background-video-hosted').attr('src', this.videoUrl ).one( 'canplay', this.changeVideoSize.bind(this) );

                // if video is fragmented and should be looped, we need to hack because when fragmented, looping won't be done automatically
                var isVideoFragmented = -1 !== this.videoUrl.indexOf('#t=');
                if ( self.options.loop && isVideoFragmented ) {
                      // solution found here : https://stackoverflow.com/questions/23304021/loop-video-with-media-fragments
                      self.$backgroundVideoHosted.on( 'timeupdate', _utils_.throttle( function () {
                            if( this.currentTime > endTime ) {
                                  this.currentTime = startTime;
                                  this.play();
                            }
                      }, 100 ) );
                } else {
                      // If not looped, remove video after play
                      self.$backgroundVideoHosted.on( 'ended', function () {
                            self.$backgroundVideoHosted.hide();
                      });
                }
            }

            this.$window.on('resize', _.debounce( function() { self.changeVideoSize(); }, 200 ) );
      };

      Plugin.prototype.prepareYTVideo = function() {
          var self = this;

          var $backgroundVideoContainer = self.$backgroundVideoContainer;
              //elementSettings = this.getElementSettings();
          //var startStateCode = self.apiObject.PlayerState.PLAYING;

          // Chrome doesn't trigger the `PLAYING` state at start time
          if (window.chrome) {
                startStateCode = self.apiObject.PlayerState.UNSTARTED;
          }

          $backgroundVideoContainer.addClass('sek-bg-loading');
          this.player = new self.apiObject.Player( $backgroundVideoContainer.find( '.sek-background-video-embed')[0], {
                videoId: self.videoId,
                events: {
                      onReady: function onReady() {
                            self.player.mute();
                            self.changeVideoSize();
                            //self.startVideoLoop(true);
                            self.player.playVideo();
                      },
                      onStateChange: function onStateChange(event) {
                            switch (event.data) {
                                  case startStateCode:
                                        $backgroundVideoContainer.removeClass('sek-bg-loading');
                                  break;

                                  case self.apiObject.PlayerState.ENDED:
                                        self.player.seekTo(0);

                                        if ( !self.options.loop ) {
                                              self.player.destroy();
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
      };//prepareYTVideo


      Plugin.prototype.prepareVimeoVideo = function() {
            var self = this;

            var //elementSettings = this.getElementSettings(),
                //startTime = elementSettings.background_video_start ? elementSettings.background_video_start : 0,
                videoSize = self.$backgroundVideoContainer.outerWidth(),
                vimeoOptions = {
                      id: self.videoId,
                      width: videoSize.width,
                      autoplay: true,
                      loop: self.options.loop,
                      transparent: false,
                      playsinline: false,
                      background: true,
                      muted: true
                };
            this.player = new self.apiObject.Player( self.$backgroundVideoContainer, vimeoOptions ); // Handle user-defined start/end times

            //this.handleVimeoStartEndTimes(elementSettings, startTime);
            this.player.ready().then(function () {
                  $(self.player.element).addClass('sek-background-video-embed');
                  self.changeVideoSize();
            });
      };//prepareVimeoVideo



      Plugin.prototype.changeVideoSize = function() {
            var self = this;
            if ( 'hosted' !== this.videoType && !this.player )
              return;

            var $video;
            if ('youtube' === this.videoType) {
                $video = $( this.player.getIframe() );
            } else if ('vimeo' === this.videoType ) {
                $video = $( this.player.element );
            } else if ('hosted' === this.videoType ) {
                $video = self.$backgroundVideoHosted;
            }

            if ( !$video )
              return;

            var size = this.calcVideosSize( $video );
            $video.width( size.width ).height( size.height );
      };


      Plugin.prototype.calcVideosSize = function($video) {
            var aspectRatioSetting = '16:9';

            if ('vimeo' === this.videoType) {
                  aspectRatioSetting = $video[0].width + ':' + $video[0].height;
            }

            var containerWidth = this.$backgroundVideoContainer.outerWidth(),
                containerHeight = this.$backgroundVideoContainer.outerHeight(),
                aspectRatioArray = aspectRatioSetting.split(':'),
                aspectRatio = aspectRatioArray[0] / aspectRatioArray[1],
                ratioWidth = containerWidth / aspectRatio,
                ratioHeight = containerHeight * aspectRatio,
                isWidthFixed = containerWidth / containerHeight > aspectRatio;

            return {
                  width: isWidthFixed ? containerWidth : ratioHeight,
                  height: isWidthFixed ? ratioWidth : containerHeight
            };
      };



      Plugin.prototype.getApiProvider = function( provider ) {
          var self = this;
          var _defaults = {
                insertAPI : function() {
                      $('script:first').before($('<script>', {
                            src: this.getApiURL(),
                            id:'sek-' + provider + '-api'
                      }));
                },
                getVideoIDFromURL : function(url) {
                      var videoIDParts = url.match(this.getURLRegex());
                      return videoIDParts && videoIDParts[1];
                },
                onApiReady: function(callback) {
                      var self = this;

                      if ($('#' + 'sek-' + provider + '-api').length < 1 ) {
                        this.insertAPI();
                      }

                      if (this.isApiLoaded()) {
                        callback(this.getApiObject());
                      } else {
                        // If not ready check again by timeout..
                        setTimeout(function () {
                          self.onApiReady(callback);
                        }, 350);
                      }
                }
          };//defaults

          var _youtubeLoader = {
                getApiURL : function() {
                      return 'https://www.youtube.com/iframe_api';
                },
                getURLRegex : function() {
                      return /^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^?&"'>]+)/;
                },
                isApiLoaded : function() {
                      return window.YT && YT.loaded;
                },
                getApiObject : function() {
                      return YT;
                }
          };

          var _vimeoLoader = {
                getApiURL : function() {
                      return 'https://player.vimeo.com/api/player.js';
                },
                getURLRegex : function() {
                      return /^(?:https?:\/\/)?(?:www|player\.)?(?:vimeo\.com\/)?(?:video\/)?(\d+)([^?&#"'>]?)/;
                },
                isApiLoaded : function() {
                      return window.Vimeo;
                },
                getApiObject : function() {
                      return Vimeo;
                }
          };

          return _.extend( 'vimeo' === provider ? _vimeoLoader : _youtubeLoader, _defaults );
      };//getApiProvider


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