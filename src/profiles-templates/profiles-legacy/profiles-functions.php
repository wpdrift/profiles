<?php
/**
 * Functions of Profiles's Legacy theme.
 *
 * @since 1.7.0
 *
 * @package Profiles
 * @suprofilesackage Profiles_Theme_Compat
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Theme Setup ***************************************************************/

if ( !class_exists( 'Profiles_Legacy' ) ) :

/**
 * Loads Profiles Legacy Theme functionality.
 *
 * This is not a real theme by WordPress standards, and is instead used as the
 * fallback for any WordPress theme that does not have Profiles templates in it.
 *
 * To make your custom theme Profiles compatible and customize the templates, you
 * can copy these files into your theme without needing to merge anything
 * together; Profiles should safely handle the rest.
 *
 * See @link Profiles_Theme_Compat() for more.
 *
 * @since 1.7.0
 *
 * @package Profiles
 * @suprofilesackage Profiles_Theme_Compat
 */
class Profiles_Legacy extends Profiles_Theme_Compat {

	/** Functions *************************************************************/

	/**
	 * The main Profiles (Legacy) Loader.
	 *
	 * @since 1.7.0
	 *
	 */
	public function __construct() {
		parent::start();
	}

	/**
	 * Component global variables.
	 *
	 * You'll want to customize the values in here, so they match whatever your
	 * needs are.
	 *
	 * @since 1.7.0
	 */
	protected function setup_globals() {
		$profiles            = profiles();
		$this->id      = 'legacy';
		$this->name    = __( 'Profiles Legacy', 'profiles' );
		$this->version = profiles_get_version();
		$this->dir     = trailingslashit( $profiles->themes_dir . '/profiles-legacy' );
		$this->url     = trailingslashit( $profiles->themes_url . '/profiles-legacy' );
	}

	/**
	 * Setup the theme hooks.
	 *
	 * @since 1.7.0
	 *
	 */
	protected function setup_actions() {

		// Filter Profiles template hierarchy and look for page templates.
		add_filter( 'profiles_get_profiles_template', array( $this, 'theme_compat_page_templates' ), 10, 1 );

		/** Scripts ***********************************************************/

		add_action( 'profiles_enqueue_scripts', array( $this, 'enqueue_styles'   ) ); // Enqueue theme CSS
		add_action( 'profiles_enqueue_scripts', array( $this, 'enqueue_scripts'  ) ); // Enqueue theme JS
		add_filter( 'profiles_enqueue_scripts', array( $this, 'localize_scripts' ) ); // Enqueue theme script localization

		/** Body no-js Class **************************************************/

		add_filter( 'body_class', array( $this, 'add_nojs_body_class' ), 20, 1 );

		/** Buttons ***********************************************************/

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			// Register buttons for the relevant component templates
			// Friends button.
			// if ( profiles_is_active( 'friends' ) )
			// 	add_action( 'profiles_member_header_actions',    'profiles_add_friend_button',           5 );


		}


		/** Ajax **************************************************************/

		$actions = array(

			// Directory filters.
			'blogs_filter'    => 'profiles_legacy_theme_object_template_loader',
			'forums_filter'   => 'profiles_legacy_theme_object_template_loader',
			'groups_filter'   => 'profiles_legacy_theme_object_template_loader',
			'members_filter'  => 'profiles_legacy_theme_object_template_loader',
			'messages_filter' => 'profiles_legacy_theme_messages_template_loader',
			'invite_filter'   => 'profiles_legacy_theme_invite_template_loader',
			'requests_filter' => 'profiles_legacy_theme_requests_template_loader',

		);

		/**
		 * Register all of these AJAX handlers.
		 *
		 * The "wp_ajax_" action is used for logged in users, and "wp_ajax_nopriv_"
		 * executes for users that aren't logged in. This is for backpat with BP <1.6.
		 */
		foreach( $actions as $name => $function ) {
			add_action( 'wp_ajax_'        . $name, $function );
			add_action( 'wp_ajax_nopriv_' . $name, $function );
		}

		add_filter( 'profiles_ajax_querystring', 'profiles_legacy_theme_ajax_querystring', 10, 2 );

		/** Override **********************************************************/

