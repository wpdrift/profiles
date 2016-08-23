/* global profiles, Profiles_Uploader, _, Backbone */

window.profiles = window.profiles || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof Profiles_Uploader === 'undefined' ) {
		return;
	}

	profiles.Models      = profiles.Models || {};
	profiles.Collections = profiles.Collections || {};
	profiles.Views       = profiles.Views || {};

	profiles.Avatar = {
		start: function() {
			var self = this;

			/**
			 * Remove the profiles-legacy UI
			 *
			 * profiles.Avatar successfully loaded, we can now
			 * safely remove the Legacy UI.
			 */
			this.removeLegacyUI();

			// Init some vars
			this.views    = new Backbone.Collection();
			this.jcropapi = {};
			this.warning = null;

			// Set up nav
			this.setupNav();

			// Avatars are uploaded files
			this.avatars = profiles.Uploader.filesUploaded;

			// The Avatar Attachment object.
			this.Attachment = new Backbone.Model();

			// Wait till the queue is reset
			profiles.Uploader.filesQueue.on( 'reset', this.cropView, this );

			/**
			 * In Administration screens we're using Thickbox
			 * We need to make sure to reset the views if it's closed or opened
			 */
			$( 'body.wp-admin' ).on( 'tb_unload', '#TB_window', function() {
				self.resetViews();
			} );

			$( 'body.wp-admin' ).on( 'click', '.profiles-xprofile-avatar-user-edit', function() {
				self.resetViews();
			} );
		},

		removeLegacyUI: function() {
			// User
			if ( $( '#avatar-upload-form' ).length ) {
				$( '#avatar-upload' ).remove();
				$( '#avatar-upload-form p' ).remove();

			// Group Manage
			} else if ( $( '#group-settings-form' ).length ) {
				$( '#group-settings-form p' ).each( function( i ) {
					if ( 0 !== i ) {
						$( this ).remove();
					}
				} );

				if ( $( '#delete-group-avatar-button' ).length ) {
					$( '#delete-group-avatar-button' ).remove();
				}

			// Group Create
			} else if ( $( '#group-create-body' ).length ) {
				$( '.main-column p #file' ).remove();
				$( '.main-column p #upload' ).remove();

			// Admin Extended Profile
			} else if ( $( '#profiles_xprofile_user_admin_avatar a.profiles-xprofile-avatar-user-admin' ).length ) {
				$( '#profiles_xprofile_user_admin_avatar a.profiles-xprofile-avatar-user-admin' ).remove();
			}
		},

		setView: function( view ) {
			// Clear views
			if ( ! _.isUndefined( this.views.models ) ) {
				_.each( this.views.models, function( model ) {
					model.get( 'view' ).remove();
				}, this );
			}

			// Reset Views
			this.views.reset();

			// Reset Avatars (file uploaded)
			if ( ! _.isUndefined( this.avatars ) ) {
				this.avatars.reset();
			}

			// Reset the Jcrop API
			if ( ! _.isEmpty( this.jcropapi ) ) {
				this.jcropapi.destroy();
				this.jcropapi = {};
			}

			// Load the required view
			switch ( view ) {
				case 'upload':
					this.uploaderView();
					break;

				case 'delete':
					this.deleteView();
					break;
			}
		},

		resetViews: function() {
			// Reset to the uploader view
			this.nav.trigger( 'profiles-avatar-view:changed', 'upload' );

			// Reset to the uploader nav
			_.each( this.navItems.models, function( model ) {
				if ( model.id === 'upload' ) {
					model.set( { active: 1 } );
				} else {
					model.set( { active: 0 } );
				}
			} );
		},

		setupNav: function() {
			var self = this,
			    initView, activeView;

			this.navItems = new Backbone.Collection();

			_.each( Profiles_Uploader.settings.nav, function( item, index ) {
				if ( ! _.isObject( item ) ) {
					return;
				}

				// Reset active View
				activeView = 0;

				if ( 0 === index ) {
					initView = item.id;
					activeView = 1;
				}

				self.navItems.add( {
					id     : item.id,
					name   : item.caption,
					href   : '#',
					active : activeView,
					hide   : _.isUndefined( item.hide ) ? 0 : item.hide
				} );
			} );

			this.nav = new profiles.Views.Nav( { collection: this.navItems } );
			this.nav.inject( '.profiles-avatar-nav' );

			// Activate the initial view (uploader)
			this.setView( initView );

			// Listen to nav changes (it's like a do_action!)
			this.nav.on( 'profiles-avatar-view:changed', _.bind( this.setView, this ) );
		},

		uploaderView: function() {
			// Listen to the Queued uploads
			profiles.Uploader.filesQueue.on( 'add', this.uploadProgress, this );

			// Create the Profiles Uploader
			var uploader = new profiles.Views.Uploader();

			// Add it to views
			this.views.add( { id: 'upload', view: uploader } );

			// Display it
			uploader.inject( '.profiles-avatar' );
		},

		uploadProgress: function() {
			// Create the Uploader status view
			var avatarStatus = new profiles.Views.uploaderStatus( { collection: profiles.Uploader.filesQueue } );

			if ( ! _.isUndefined( this.views.get( 'status' ) ) ) {
				this.views.set( { id: 'status', view: avatarStatus } );
			} else {
				this.views.add( { id: 'status', view: avatarStatus } );
			}

			// Display it
			avatarStatus.inject( '.profiles-avatar-status' );
		},

		cropView: function() {
			var status;

			// Bail there was an error during the Upload
			if ( _.isEmpty( this.avatars.models ) ) {
				return;
			}

			// Make sure to remove the uploads status
			if ( ! _.isUndefined( this.views.get( 'status' ) ) ) {
				status = this.views.get( 'status' );
				status.get( 'view' ).remove();
				this.views.remove( { id: 'status', view: status } );
			}

			// Create the Avatars view
			var avatar = new profiles.Views.Avatars( { collection: this.avatars } );
			this.views.add( { id: 'crop', view: avatar } );

			avatar.inject( '.profiles-avatar' );
		},

		setAvatar: function( avatar ) {
			var self = this,
				crop;

			// Remove the crop view
			if ( ! _.isUndefined( this.views.get( 'crop' ) ) ) {
				// Remove the JCrop API
				if ( ! _.isEmpty( this.jcropapi ) ) {
					this.jcropapi.destroy();
					this.jcropapi = {};
				}
				crop = this.views.get( 'crop' );
				crop.get( 'view' ).remove();
				this.views.remove( { id: 'crop', view: crop } );
			}

			// Set the avatar !
			profiles.ajax.post( 'profiles_avatar_set', {
				json:          true,
				original_file: avatar.get( 'url' ),
				crop_w:        avatar.get( 'w' ),
				crop_h:        avatar.get( 'h' ),
				crop_x:        avatar.get( 'x' ),
				crop_y:        avatar.get( 'y' ),
				item_id:       avatar.get( 'item_id' ),
				object:        avatar.get( 'object' ),
				type:          _.isUndefined( avatar.get( 'type' ) ) ? 'crop' : avatar.get( 'type' ),
				nonce:         avatar.get( 'nonces' ).set
			} ).done( function( response ) {
				var avatarStatus = new profiles.Views.AvatarStatus( {
					value : Profiles_Uploader.strings.feedback_messages[ response.feedback_code ],
					type : 'success'
				} );

				self.views.add( {
					id   : 'status',
					view : avatarStatus
				} );

				avatarStatus.inject( '.profiles-avatar-status' );

				// Update each avatars of the page
				$( '.' + avatar.get( 'object' ) + '-' + response.item_id + '-avatar' ).each( function() {
					$(this).prop( 'src', response.avatar );
				} );

				// Inject the Delete nav
				profiles.Avatar.navItems.get( 'delete' ).set( { hide: 0 } );

				/**
				 * Set the Attachment object
				 *
				 * You can run extra actions once the avatar is set using:
				 * profiles.Avatar.Attachment.on( 'change:url', function( data ) { your code } );
				 *
				 * In this case data.attributes will include the url to the newly
				 * uploaded avatar, the object and the item_id concerned.
				 */
				self.Attachment.set( _.extend(
					_.pick( avatar.attributes, ['object', 'item_id'] ),
					{ url: response.avatar, action: 'uploaded' }
				) );

			} ).fail( function( response ) {
				var feedback = Profiles_Uploader.strings.default_error;
				if ( ! _.isUndefined( response ) ) {
					feedback = Profiles_Uploader.strings.feedback_messages[ response.feedback_code ];
				}

				var avatarStatus = new profiles.Views.AvatarStatus( {
					value : feedback,
					type : 'error'
				} );

				self.views.add( {
					id   : 'status',
					view : avatarStatus
				} );

				avatarStatus.inject( '.profiles-avatar-status' );
			} );
		},

		deleteView:function() {
			// Create the delete model
			var delete_model = new Backbone.Model( _.pick( Profiles_Uploader.settings.defaults.multipart_params.profiles_params,
				'object',
				'item_id',
				'nonces'
			) );

			// Create the delete view
			var deleteView = new profiles.Views.DeleteAvatar( { model: delete_model } );

			// Add it to views
			this.views.add( { id: 'delete', view: deleteView } );

			// Display it
			deleteView.inject( '.profiles-avatar' );
		},

		deleteAvatar: function( model ) {
			var self = this,
				deleteView;

			// Remove the delete view
			if ( ! _.isUndefined( this.views.get( 'delete' ) ) ) {
				deleteView = this.views.get( 'delete' );
				deleteView.get( 'view' ).remove();
				this.views.remove( { id: 'delete', view: deleteView } );
			}

			// Remove the avatar !
			profiles.ajax.post( 'profiles_avatar_delete', {
				json:          true,
				item_id:       model.get( 'item_id' ),
				object:        model.get( 'object' ),
				nonce:         model.get( 'nonces' ).remove
			} ).done( function( response ) {
				var avatarStatus = new profiles.Views.AvatarStatus( {
					value : Profiles_Uploader.strings.feedback_messages[ response.feedback_code ],
					type : 'success'
				} );

				self.views.add( {
					id   : 'status',
					view : avatarStatus
				} );

				avatarStatus.inject( '.profiles-avatar-status' );

				// Update each avatars of the page
				$( '.' + model.get( 'object' ) + '-' + response.item_id + '-avatar').each( function() {
					$( this ).prop( 'src', response.avatar );
				} );

				// Remove the Delete nav
				profiles.Avatar.navItems.get( 'delete' ).set( { active: 0, hide: 1 } );

				/**
				 * Reset the Attachment object
				 *
				 * You can run extra actions once the avatar is set using:
				 * profiles.Avatar.Attachment.on( 'change:url', function( data ) { your code } );
				 *
				 * In this case data.attributes will include the url to the gravatar,
				 * the object and the item_id concerned.
				 */
				self.Attachment.set( _.extend(
					_.pick( model.attributes, ['object', 'item_id'] ),
					{ url: response.avatar, action: 'deleted' }
				) );

			} ).fail( function( response ) {
				var feedback = Profiles_Uploader.strings.default_error;
				if ( ! _.isUndefined( response ) ) {
					feedback = Profiles_Uploader.strings.feedback_messages[ response.feedback_code ];
				}

				var avatarStatus = new profiles.Views.AvatarStatus( {
					value : feedback,
					type : 'error'
				} );

				self.views.add( {
					id   : 'status',
					view : avatarStatus
				} );

				avatarStatus.inject( '.profiles-avatar-status' );
			} );
		},

		removeWarning: function() {
			if ( ! _.isNull( this.warning ) ) {
				this.warning.remove();
			}
		},

		displayWarning: function( message ) {
			this.removeWarning();

			this.warning = new profiles.Views.uploaderWarning( {
				value: message
			} );

			this.warning.inject( '.profiles-avatar-status' );
		}
	};

	// Main Nav view
	profiles.Views.Nav = profiles.View.extend( {
		tagName:    'ul',
		className:  'avatar-nav-items',

		events: {
			'click .profiles-avatar-nav-item' : 'toggleView'
		},

		initialize: function() {
			var hasAvatar = _.findWhere( this.collection.models, { id: 'delete' } );

			// Display a message to inform about the delete tab
			if ( 1 !== hasAvatar.get( 'hide' ) ) {
				profiles.Avatar.displayWarning( Profiles_Uploader.strings.has_avatar_warning );
			}

			_.each( this.collection.models, this.addNavItem, this );
			this.collection.on( 'change:hide', this.showHideNavItem, this );
		},

		addNavItem: function( item ) {
			/**
			 * The delete nav is not added if no avatar
			 * is set for the object
			 */
			if ( 1 === item.get( 'hide' ) ) {
				return;
			}

			this.views.add( new profiles.Views.NavItem( { model: item } ) );
		},

		showHideNavItem: function( item ) {
			var isRendered = null;

			/**
			 * Loop in views to show/hide the nav item
			 * Profiles is only using this for the delete nav
			 */
			_.each( this.views._views[''], function( view ) {
				if ( 1 === view.model.get( 'hide' ) ) {
					view.remove();
				}

				// Check to see if the nav is not already rendered
				if ( item.get( 'id' ) === view.model.get( 'id' ) ) {
					isRendered = true;
				}
			} );

			// Add the Delete nav if not rendered
			if ( ! _.isBoolean( isRendered ) ) {
				this.addNavItem( item );
			}
		},

		toggleView: function( event ) {
			event.preventDefault();

			// First make sure to remove all warnings
			profiles.Avatar.removeWarning();

			var active = $( event.target ).data( 'nav' );

			_.each( this.collection.models, function( model ) {
				if ( model.id === active ) {
					model.set( { active: 1 } );
					this.trigger( 'profiles-avatar-view:changed', model.id );
				} else {
					model.set( { active: 0 } );
				}
			}, this );
		}
	} );

	// Nav item view
	profiles.Views.NavItem = profiles.View.extend( {
		tagName:    'li',
		className:  'avatar-nav-item',
		template: profiles.template( 'profiles-avatar-nav' ),

		initialize: function() {
			if ( 1 === this.model.get( 'active' ) ) {
				this.el.className += ' current';
			}
			this.el.id += 'profiles-avatar-' + this.model.get( 'id' );

			this.model.on( 'change:active', this.setCurrentNav, this );
		},

		setCurrentNav: function( model ) {
			if ( 1 === model.get( 'active' ) ) {
				this.$el.addClass( 'current' );
			} else {
				this.$el.removeClass( 'current' );
			}
		}
	} );

	// Avatars view
	profiles.Views.Avatars = profiles.View.extend( {
		className: 'items',

		initialize: function() {
			_.each( this.collection.models, this.addItemView, this );
		},

		addItemView: function( item ) {
			// Defaults to 150
			var full_d = { full_h: 150, full_w: 150 };

			// Make sure to take in account profiles_core_avatar_full_height or profiles_core_avatar_full_width php filters
			if ( ! _.isUndefined( Profiles_Uploader.settings.crop.full_h ) && ! _.isUndefined( Profiles_Uploader.settings.crop.full_w ) ) {
				full_d.full_h = Profiles_Uploader.settings.crop.full_h;
				full_d.full_w = Profiles_Uploader.settings.crop.full_w;
			}

			// Set the avatar model
			item.set( _.extend( _.pick( Profiles_Uploader.settings.defaults.multipart_params.profiles_params,
				'object',
				'item_id',
				'nonces'
			), full_d ) );

			// Add the view
			this.views.add( new profiles.Views.Avatar( { model: item } ) );
		}
	} );

	// Avatar view
	profiles.Views.Avatar = profiles.View.extend( {
		className: 'item',
		template: profiles.template( 'profiles-avatar-item' ),

		events: {
			'click .avatar-crop-submit': 'cropAvatar'
		},

		initialize: function() {
			_.defaults( this.options, {
				full_h:  Profiles_Uploader.settings.crop.full_h,
				full_w:  Profiles_Uploader.settings.crop.full_w,
				aspectRatio : 1
			} );

			// Display a warning if the image is smaller than minimum advised
			if ( false !== this.model.get( 'feedback' ) ) {
				profiles.Avatar.displayWarning( this.model.get( 'feedback' ) );
			}

			this.on( 'ready', this.initCropper );
		},

		initCropper: function() {
			var self = this,
				tocrop = this.$el.find( '#avatar-to-crop img' ),
				availableWidth = this.$el.width(),
				selection = {}, crop_top, crop_bottom, crop_left, crop_right, nh, nw;

			if ( ! _.isUndefined( this.options.full_h ) && ! _.isUndefined( this.options.full_w ) ) {
				this.options.aspectRatio = this.options.full_w / this.options.full_h;
			}

			selection.w = this.model.get( 'width' );
			selection.h = this.model.get( 'height' );

			/**
			 * Make sure the crop preview is at the right of the avatar
			 * if the available width allowes it.
			 */
			if ( this.options.full_w + selection.w + 20 < availableWidth ) {
				$( '#avatar-to-crop' ).addClass( 'adjust' );
				this.$el.find( '.avatar-crop-management' ).addClass( 'adjust' );
			}

			if ( selection.h <= selection.w ) {
				crop_top    = Math.round( selection.h / 4 );
				nh = nw     = Math.round( selection.h / 2 );
				crop_bottom = nh + crop_top;
				crop_left   = ( selection.w - nw ) / 2;
				crop_right  = nw + crop_left;
			} else {
				crop_left   = Math.round( selection.w / 4 );
				nh = nw     = Math.round( selection.w / 2 );
				crop_right  = nw + crop_left;
				crop_top    = ( selection.h - nh ) / 2;
				crop_bottom = nh + crop_top;
			}

			// Add the cropping interface
			tocrop.Jcrop( {
				onChange: _.bind( self.showPreview, self ),
				onSelect: _.bind( self.showPreview, self ),
				aspectRatio: self.options.aspectRatio,
				setSelect: [ crop_left, crop_top, crop_right, crop_bottom ]
			}, function() {
				// Get the Jcrop API
				profiles.Avatar.jcropapi = this;
			} );
		},

		cropAvatar: function( event ) {
			event.preventDefault();

			profiles.Avatar.setAvatar( this.model );
		},

		showPreview: function( coords ) {
			if ( ! coords.w || ! coords.h ) {
				return;
			}

			if ( parseInt( coords.w, 10 ) > 0 ) {
				var fw = this.options.full_w;
				var fh = this.options.full_h;
				var rx = fw / coords.w;
				var ry = fh / coords.h;

				// Update the model
				this.model.set( { x: coords.x, y: coords.y, w: coords.w, h: coords.h } );

				$( '#avatar-crop-preview' ).css( {
					maxWidth:'none',
					width: Math.round( rx *  this.model.get( 'width' ) )+ 'px',
					height: Math.round( ry * this.model.get( 'height' ) )+ 'px',
					marginLeft: '-' + Math.round( rx * this.model.get( 'x' ) ) + 'px',
					marginTop: '-' + Math.round( ry * this.model.get( 'y' ) ) + 'px'
				} );
			}
		}
	} );

	// Profiles Avatar Feedback view
	profiles.Views.AvatarStatus = profiles.View.extend( {
		tagName: 'p',
		className: 'updated',
		id: 'profiles-avatar-feedback',

		initialize: function() {
			this.el.className += ' ' + this.options.type;
			this.value = this.options.value;
		},

		render: function() {
			this.$el.html( this.value );
			return this;
		}
	} );

	// Profiles Avatar Delete view
	profiles.Views.DeleteAvatar = profiles.View.extend( {
		tagName: 'div',
		id: 'profiles-delete-avatar-container',
		template: profiles.template( 'profiles-avatar-delete' ),

		events: {
			'click #profiles-delete-avatar': 'deleteAvatar'
		},

		deleteAvatar: function( event ) {
			event.preventDefault();

			profiles.Avatar.deleteAvatar( this.model );
		}
	} );

	profiles.Avatar.start();

})( profiles, jQuery );
