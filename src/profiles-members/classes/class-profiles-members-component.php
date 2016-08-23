<?php
/**
 * Profiles Member Loader.
 *
 * @package Profiles
 * @suprofilesackage Members
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the Profiles Members Component.
 *
 * @since 1.5.0
 */
class Profiles_Members_Component extends Profiles_Component {

	/**
	 * Member types.
	 *
	 * @see profiles_register_member_type()
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $types = array();

	/**
	 * Start the members component creation process.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {
		parent::start(
			'members',
			__( 'Members', 'profiles' ),
			profiles()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 20,
				'search_query_arg' => 'members_search',
			)
		);
	}

	/**
	 * Include profiles-members files.
	 *
	 * @since 1.5.0
	 *
	 * @see Profiles_Component::includes() for description of parameters.
	 *
	 * @param array $includes See {@link Profiles_Component::includes()}.
	 */
	public function includes( $includes = array() ) {

		// Always include these files.
		$includes = array(
			'actions',
			'filters',
			'screens',
			'template',
			'adminbar',
			'functions',
			'widgets',
			'cache',
		);

		if ( ! profiles()->do_autoload ) {
			$includes[] = 'classes';
		}

		// Include these only if in admin.
		if ( is_admin() ) {
			$includes[] = 'admin';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up profiles-members global settings.
	 *
	 * The Profiles_MEMBERS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5.0
	 *
	 * @see Profiles_Component::setup_globals() for description of parameters.
	 *
	 * @param array $args See {@link Profiles_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		global $wpdb;

		$profiles = profiles();

		/** Component Globals ************************************************
		 */

		// Define a slug, as a fallback for backpat.
		if ( !defined( 'Profiles_MEMBERS_SLUG' ) ) {
			define( 'Profiles_MEMBERS_SLUG', $this->id );
		}

		// Override any passed args.
		$args = array(
			'slug'            => Profiles_MEMBERS_SLUG,
			'root_slug'       => isset( $profiles->pages->members->slug ) ? $profiles->pages->members->slug : Profiles_MEMBERS_SLUG,
			'has_directory'   => true,
			'directory_title' => _x( 'Members', 'component directory title', 'profiles' ),
			'search_string'   => __( 'Search Members...', 'profiles' ),
			'global_tables'   => array(
				'table_name_last_activity' => profiles_core_get_table_prefix() . 'profiles_activity',
				'table_name_signups'       => $wpdb->base_prefix . 'signups', // Signups is a global WordPress table.
			)
		);

		parent::setup_globals( $args );

		/** Logged in user ***************************************************
		 */

		// The core userdata of the user who is currently logged in.
		$profiles->loggedin_user->userdata       = profiles_core_get_core_userdata( profiles_loggedin_user_id() );

		// Fetch the full name for the logged in user.
		$profiles->loggedin_user->fullname       = isset( $profiles->loggedin_user->userdata->display_name ) ? $profiles->loggedin_user->userdata->display_name : '';

		// Hits the DB on single WP installs so get this separately.
		$profiles->loggedin_user->is_super_admin = $profiles->loggedin_user->is_site_admin = is_super_admin( profiles_loggedin_user_id() );

		// The domain for the user currently logged in. eg: http://example.com/members/andy.
		$profiles->loggedin_user->domain         = profiles_core_get_user_domain( profiles_loggedin_user_id() );

		/** Displayed user ***************************************************
		 */

		// The core userdata of the user who is currently being displayed.
		$profiles->displayed_user->userdata = profiles_core_get_core_userdata( profiles_displayed_user_id() );

		// Fetch the full name displayed user.
		$profiles->displayed_user->fullname = isset( $profiles->displayed_user->userdata->display_name ) ? $profiles->displayed_user->userdata->display_name : '';

		// The domain for the user currently being displayed.
		$profiles->displayed_user->domain   = profiles_core_get_user_domain( profiles_displayed_user_id() );

		// Initialize the nav for the members component.
		$this->nav = new Profiles_Core_Nav();

		// If A user is displayed, check if there is a front template
		if ( profiles_get_displayed_user() ) {
			$profiles->displayed_user->front_template = profiles_displayed_user_get_front_template();
		}

		/** Signup ***********************************************************
		 */

		$profiles->signup = new stdClass;

		/** Profiles Fallback ************************************************
		 */

		if ( ! profiles_is_active( 'xprofile' ) ) {
			$profiles->profile       = new stdClass;
			$profiles->profile->slug = 'profile';
			$profiles->profile->id   = 'profile';
		}
	}

	/**
	 * Set up canonical stack for this component.
	 *
	 * @since 2.1.0
	 */
	public function setup_canonical_stack() {
		$profiles = profiles();

		/** Default Profile Component ****************************************
		 */
		if ( profiles_displayed_user_has_front_template() ) {
			$profiles->default_component = 'front';
		} elseif ( defined( 'Profiles_DEFAULT_COMPONENT' ) && Profiles_DEFAULT_COMPONENT ) {
			$profiles->default_component = Profiles_DEFAULT_COMPONENT;
		} else {
			$profiles->default_component = ( 'xprofile' === $profiles->profile->id ) ? 'profile' : $profiles->profile->id;
		}

		/** Canonical Component Stack ****************************************
		 */

		if ( profiles_displayed_user_id() ) {
			$profiles->canonical_stack['base_url'] = profiles_displayed_user_domain();

			if ( profiles_current_component() ) {
				$profiles->canonical_stack['component'] = profiles_current_component();
			}

			if ( profiles_current_action() ) {
				$profiles->canonical_stack['action'] = profiles_current_action();
			}

			if ( !empty( $profiles->action_variables ) ) {
				$profiles->canonical_stack['action_variables'] = profiles_action_variables();
			}

			// Looking at the single member root/home, so assume the default.
			if ( ! profiles_current_component() ) {
				$profiles->current_component = $profiles->default_component;

			// The canonical URL will not contain the default component.
			} elseif ( profiles_is_current_component( $profiles->default_component ) && ! profiles_current_action() ) {
				unset( $profiles->canonical_stack['component'] );
			}

			// If we're on a spammer's profile page, only users with the 'profiles_moderate' cap
			// can view suprofilesages on the spammer's profile.
			//
			// users without the cap trying to access a spammer's subnav page will get
			// redirected to the root of the spammer's profile page.  this occurs by
			// by removing the component in the canonical stack.
			if ( profiles_is_user_spammer( profiles_displayed_user_id() ) && ! profiles_current_user_can( 'profiles_moderate' ) ) {
				unset( $profiles->canonical_stack['component'] );
			}
		}
	}

	/**
	 * Set up fall-back component navigation if XProfile is inactive.
	 *
	 * @since 1.5.0
	 *
	 * @see Profiles_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See Profiles_Component::setup_nav() for
	 *                        description.
	 * @param array $sub_nav  Optional. See Profiles_Component::setup_nav() for
	 *                        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Don't set up navigation if there's no member.
		if ( ! is_user_logged_in() && ! profiles_is_user() ) {
			return;
		}

		$is_xprofile_active = profiles_is_active( 'xprofile' );

		// Bail if XProfile component is active and there's no custom front page for the user.
		if ( ! profiles_displayed_user_has_front_template() && $is_xprofile_active ) {
			return;
		}

		// Determine user to use.
		if ( profiles_displayed_user_domain() ) {
			$user_domain = profiles_displayed_user_domain();
		} elseif ( profiles_loggedin_user_domain() ) {
			$user_domain = profiles_loggedin_user_domain();
		} else {
			return;
		}

		// Set slug to profile in case the xProfile component is not active
		$slug = profiles_get_profile_slug();

		// Defaults to empty navs
		$this->main_nav = array();
		$this->sub_nav  = array();

		if ( ! $is_xprofile_active ) {
			$this->main_nav = array(
				'name'                => _x( 'Profile', 'Member profile main navigation', 'profiles' ),
				'slug'                => $slug,
				'position'            => 20,
				'screen_function'     => 'profiles_members_screen_display_profile',
				'default_subnav_slug' => 'public',
				'item_css_id'         => profiles()->profile->id
			);
		}

		/**
		 * Setup the subnav items for the member profile.
		 *
		 * This is required in case there's a custom front or in case the xprofile component
		 * is not active.
		 */
		$this->sub_nav = array(
			'name'            => _x( 'View', 'Member profile view', 'profiles' ),
			'slug'            => 'public',
			'parent_url'      => trailingslashit( $user_domain . $slug ),
			'parent_slug'     => $slug,
			'screen_function' => 'profiles_members_screen_display_profile',
			'position'        => 10
		);

		/**
		 * If there's a front template the members component nav
		 * will be there to display the user's front page.
		 */
		if ( profiles_displayed_user_has_front_template() ) {
			$main_nav = array(
				'name'                => _x( 'Home', 'Member Home page', 'profiles' ),
				'slug'                => 'front',
				'position'            => 5,
				'screen_function'     => 'profiles_members_screen_display_profile',
				'default_subnav_slug' => 'public',
			);

			// We need a dummy subnav for the front page to load.
			$front_subnav = $this->sub_nav;
			$front_subnav['parent_slug'] = 'front';

			// In case the subnav is displayed in the front template
			$front_subnav['parent_url'] = trailingslashit( $user_domain . 'front' );

			// Set the subnav
			$sub_nav[] = $front_subnav;

			/**
			 * If the profile component is not active, we need to create a new
			 * nav to display the WordPress profile.
			 */
			if ( ! $is_xprofile_active ) {
				add_action( 'profiles_members_setup_nav', array( $this, 'setup_profile_nav' ) );
			}

		/**
		 * If there's no front template and xProfile is not active, the members
		 * component nav will be there to display the WordPress profile
		 */
		} else {
			$main_nav  = $this->main_nav;
			$sub_nav[] = $this->sub_nav;
		}


		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up a profile nav in case the xProfile
	 * component is not active and a front template is
	 * used.
	 *
	 * @since 2.6.0
	 */
	public function setup_profile_nav() {
		if ( empty( $this->main_nav ) || empty( $this->sub_nav ) ) {
			return;
		}

		// Add the main nav
		profiles_core_new_nav_item( $this->main_nav, 'members' );

		// Add the sub nav item.
		profiles_core_new_subnav_item( $this->sub_nav, 'members' );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since 1.5.0
	 */
	public function setup_title() {
		$profiles = profiles();

		if ( profiles_is_my_profile() ) {
			$profiles->profiles_options_title = __( 'You', 'profiles' );
		} elseif ( profiles_is_user() ) {
			$profiles->profiles_options_title  = profiles_get_displayed_user_fullname();
			$profiles->profiles_options_avatar = profiles_core_fetch_avatar( array(
				'item_id' => profiles_displayed_user_id(),
				'type'    => 'thumb',
				'alt'     => sprintf( __( 'Profile picture of %s', 'profiles' ), $profiles->profiles_options_title )
			) );
		}

		parent::setup_title();
	}

	/**
	 * Setup cache groups.
	 *
	 * @since 2.2.0
	 */
	public function setup_cache_groups() {

		// Global groups.
		wp_cache_add_global_groups( array(
			'profiles_last_activity',
			'profiles_member_type'
		) );

		parent::setup_cache_groups();
	}
}