		/**
		 * Fires after all of the Profiles theme compat actions have been added.
		 *
		 * @since 1.7.0
		 *
		 * @param Profiles_Legacy $this Current Profiles_Legacy instance.
		 */
		do_action_ref_array( 'profiles_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since 1.7.0
	 * @since 2.3.0 Support custom CSS file named after the current theme or parent theme.
	 *
	 */
	public function enqueue_styles() {
		$min = profiles_core_get_minified_asset_suffix();

		// Locate the BP stylesheet.
		$ltr = $this->locate_asset_in_stack( "profiles{$min}.css",     'css' );

		// LTR.
		if ( ! is_rtl() && isset( $ltr['location'], $ltr['handle'] ) ) {
			wp_enqueue_style( $ltr['handle'], $ltr['location'], array(), $this->version, 'screen' );

			if ( $min ) {
				wp_style_add_data( $ltr['handle'], 'suffix', $min );
			}
		}

		// RTL.
		if ( is_rtl() ) {
			$rtl = $this->locate_asset_in_stack( "profiles-rtl{$min}.css", 'css' );

			if ( isset( $rtl['location'], $rtl['handle'] ) ) {
				$rtl['handle'] = str_replace( '-css', '-css-rtl', $rtl['handle'] );  // Backwards compatibility.
				wp_enqueue_style( $rtl['handle'], $rtl['location'], array(), $this->version, 'screen' );

				if ( $min ) {
					wp_style_add_data( $rtl['handle'], 'suffix', $min );
				}
			}
		}

		// Compatibility stylesheets for specific themes.
		$theme = $this->locate_asset_in_stack( get_template() . "{$min}.css", 'css' );
		if ( ! is_rtl() && isset( $theme['location'] ) ) {
			// Use a unique handle.
			$theme['handle'] = 'profiles-' . get_template();
			wp_enqueue_style( $theme['handle'], $theme['location'], array(), $this->version, 'screen' );

			if ( $min ) {
				wp_style_add_data( $theme['handle'], 'suffix', $min );
			}
		}

		// Compatibility stylesheet for specific themes, RTL-version.
		if ( is_rtl() ) {
			$theme_rtl = $this->locate_asset_in_stack( get_template() . "-rtl{$min}.css", 'css' );

			if ( isset( $theme_rtl['location'] ) ) {
				$theme_rtl['handle'] = $theme['handle'] . '-rtl';
				wp_enqueue_style( $theme_rtl['handle'], $theme_rtl['location'], array(), $this->version, 'screen' );

				if ( $min ) {
					wp_style_add_data( $theme_rtl['handle'], 'suffix', $min );
				}
			}
		}
	}

	/**
	 * Enqueue the required JavaScript files
	 *
	 * @since 1.7.0
	 */
	public function enqueue_scripts() {
		$min = profiles_core_get_minified_asset_suffix();

		// Locate the BP JS file.
		$asset = $this->locate_asset_in_stack( "profiles{$min}.js", 'js' );

		// Enqueue the global JS, if found - AJAX will not work
		// without it.
		if ( isset( $asset['location'], $asset['handle'] ) ) {
			wp_enqueue_script( $asset['handle'], $asset['location'], profiles_core_get_js_dependencies(), $this->version );
		}

		/**
		 * Filters core JavaScript strings for internationalization before AJAX usage.
		 *
		 * @since 2.0.0
		 *
		 * @param array $value Array of key/value pairs for AJAX usage.
		 */
		$params = apply_filters( 'profiles_core_get_js_strings', array(
			'accepted'            => __( 'Accepted', 'profiles' ),
			'close'               => __( 'Close', 'profiles' ),
			'comments'            => __( 'comments', 'profiles' ),
			'leave_group_confirm' => __( 'Are you sure you want to leave this group?', 'profiles' ),
			'mark_as_fav'	      => __( 'Favorite', 'profiles' ),
			'my_favs'             => __( 'My Favorites', 'profiles' ),
			'rejected'            => __( 'Rejected', 'profiles' ),
			'remove_fav'	      => __( 'Remove Favorite', 'profiles' ),
			'show_all'            => __( 'Show all', 'profiles' ),
			'show_all_comments'   => __( 'Show all comments for this thread', 'profiles' ),
			'show_x_comments'     => __( 'Show all %d comments', 'profiles' ),
			'unsaved_changes'     => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'profiles' ),
			'view'                => __( 'View', 'profiles' ),
		) );
		wp_localize_script( $asset['handle'], 'Profiles_DTheme', $params );

		// Maybe enqueue comment reply JS.
		if ( is_singular() && profiles_is_blog_page() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Maybe enqueue password verify JS (register page or user settings page).
		if ( profiles_is_register_page() || ( function_exists( 'profiles_is_user_settings_general' ) && profiles_is_user_settings_general() ) ) {

			// Locate the Register Page JS file.
			$asset = $this->locate_asset_in_stack( "password-verify{$min}.js", 'js', 'profiles-legacy-password-verify' );

			$dependencies = array_merge( profiles_core_get_js_dependencies(), array(
				'password-strength-meter',
			) );

			// Enqueue script.
			wp_enqueue_script( $asset['handle'] . '-password-verify', $asset['location'], $dependencies, $this->version);
		}

		// Star private messages.
		if ( profiles_is_active( 'messages', 'star' ) && profiles_is_user_messages() ) {
			wp_localize_script( $asset['handle'], 'Profiles_PM_Star', array(
				'strings' => array(
					'text_unstar'  => __( 'Unstar', 'profiles' ),
					'text_star'    => __( 'Star', 'profiles' ),
					'title_unstar' => __( 'Starred', 'profiles' ),
					'title_star'   => __( 'Not starred', 'profiles' ),
					'title_unstar_thread' => __( 'Remove all starred messages in this thread', 'profiles' ),
					'title_star_thread'   => __( 'Star the first message in this thread', 'profiles' ),
				),
				'is_single_thread' => (int) profiles_is_messages_conversation(),
				'star_counter'     => 0,
				'unstar_counter'   => 0
			) );
		}
	}

	/**
	 * Get the URL and handle of a web-accessible CSS or JS asset
	 *
	 * We provide two levels of customizability with respect to where CSS
	 * and JS files can be stored: (1) the child theme/parent theme/theme
	 * compat hierarchy, and (2) the "template stack" of /profiles/css/,
	 * /community/css/, and /css/. In this way, CSS and JS assets can be
	 * overloaded, and default versions provided, in exactly the same way
	 * as corresponding PHP templates.
	 *
	 * We are duplicating some of the logic that is currently found in
	 * profiles_locate_template() and the _template_stack() functions. Those
	 * functions were built with PHP templates in mind, and will require
	 * refactoring in order to provide "stack" functionality for assets
	 * that must be accessible both using file_exists() (the file path)
	 * and at a public URI.
	 *
	 * This method is marked private, with the understanding that the
	 * implementation is subject to change or removal in an upcoming
	 * release, in favor of a unified _template_stack() system. Plugin
	 * and theme authors should not attempt to use what follows.
	 *
	 * @since 1.8.0
	 * @param string $file A filename like profiles.css.
	 * @param string $type Optional. Either "js" or "css" (the default).
	 * @param string $script_handle Optional. If set, used as the script name in `wp_enqueue_script`.
	 * @return array An array of data for the wp_enqueue_* function:
	 *   'handle' (eg 'profiles-child-css') and a 'location' (the URI of the
	 *   asset)
	 */
	private function locate_asset_in_stack( $file, $type = 'css', $script_handle = '' ) {
		$locations = array();

		// Ensure the assets can be located when running from /src/.
		if ( defined( 'Profiles_SOURCE_SUBDIRECTORY' ) && Profiles_SOURCE_SUBDIRECTORY === 'src' ) {
			$file = str_replace( '.min', '', $file );
		}

		// No need to check child if template == stylesheet.
		if ( is_child_theme() ) {
			$locations['profiles-child'] = array(
				'dir'  => get_stylesheet_directory(),
				'uri'  => get_stylesheet_directory_uri(),
				'file' => str_replace( '.min', '', $file ),
			);
		}

		$locations['profiles-parent'] = array(
			'dir'  => get_template_directory(),
			'uri'  => get_template_directory_uri(),
			'file' => str_replace( '.min', '', $file ),
		);

		$locations['profiles-legacy'] = array(
			'dir'  => profiles_get_theme_compat_dir(),
			'uri'  => profiles_get_theme_compat_url(),
			'file' => $file,
		);

		// Subdirectories within the top-level $locations directories.
		$subdirs = array(
			'profiles/' . $type,
			'community/' . $type,
			$type,
		);

		$retval = array();

		foreach ( $locations as $location_type => $location ) {
			foreach ( $subdirs as $subdir ) {
				if ( file_exists( trailingslashit( $location['dir'] ) . trailingslashit( $subdir ) . $location['file'] ) ) {
					$retval['location'] = trailingslashit( $location['uri'] ) . trailingslashit( $subdir ) . $location['file'];
					$retval['handle']   = ( $script_handle ) ? $script_handle : "{$location_type}-{$type}";

					break 2;
				}
			}
		}

		return $retval;
	}

	/**
	 * Adds the no-js class to the body tag.
	 *
	 * This function ensures that the <body> element will have the 'no-js' class by default. If you're
	 * using JavaScript for some visual functionality in your theme, and you want to provide noscript
	 * support, apply those styles to body.no-js.
	 *
	 * The no-js class is removed by the JavaScript created in profiles.js.
	 *
	 * @since 1.7.0
	 *
	 * @param array $classes Array of classes to append to body tag.
	 * @return array $classes
	 */
	public function add_nojs_body_class( $classes ) {
		if ( ! in_array( 'no-js', $classes ) )
			$classes[] = 'no-js';

		return array_unique( $classes );
	}

	/**
	 * Load localizations for topic script.
	 *
	 * These localizations require information that may not be loaded even by init.
	 *
	 * @since 1.7.0
	 */
	public function localize_scripts() {
	}

	/**
	 * Outputs sitewide notices markup in the footer.
	 *
	 * @since 1.7.0
	 *
	 * @see https://profiles.trac.wordpress.org/ticket/4802
	 */
	public function sitewide_notices() {
		// Do not show notices if user is not logged in.
		if ( ! is_user_logged_in() )
			return;

		// Add a class to determine if the admin bar is on or not.
		$class = did_action( 'admin_bar_menu' ) ? 'admin-bar-on' : 'admin-bar-off';
	}

	/**
	 * Filter the default theme compatibility root template hierarchy, and prepend
	 * a page template to the front if it's set.
	 *
	 * @see https://profiles.trac.wordpress.org/ticket/6065
	 *
	 * @since 2.2.0
	 *
	 * @param  array $templates Array of templates.
	 *                         to use the defined page template for component's directory and its single items
	 * @return array
	 */
	public function theme_compat_page_templates( $templates = array() ) {

		/**
		 * Filters whether or not we are looking at a directory to determine if to return early.
		 *
		 * @since 2.2.0
		 *
		 * @param bool $value Whether or not we are viewing a directory.
		 */
		if ( true === (bool) apply_filters( 'profiles_legacy_theme_compat_page_templates_directory_only', ! profiles_is_directory() ) ) {
			return $templates;
		}

		// No page ID yet.
		$page_id = 0;

		// Get the WordPress Page ID for the current view.
		foreach ( (array) profiles()->pages as $component => $profiles_page ) {

			// Handles the majority of components.
			if ( profiles_is_current_component( $component ) ) {
				$page_id = (int) $profiles_page->id;
			}

			// Stop if not on a user page.
			if ( ! profiles_is_user() && ! empty( $page_id ) ) {
				break;
			}

			// The Members component requires an explicit check due to overlapping components.
			if ( profiles_is_user() && ( 'members' === $component ) ) {
				$page_id = (int) $profiles_page->id;
				break;
			}
		}

		// Bail if no directory page set.
		if ( 0 === $page_id ) {
			return $templates;
		}

		// Check for page template.
		$page_template = get_page_template_slug( $page_id );

		// Add it to the beginning of the templates array so it takes precedence
		// over the default hierarchy.
		if ( ! empty( $page_template ) ) {

			/**
			 * Check for existence of template before adding it to template
			 * stack to avoid accidentally including an unintended file.
			 *
			 * @see: https://profiles.trac.wordpress.org/ticket/6190
			 */
			if ( '' !== locate_template( $page_template ) ) {
				array_unshift( $templates, $page_template );
			}
		}

		return $templates;
	}
}
new Profiles_Legacy();
endif;

/**
 * Add the Create a Group button to the Groups directory title.
 *
 * The profiles-legacy puts the Create a Group button into the page title, to mimic
 * the behavior of profiles-default.
 *
 * @since 2.0.0
 * @todo Deprecate
 *
 * @param string $title Groups directory title.
 * @return string
 */
function profiles_legacy_theme_group_create_button( $title ) {
	return $title . ' ' . profiles_get_group_create_button();
}

/**
 * Add the Create a Group nav to the Groups directory navigation.
 *
 * The profiles-legacy puts the Create a Group nav at the last position of
 * the Groups directory navigation.
 *
 * @since 2.2.0
 *
 */
function profiles_legacy_theme_group_create_nav() {
	profiles_group_create_nav_item();
}

/**
 * Add the Create a Site button to the Sites directory title.
 *
 * The profiles-legacy puts the Create a Site button into the page title, to mimic
 * the behavior of profiles-default.
 *
 * @since 2.0.0
 * @todo Deprecate
 *
 * @param string $title Sites directory title.
 * @return string
 */
function profiles_legacy_theme_blog_create_button( $title ) {
	return $title . ' ' . profiles_get_blog_create_button();
}

/**
 * Add the Create a Site nav to the Sites directory navigation.
 *
 * The profiles-legacy puts the Create a Site nav at the last position of
 * the Sites directory navigation.
 *
 * @since 2.2.0
 *
 */
function profiles_legacy_theme_blog_create_nav() {
	profiles_blog_create_nav_item();
}

/**
 * This function looks scarier than it actually is. :)
 * Each object loop (activity/members/groups/blogs/forums) contains default
 * parameters to show specific information based on the page we are currently
 * looking at.
 *
 * The following function will take into account any cookies set in the JS and
 * allow us to override the parameters sent. That way we can change the results
 * returned without reloading the page.
 *
 * By using cookies we can also make sure that user settings are retained
 * across page loads.
 *
 * @param string $query_string Query string for the current request.
 * @param string $object       Object for cookie.
 * @return string Query string for the component loops
 * @since 1.2.0
 */
function profiles_legacy_theme_ajax_querystring( $query_string, $object ) {
	if ( empty( $object ) )
		return '';

	// Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts.
	if ( ! empty( $_POST['cookie'] ) ) {
		$_Profiles_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
	} else {
		$_Profiles_COOKIE = &$_COOKIE;
	}

	$qs = array();

	/**
	 * Check if any cookie values are set. If there are then override the
	 * default params passed to the template loop.
	 */

	// Activity stream filtering on action.
	if ( ! empty( $_Profiles_COOKIE['profiles-' . $object . '-filter'] ) && '-1' != $_Profiles_COOKIE['profiles-' . $object . '-filter'] ) {
		$qs[] = 'type='   . $_Profiles_COOKIE['profiles-' . $object . '-filter'];
		$qs[] = 'action=' . $_Profiles_COOKIE['profiles-' . $object . '-filter'];
	}

	if ( ! empty( $_Profiles_COOKIE['profiles-' . $object . '-scope'] ) ) {
		if ( 'personal' == $_Profiles_COOKIE['profiles-' . $object . '-scope'] ) {
			$user_id = ( profiles_displayed_user_id() ) ? profiles_displayed_user_id() : profiles_loggedin_user_id();
			$qs[] = 'user_id=' . $user_id;
		}

		// Activity stream scope only on activity directory.
		if ( 'all' != $_Profiles_COOKIE['profiles-' . $object . '-scope'] && ! profiles_displayed_user_id() && ! profiles_is_single_item() )
			$qs[] = 'scope=' . $_Profiles_COOKIE['profiles-' . $object . '-scope'];
	}

	// If page and search_terms have been passed via the AJAX post request, use those.
	if ( ! empty( $_POST['page'] ) && '-1' != $_POST['page'] )
		$qs[] = 'page=' . absint( $_POST['page'] );

	// Excludes activity just posted and avoids duplicate ids.
	if ( ! empty( $_POST['exclude_just_posted'] ) ) {
		$just_posted = wp_parse_id_list( $_POST['exclude_just_posted'] );
		$qs[] = 'exclude=' . implode( ',', $just_posted );
	}

	// To get newest activities.
	if ( ! empty( $_POST['offset'] ) ) {
		$qs[] = 'offset=' . intval( $_POST['offset'] );
	}

	$object_search_text = profiles_get_search_default_text( $object );
	if ( ! empty( $_POST['search_terms'] ) && $object_search_text != $_POST['search_terms'] && 'false' != $_POST['search_terms'] && 'undefined' != $_POST['search_terms'] )
		$qs[] = 'search_terms=' . urlencode( $_POST['search_terms'] );

	// Now pass the querystring to override default values.
	$query_string = empty( $qs ) ? '' : join( '&', (array) $qs );

	$object_filter = '';
	if ( isset( $_Profiles_COOKIE['profiles-' . $object . '-filter'] ) )
		$object_filter = $_Profiles_COOKIE['profiles-' . $object . '-filter'];

	$object_scope = '';
	if ( isset( $_Profiles_COOKIE['profiles-' . $object . '-scope'] ) )
		$object_scope = $_Profiles_COOKIE['profiles-' . $object . '-scope'];

	$object_page = '';
	if ( isset( $_Profiles_COOKIE['profiles-' . $object . '-page'] ) )
		$object_page = $_Profiles_COOKIE['profiles-' . $object . '-page'];

	$object_search_terms = '';
	if ( isset( $_Profiles_COOKIE['profiles-' . $object . '-search-terms'] ) )
		$object_search_terms = $_Profiles_COOKIE['profiles-' . $object . '-search-terms'];

	$object_extras = '';
	if ( isset( $_Profiles_COOKIE['profiles-' . $object . '-extras'] ) )
		$object_extras = $_Profiles_COOKIE['profiles-' . $object . '-extras'];

	/**
	 * Filters the AJAX query string for the component loops.
	 *
	 * @since 1.7.0
	 *
	 * @param string $query_string        The query string we are working with.
	 * @param string $object              The type of page we are on.
	 * @param string $object_filter       The current object filter.
	 * @param string $object_scope        The current object scope.
	 * @param string $object_page         The current object page.
	 * @param string $object_search_terms The current object search terms.
	 * @param string $object_extras       The current object extras.
	 */
	return apply_filters( 'profiles_legacy_theme_ajax_querystring', $query_string, $object, $object_filter, $object_scope, $object_page, $object_search_terms, $object_extras );
}

/**
 * Load the template loop for the current object.
 *
 * @return string Prints template loop for the specified object
 * @since 1.2.0
 */
function profiles_legacy_theme_object_template_loader() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Bail if no object passed.
	if ( empty( $_POST['object'] ) )
		return;

