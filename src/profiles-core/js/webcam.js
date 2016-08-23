/* global profiles, Profiles_Uploader, _, Backbone */

window.profiles = window.profiles || {};

( function() {

	// Bail if not set
	if ( typeof Profiles_Uploader === 'undefined' ) {
		return;
	}

	profiles.Models      = profiles.Models || {};
	profiles.Collections = profiles.Collections || {};
	profiles.Views       = profiles.Views || {};

	profiles.WebCam = {
		start: function() {
			this.params = {
				video:          null,
				videoStream:    null,
				capture_enable: false,
				capture:        null,
				canvas:         null,
				warning:        null,
				flipped:        false
			};

			profiles.Avatar.nav.on( 'profiles-avatar-view:changed', _.bind( this.setView, this ) );
		},

		setView: function( view ) {
			if ( 'camera' !== view ) {
				// Stop the camera if needed
				if ( ! _.isNull( this.params.video ) ) {
					this.stop();

					// Remove all warnings as we're changing the view
					this.removeWarning();
				}

				// Stop as this is not Camera area
				return;
			}

			// Create the WebCam view
			var cameraView = new profiles.Views.WebCamAvatar( { model: new Backbone.Model( { user_media: false } ) } );

			// Make sure the flipped param is reset
			this.params.flipped = false;

			// Add it to views
			profiles.Avatar.views.add( { id: 'camera', view: cameraView } );

			// Display it
	        cameraView.inject( '.profiles-avatar' );
		},

		removeView: function() {
			var camera;

			if ( ! _.isUndefined( profiles.Avatar.views.get( 'camera' ) ) ) {
				camera = profiles.Avatar.views.get( 'camera' );
				camera.get( 'view' ).remove();
				profiles.Avatar.views.remove( { id: 'camera', view: camera } );
			}
		},

		gotStream: function( stream ) {
			var video = profiles.WebCam.params.video;
			profiles.WebCam.params.videoStream = stream;

			// User Feedback
			profiles.WebCam.displayWarning( 'loaded' );

			video.onerror = function () {
				// User Feedback
				profiles.WebCam.displayWarning( 'videoerror' );

				if ( video ) {
					profiles.WebCam.stop();
				}
			};

			stream.onended = profiles.WebCam.noStream();

			if ( video.mozSrcObject !== undefined ) {
				video.mozSrcObject = stream;
				video.play();
			} else if ( navigator.mozGetUserMedia ) {
				video.src = stream;
				video.play();
			} else if ( video.srcObject !== undefined ) {
				video.srcObject = stream;
			} else if ( window.URL ) {
				video.src = window.URL.createObjectURL( stream );
			} else {
				video.src = stream;
			}

			profiles.WebCam.params.capture_enable = true;
		},

		stop: function() {
			profiles.WebCam.params.capture_enable = false;
			if ( profiles.WebCam.params.videoStream ) {
				if ( profiles.WebCam.params.videoStream.stop ) {
					profiles.WebCam.params.videoStream.stop();
				} else if ( profiles.WebCam.params.videoStream.msStop ) {
					profiles.WebCam.params.videoStream.msStop();
				}
				profiles.WebCam.params.videoStream.onended = null;
				profiles.WebCam.params.videoStream = null;
			}
			if ( profiles.WebCam.params.video ) {
				profiles.WebCam.params.video.onerror = null;
				profiles.WebCam.params.video.pause();
				if ( profiles.WebCam.params.video.mozSrcObject ) {
					profiles.WebCam.params.video.mozSrcObject = null;
				}
				profiles.WebCam.params.video.src = '';
			}
		},

		noStream: function() {
			if ( _.isNull( profiles.WebCam.params.videoStream ) ) {
				// User Feedback
				profiles.WebCam.displayWarning( 'noaccess' );

				profiles.WebCam.removeView();
			}
		},

		setAvatar: function( avatar ) {
			if ( ! avatar.get( 'url' ) ) {
				profiles.WebCam.displayWarning( 'nocapture' );
			}

			// Remove the view
			profiles.WebCam.removeView();

			profiles.Avatar.setAvatar( avatar );
		},

		removeWarning: function() {
			if ( ! _.isNull( this.params.warning ) ) {
				this.params.warning.remove();
			}
		},

		displayWarning: function( code ) {
			this.removeWarning();

			this.params.warning = new profiles.Views.uploaderWarning( {
				value: Profiles_Uploader.strings.camera_warnings[code]
			} );

			this.params.warning.inject( '.profiles-avatar-status' );
		}
	};

	// Profiles WebCam view
	profiles.Views.WebCamAvatar = profiles.View.extend( {
		tagName: 'div',
		id: 'profiles-webcam-avatar',
		template: profiles.template( 'profiles-avatar-webcam' ),

		events: {
			'click .avatar-webcam-capture': 'captureStream',
			'click .avatar-webcam-save': 'saveCapture'
		},

		initialize: function() {
			var params;

			if ( navigator.getUserMedia || navigator.oGetUserMedia || navigator.mozGetUserMedia || navigator.webkitGetUserMedia || navigator.msGetUserMedia ) {

				// We need to add some cropping stuff to use profiles.Avatar.setAvatar()
				params = _.extend( _.pick( Profiles_Uploader.settings.defaults.multipart_params.profiles_params,
					'object',
					'item_id',
					'nonces'
					), {
						user_media:  true,
						w: Profiles_Uploader.settings.crop.full_w,
						h: Profiles_Uploader.settings.crop.full_h,
						x: 0,
						y: 0,
						type: 'camera'
					}
				);

				this.model.set( params );
			}

			this.on( 'ready', this.useStream, this );
		},

		useStream:function() {
			// No support for user media... Stop!
			if ( ! this.model.get( 'user_media' ) ) {
				return;
			}

			this.options.video = new profiles.Views.WebCamVideo();
			this.options.canvas = new profiles.Views.WebCamCanvas();

			this.$el.find( '#avatar-to-crop' ).append( this.options.video.el );
			this.$el.find( '#avatar-crop-pane' ).append( this.options.canvas.el );

			profiles.WebCam.params.video = this.options.video.el;
			profiles.WebCam.params.canvas = this.options.canvas.el;

			// User Feedback
			profiles.WebCam.displayWarning( 'requesting' );

			if ( navigator.getUserMedia ) {
				navigator.getUserMedia( { video:true }, profiles.WebCam.gotStream, profiles.WebCam.noStream );
			}  else if ( navigator.oGetUserMedia ) {
				navigator.oGetUserMedia( { video:true }, profiles.WebCam.gotStream, profiles.WebCam.noStream );
			} else if ( navigator.mozGetUserMedia ) {
				navigator.mozGetUserMedia( { video:true }, profiles.WebCam.gotStream, profiles.WebCam.noStream );
			} else if ( navigator.webkitGetUserMedia ) {
				navigator.webkitGetUserMedia( { video:true }, profiles.WebCam.gotStream, profiles.WebCam.noStream );
			} else if (navigator.msGetUserMedia) {
				navigator.msGetUserMedia( { video:true, audio:false }, profiles.WebCams.gotStream, profiles.WebCam.noStream );
			} else {
				// User Feedback
				profiles.WebCam.displayWarning( 'errormsg' );
			}
		},

		captureStream: function( event ) {
			var sx, sc;
			event.preventDefault();

			if ( ! profiles.WebCam.params.capture_enable ) {
				// User Feedback
				profiles.WebCam.displayWarning( 'loading' );
				return;
			}

			if ( this.model.get( 'h' ) > this.options.video.el.videoHeight || this.model.get( 'w' ) > this.options.video.el.videoWidth ) {
				profiles.WebCam.displayWarning( 'videoerror' );
				return;
			}

			// Set the offset
			sc = this.options.video.el.videoHeight;
			sx = ( this.options.video.el.videoWidth - sc ) / 2;

			// Flip only once.
			if ( ! profiles.WebCam.params.flipped ) {
				this.options.canvas.el.getContext( '2d' ).translate( this.model.get( 'w' ), 0 );
				this.options.canvas.el.getContext( '2d' ).scale( -1, 1 );
				profiles.WebCam.params.flipped = true;
			}

			this.options.canvas.el.getContext( '2d' ).drawImage( this.options.video.el, sx, 0, sc, sc, 0, 0, this.model.get( 'w' ), this.model.get( 'h' ) );
			profiles.WebCam.params.capture = this.options.canvas.el.toDataURL( 'image/png' );
			this.model.set( 'url', profiles.WebCam.params.capture );

			// User Feedback
			profiles.WebCam.displayWarning( 'ready' );
		},

		saveCapture: function( event ) {
			event.preventDefault();

			if ( ! profiles.WebCam.params.capture ) {
				// User Feedback
				profiles.WebCam.displayWarning( 'nocapture' );
				return;
			}

			profiles.WebCam.stop();
			profiles.WebCam.setAvatar( this.model );
		}
	} );

	// Profiles Video stream view
	profiles.Views.WebCamVideo = profiles.View.extend( {
		tagName: 'video',
		id: 'profiles-webcam-video',
		attributes: {
			autoplay: 'autoplay'
		}
	} );

	// Profiles Canvas (capture) view
	profiles.Views.WebCamCanvas = profiles.View.extend( {
		tagName: 'canvas',
		id: 'profiles-webcam-canvas',
		attributes: {
			width:  150,
			height: 150
		},

		initialize: function() {
			// Make sure to take in account profiles_core_avatar_full_height or profiles_core_avatar_full_width php filters
			if ( ! _.isUndefined( Profiles_Uploader.settings.crop.full_h ) && ! _.isUndefined( Profiles_Uploader.settings.crop.full_w ) ) {
				this.el.attributes.width.value  = Profiles_Uploader.settings.crop.full_w;
				this.el.attributes.height.value = Profiles_Uploader.settings.crop.full_h;
			}
		}
	} );

	profiles.WebCam.start();

})( profiles, jQuery );