	// Sanitize the object.
	$object = sanitize_title( $_POST['object'] );

	// Bail if object is not an active component to prevent arbitrary file inclusion.
	if ( ! profiles_is_active( $object ) )
		return;

	/**
	 * AJAX requests happen too early to be seen by profiles_update_is_directory()
	 * so we do it manually here to ensure templates load with the correct
	 * context. Without this check, templates will load the 'single' version
	 * of themselves rather than the directory version.
	 */
	if ( ! profiles_current_action() )
		profiles_update_is_directory( true, profiles_current_component() );

	$template_part = $object . '/' . $object . '-loop';

	// The template part can be overridden by the calling JS function.
	if ( ! empty( $_POST['template'] ) ) {
		$template_part = sanitize_option( 'upload_path', $_POST['template'] );
	}

	// Locate the object template.
	profiles_get_template_part( $template_part );
	exit();
}

/**
 * Load messages template loop when searched on the private message page
 *
 * @since 1.6.0
 *
 * @return string Prints template loop for the Messages component.
 */
function profiles_legacy_theme_messages_template_loader() {
	profiles_get_template_part( 'members/single/messages/messages-loop' );
	exit();
}

/**
 * Load group invitations loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function profiles_legacy_theme_invite_template_loader() {
	profiles_get_template_part( 'groups/single/invites-loop' );
	exit();
}

/**
 * Load group membership requests loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function profiles_legacy_theme_requests_template_loader() {
	profiles_get_template_part( 'groups/single/requests-loop' );
	exit();
}


/**
 * Posts new Activity comments received via a POST request.
 *
 * @since 1.2.0
 *
 * @global Profiles_Activity_Template $activities_template
 *
 * @return string HTML
 */
function profiles_legacy_theme_new_activity_comment() {
	global $activities_template;

	$profiles = profiles();

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'new_activity_comment', '_wpnonce_new_activity_comment' );

	if ( ! is_user_logged_in() ) {
		exit( '-1' );
	}

	$feedback = __( 'There was an error posting your reply. Please try again.', 'profiles' );

	if ( empty( $_POST['content'] ) ) {
		exit( '-1<div id="message" class="error profiles-ajax-message"><p>' . esc_html__( 'Please do not leave the comment area blank.', 'profiles' ) . '</p></div>' );
	}

	if ( empty( $_POST['form_id'] ) || empty( $_POST['comment_id'] ) || ! is_numeric( $_POST['form_id'] ) || ! is_numeric( $_POST['comment_id'] ) ) {
		exit( '-1<div id="message" class="error profiles-ajax-message"><p>' . esc_html( $feedback ) . '</p></div>' );
	}

	$comment_id = profiles_activity_new_comment( array(
		'activity_id' => $_POST['form_id'],
		'content'     => $_POST['content'],
		'parent_id'   => $_POST['comment_id'],
		'error_type'  => 'wp_error'
	) );

	if ( is_wp_error( $comment_id ) ) {
		exit( '-1<div id="message" class="error profiles-ajax-message"><p>' . esc_html( $comment_id->get_error_message() ) . '</p></div>' );
	}

	// Load the new activity item into the $activities_template global.
	profiles_has_activities( 'display_comments=stream&hide_spam=false&show_hidden=true&include=' . $comment_id );

	// Swap the current comment with the activity item we just loaded.
	if ( isset( $activities_template->activities[0] ) ) {
		$activities_template->activity = new stdClass();
		$activities_template->activity->id              = $activities_template->activities[0]->item_id;
		$activities_template->activity->current_comment = $activities_template->activities[0];

		// Because the whole tree has not been loaded, we manually
		// determine depth.
		$depth = 1;
		$parent_id = (int) $activities_template->activities[0]->secondary_item_id;
		while ( $parent_id !== (int) $activities_template->activities[0]->item_id ) {
			$depth++;
			$p_obj = new Profiles_Activity_Activity( $parent_id );
			$parent_id = (int) $p_obj->secondary_item_id;
		}
		$activities_template->activity->current_comment->depth = $depth;
	}

	// Get activity comment template part.
	profiles_get_template_part( 'activity/comment' );

	unset( $activities_template );
	exit;
}

/**
 * Deletes an Activity item received via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function profiles_legacy_theme_delete_activity() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce.
	check_admin_referer( 'profiles_activity_delete_link' );

	if ( ! is_user_logged_in() )
		exit( '-1' );

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	$activity = new Profiles_Activity_Activity( (int) $_POST['id'] );

	// Check access.
	if ( ! profiles_activity_user_can_delete( $activity ) )
		exit( '-1' );

	/** This action is documented in profiles-activity/profiles-activity-actions.php */
	do_action( 'profiles_activity_before_action_delete_activity', $activity->id, $activity->user_id );

	if ( ! profiles_activity_delete( array( 'id' => $activity->id, 'user_id' => $activity->user_id ) ) )
		exit( '-1<div id="message" class="error profiles-ajax-message"><p>' . __( 'There was a problem when deleting. Please try again.', 'profiles' ) . '</p></div>' );

	/** This action is documented in profiles-activity/profiles-activity-actions.php */
	do_action( 'profiles_activity_action_delete_activity', $activity->id, $activity->user_id );
	exit;
}

/**
 * Deletes an Activity comment received via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function profiles_legacy_theme_delete_activity_comment() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce.
	check_admin_referer( 'profiles_activity_delete_link' );

	if ( ! is_user_logged_in() )
		exit( '-1' );

	$comment = new Profiles_Activity_Activity( $_POST['id'] );

	// Check access.
	if ( ! profiles_current_user_can( 'profiles_moderate' ) && $comment->user_id != profiles_loggedin_user_id() )
		exit( '-1' );

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	/** This action is documented in profiles-activity/profiles-activity-actions.php */
	do_action( 'profiles_activity_before_action_delete_activity', $_POST['id'], $comment->user_id );

	if ( ! profiles_activity_delete_comment( $comment->item_id, $comment->id ) )
		exit( '-1<div id="message" class="error profiles-ajax-message"><p>' . __( 'There was a problem when deleting. Please try again.', 'profiles' ) . '</p></div>' );

	/** This action is documented in profiles-activity/profiles-activity-actions.php */
	do_action( 'profiles_activity_action_delete_activity', $_POST['id'], $comment->user_id );
	exit;
}

/**
 * AJAX spam an activity item or comment.
 *
 * @since 1.6.0
 *
 * @return mixed String on error, void on success.
 */
function profiles_legacy_theme_spam_activity() {
	$profiles = profiles();

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check an item ID was passed.
	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	// Is the current user allowed to spam items?
	if ( ! profiles_activity_user_can_mark_spam() )
		exit( '-1' );

	// Load up the activity item.
	$activity = new Profiles_Activity_Activity( (int) $_POST['id'] );
	if ( empty( $activity->component ) )
		exit( '-1' );

	// Check nonce.
	check_admin_referer( 'profiles_activity_akismet_spam_' . $activity->id );

	/** This action is documented in profiles-activity/profiles-activity-actions.php */
	do_action( 'profiles_activity_before_action_spam_activity', $activity->id, $activity );

	// Mark as spam.
	profiles_activity_mark_as_spam( $activity );
	$activity->save();

	/** This action is documented in profiles-activity/profiles-activity-actions.php */
	do_action( 'profiles_activity_action_spam_activity', $activity->id, $activity->user_id );
	exit;
}

/**
 * Mark an activity as a favourite via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function profiles_legacy_theme_mark_activity_favorite() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( profiles_activity_add_user_favorite( $_POST['id'] ) )
		_e( 'Remove Favorite', 'profiles' );
	else
		_e( 'Favorite', 'profiles' );

	exit;
}

/**
 * Un-favourite an activity via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function profiles_legacy_theme_unmark_activity_favorite() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( profiles_activity_remove_user_favorite( $_POST['id'] ) )
		_e( 'Favorite', 'profiles' );
	else
		_e( 'Remove Favorite', 'profiles' );

	exit;
}

/**
 * Fetches an activity's full, non-excerpted content via a POST request.
 * Used for the 'Read More' link on long activity items.
 *
 * @since 1.5.0
 *
 * @return string HTML
 */
function profiles_legacy_theme_get_single_activity_content() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	$activity_array = profiles_activity_get_specific( array(
		'activity_ids'     => $_POST['activity_id'],
		'display_comments' => 'stream'
	) );

	$activity = ! empty( $activity_array['activities'][0] ) ? $activity_array['activities'][0] : false;

	if ( empty( $activity ) )
		exit; // @todo: error?

	/**
	 * Fires before the return of an activity's full, non-excerpted content via a POST request.
	 *
	 * @since 1.7.0
	 *
	 * @param string $activity Activity content. Passed by reference.
	 */
	do_action_ref_array( 'profiles_legacy_theme_get_single_activity_content', array( &$activity ) );

	// Activity content retrieved through AJAX should run through normal filters, but not be truncated.
	remove_filter( 'profiles_get_activity_content_body', 'profiles_activity_truncate_entry', 5 );

	/** This filter is documented in profiles-activity/profiles-activity-template.php */
	$content = apply_filters( 'profiles_get_activity_content_body', $activity->content );

	exit( $content );
}

/**
 * Join or leave a group when clicking the "join/leave" button via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function profiles_legacy_theme_ajax_joinleave_group() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Cast gid as integer.
	$group_id = (int) $_POST['gid'];

	if ( groups_is_user_banned( profiles_loggedin_user_id(), $group_id ) )
		return;

	if ( ! $group = groups_get_group( array( 'group_id' => $group_id ) ) )
		return;

	if ( ! groups_is_user_member( profiles_loggedin_user_id(), $group->id ) ) {
		if ( 'public' == $group->status ) {
			check_ajax_referer( 'groups_join_group' );

			if ( ! groups_join_group( $group->id ) ) {
				_e( 'Error joining group', 'profiles' );
			} else {
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="leave-group" rel="leave" title="' . __( 'Leave Group', 'profiles' ) . '" href="' . wp_nonce_url( profiles_get_group_permalink( $group ) . 'leave-group', 'groups_leave_group' ) . '">' . __( 'Leave Group', 'profiles' ) . '</a>';
			}

		} elseif ( 'private' == $group->status ) {

			// If the user has already been invited, then this is
			// an Accept Invitation button.
			if ( groups_check_user_has_invite( profiles_loggedin_user_id(), $group->id ) ) {
				check_ajax_referer( 'groups_accept_invite' );

				if ( ! groups_accept_invite( profiles_loggedin_user_id(), $group->id ) ) {
					_e( 'Error requesting membership', 'profiles' );
				} else {
					echo '<a id="group-' . esc_attr( $group->id ) . '" class="leave-group" rel="leave" title="' . __( 'Leave Group', 'profiles' ) . '" href="' . wp_nonce_url( profiles_get_group_permalink( $group ) . 'leave-group', 'groups_leave_group' ) . '">' . __( 'Leave Group', 'profiles' ) . '</a>';
				}

			// Otherwise, it's a Request Membership button.
			} else {
				check_ajax_referer( 'groups_request_membership' );

				if ( ! groups_send_membership_request( profiles_loggedin_user_id(), $group->id ) ) {
					_e( 'Error requesting membership', 'profiles' );
				} else {
					echo '<a id="group-' . esc_attr( $group->id ) . '" class="group-button disabled pending membership-requested" rel="membership-requested" title="' . __( 'Request Sent', 'profiles' ) . '" href="' . profiles_get_group_permalink( $group ) . '">' . __( 'Request Sent', 'profiles' ) . '</a>';
				}
			}
		}

	} else {
		check_ajax_referer( 'groups_leave_group' );

		if ( ! groups_leave_group( $group->id ) ) {
			_e( 'Error leaving group', 'profiles' );
		} elseif ( 'public' == $group->status ) {
			echo '<a id="group-' . esc_attr( $group->id ) . '" class="join-group" rel="join" title="' . __( 'Join Group', 'profiles' ) . '" href="' . wp_nonce_url( profiles_get_group_permalink( $group ) . 'join', 'groups_join_group' ) . '">' . __( 'Join Group', 'profiles' ) . '</a>';
		} elseif ( 'private' == $group->status ) {
			echo '<a id="group-' . esc_attr( $group->id ) . '" class="request-membership" rel="join" title="' . __( 'Request Membership', 'profiles' ) . '" href="' . wp_nonce_url( profiles_get_group_permalink( $group ) . 'request-membership', 'groups_request_membership' ) . '">' . __( 'Request Membership', 'profiles' ) . '</a>';
		}
	}

	exit;
}

/**
 * Close and keep closed site wide notices from an admin in the sidebar, via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function profiles_legacy_theme_ajax_close_notice() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset( $_POST['notice_id'] ) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem closing the notice.', 'profiles' ) . '</p></div>';

	} else {
		$user_id      = get_current_user_id();
		$notice_ids   = profiles_get_user_meta( $user_id, 'closed_notices', true );
		$notice_ids[] = (int) $_POST['notice_id'];

		profiles_update_user_meta( $user_id, 'closed_notices', $notice_ids );
	}

	exit;
}

/**
 * Send a private message reply to a thread via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function profiles_legacy_theme_ajax_messages_send_reply() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_ajax_referer( 'messages_send_message' );

	$result = messages_new_message( array( 'thread_id' => (int) $_REQUEST['thread_id'], 'content' => $_REQUEST['content'] ) );

	if ( !empty( $result ) ) {

		// Pretend we're in the message loop.
		global $thread_template;

		profiles_thread_has_messages( array( 'thread_id' => (int) $_REQUEST['thread_id'] ) );

		// Set the current message to the 2nd last.
		$thread_template->message = end( $thread_template->thread->messages );
		$thread_template->message = prev( $thread_template->thread->messages );

		// Set current message to current key.
		$thread_template->current_message = key( $thread_template->thread->messages );

		// Now manually iterate message like we're in the loop.
		profiles_thread_the_message();

		// Manually call oEmbed
		// this is needed because we're not at the beginning of the loop.
		profiles_messages_embed();

		// Add new-message css class.
		add_filter( 'profiles_get_the_thread_message_css_class', create_function( '$retval', '
			$retval[] = "new-message";
			return $retval;
		' ) );

		// Output single message template part.
		profiles_get_template_part( 'members/single/messages/message' );

		// Clean up the loop.
		profiles_thread_messages();

	} else {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem sending that reply. Please try again.', 'profiles' ) . '</p></div>';
	}

	exit;
}

/**
 * Mark a private message as unread in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function profiles_legacy_theme_ajax_message_markunread() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem marking messages as unread.', 'profiles' ) . '</p></div>';

	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			Profiles_Messages_Thread::mark_as_unread( (int) $thread_ids[$i] );
		}
	}

	exit;
}

/**
 * Mark a private message as read in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function profiles_legacy_theme_ajax_message_markread() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __('There was a problem marking messages as read.', 'profiles' ) . '</p></div>';

	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			Profiles_Messages_Thread::mark_as_read( (int) $thread_ids[$i] );
		}
	}

	exit;
}

/**
 * Delete a private message(s) in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function profiles_legacy_theme_ajax_messages_delete() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem deleting messages.', 'profiles' ) . '</p></div>';

	} else {
		$thread_ids = wp_parse_id_list( $_POST['thread_ids'] );
		messages_delete_thread( $thread_ids );

		_e( 'Messages deleted.', 'profiles' );
	}

	exit;
}

/**
 * AJAX handler for autocomplete.
 *
 * Displays friends only, unless Profiles_MESSAGES_AUTOCOMPLETE_ALL is defined.
 *
 * @since 1.2.0
 */
function profiles_legacy_theme_ajax_messages_autocomplete_results() {

	/**
	 * Filters the max results default value for ajax messages autocomplete results.
	 *
	 * @since 1.5.0
	 *
	 * @param int $value Max results for autocomplete. Default 10.
	 */
	$limit = isset( $_GET['limit'] ) ? absint( $_GET['limit'] )          : (int) apply_filters( 'profiles_autocomplete_max_results', 10 );
	$term  = isset( $_GET['q'] )     ? sanitize_text_field( $_GET['q'] ) : '';

	// Include everyone in the autocomplete, or just friends?
	if ( profiles_is_current_component( profiles_get_messages_slug() ) ) {
		$only_friends = ( profiles()->messages->autocomplete_all === false );
	} else {
		$only_friends = true;
	}

	$suggestions = profiles_core_get_suggestions( array(
		'limit'        => $limit,
		'only_friends' => $only_friends,
		'term'         => $term,
		'type'         => 'members',
	) );

	if ( $suggestions && ! is_wp_error( $suggestions ) ) {
		foreach ( $suggestions as $user ) {

			// Note that the final line break acts as a delimiter for the
			// autocomplete JavaScript and thus should not be removed.
			printf( '<span id="%s" href="#"></span><img src="%s" style="width: 15px"> &nbsp; %s (%s)' . "\n",
				esc_attr( 'link-' . $user->ID ),
				esc_url( $user->image ),
				esc_html( $user->name ),
				esc_html( $user->ID )
			);
		}
	}

	exit;
}

/**
 * AJAX callback to set a message's star status.
 *
 * @since 2.3.0
 */
function profiles_legacy_theme_ajax_messages_star_handler() {
	if ( false === profiles_is_active( 'messages', 'star' ) || empty( $_POST['message_id'] ) ) {
		return;
	}

	// Check nonce.
	check_ajax_referer( 'profiles-messages-star-' . (int) $_POST['message_id'], 'nonce' );

	// Check capability.
	if ( ! is_user_logged_in() || ! profiles_core_can_edit_settings() ) {
		return;
	}

	if ( true === profiles_messages_star_set_action( array(
		'action'     => $_POST['star_status'],
		'message_id' => (int) $_POST['message_id'],
		'bulk'       => ! empty( $_POST['bulk'] ) ? true : false
	 ) ) ) {
		echo '1';
		die();
	}

	echo '-1';
	die();
}

/**
 * BP Legacy's callback for the cover image feature.
 *
 * @since  2.4.0
 *
 * @param  array $params the current component's feature parameters.
 * @return array          an array to inform about the css handle to attach the css rules to
 */
function profiles_legacy_theme_cover_image( $params = array() ) {
	if ( empty( $params ) ) {
		return;
	}

	// Avatar height - padding - 1/2 avatar height.
	$avatar_offset = $params['height'] - 5 - round( (int) profiles_core_avatar_full_height() / 2 );

	// Header content offset + spacing.
	$top_offset  = profiles_core_avatar_full_height() - 10;
	$left_offset = profiles_core_avatar_full_width() + 20;

	$cover_image = ( !empty( $params['cover_image'] ) ) ? 'background-image: url(' . $params['cover_image'] . ');' : '';

	$hide_avatar_style = '';

	// Adjust the cover image header, in case avatars are completely disabled.
	if ( ! profiles()->avatar->show_avatars ) {
		$hide_avatar_style = '
			#profiles #item-header-cover-image #item-header-avatar {
				display:  none;
			}
		';

		if ( profiles_is_user() ) {
			$hide_avatar_style = '
				#profiles #item-header-cover-image #item-header-avatar a {
					display: block;
					height: ' . $top_offset . 'px;
					margin: 0 15px 19px 0;
				}

				#profiles div#item-header #item-header-cover-image #item-header-content {
					margin-left: auto;
				}
			';
		}
	}

	return '
		/* Cover image */
		#profiles #header-cover-image {
			height: ' . $params["height"] . 'px;
			' . $cover_image . '
		}

		#profiles #create-group-form #header-cover-image {
			margin: 1em 0;
			position: relative;
		}

		.profiles-user #profiles #item-header {
			padding-top: 0;
		}

		#profiles #item-header-cover-image #item-header-avatar {
			margin-top: '. $avatar_offset .'px;
			float: left;
			overflow: visible;
			width: auto;
		}

		#profiles div#item-header #item-header-cover-image #item-header-content {
			clear: both;
			float: left;
			margin-left: ' . $left_offset . 'px;
			margin-top: -' . $top_offset . 'px;
			width: auto;
		}

		body.single-item.groups #profiles div#item-header #item-header-cover-image #item-header-content,
		body.single-item.groups #profiles div#item-header #item-header-cover-image #item-actions {
			clear: none;
			margin-top: ' . $params["height"] . 'px;
			margin-left: 0;
			max-width: 50%;
		}

		body.single-item.groups #profiles div#item-header #item-header-cover-image #item-actions {
			max-width: 20%;
			padding-top: 20px;
		}

		' . $hide_avatar_style . '

		#profiles div#item-header-cover-image .user-nicename a,
		#profiles div#item-header-cover-image .user-nicename {
			font-size: 200%;
			color: #fff;
			margin: 0 0 0.6em;
			text-rendering: optimizelegibility;
			text-shadow: 0 0 3px rgba( 0, 0, 0, 0.8 );
		}

		#profiles #item-header-cover-image #item-header-avatar img.avatar {
			background: rgba( 255, 255, 255, 0.8 );
			border: solid 2px #fff;
		}

		#profiles #item-header-cover-image #item-header-avatar a {
			border: 0;
			text-decoration: none;
		}

		#profiles #item-header-cover-image #item-buttons {
			margin: 0 0 10px;
			padding: 0 0 5px;
		}

		#profiles #item-header-cover-image #item-buttons:after {
			clear: both;
			content: "";
			display: table;
		}

		@media screen and (max-width: 782px) {
			#profiles #item-header-cover-image #item-header-avatar,
			.profiles-user #profiles #item-header #item-header-cover-image #item-header-avatar,
			#profiles div#item-header #item-header-cover-image #item-header-content {
				width: 100%;
				text-align: center;
			}

			#profiles #item-header-cover-image #item-header-avatar a {
				display: inline-block;
			}

			#profiles #item-header-cover-image #item-header-avatar img {
				margin: 0;
			}

			#profiles div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #profiles div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #profiles div#item-header #item-header-cover-image #item-actions {
				margin: 0;
			}

			body.single-item.groups #profiles div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #profiles div#item-header #item-header-cover-image #item-actions {
				max-width: 100%;
			}

			#profiles div#item-header-cover-image h2 a,
			#profiles div#item-header-cover-image h2 {
				color: inherit;
				text-shadow: none;
				margin: 25px 0 0;
				font-size: 200%;
			}

			#profiles #item-header-cover-image #item-buttons div {
				float: none;
				display: inline-block;
			}

			#profiles #item-header-cover-image #item-buttons:before {
				content: "";
			}

			#profiles #item-header-cover-image #item-buttons {
				margin: 5px 0;
			}
		}
	';
}
