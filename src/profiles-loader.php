<?php
/**
 * The Profiles Plugin.
 *
 * Profiles is built on the solid foundation of the BuddyPress plugin. 
 * We decided to fork this project to provide a more generic Profiles 
 * plugin that could be easily extended for more specific use-cases.
 *
 * @package Profiles
 * @suprofilesackage Main
 * @since 0.0.1
 */

/**
 * Plugin Name: Profiles
 * Plugin URI:  https://wordpress.org/plugins/profiles/
 * Description: Profiles is built on the solid foundation of the Profiles plugin. We decided to fork this project to provide a more generic Profiles plugin that could be easily extended for more specific use-cases.
 * Author:      OpenTute+
 * Author URI:  http://opentuteplus.com/
 * Version:     0.0.1
 * Text Domain: profiles
 * Domain Path: /profiles-languages/
 * License:     GPLv3 or later (license.txt)
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Constants *****************************************************************/

if ( !class_exists( 'Profiles' ) ) :
/**
 * Main Profiles Class.
 *
 * Tap tap tap... Is this thing on?
 *
 * @since 1.6.0
 */
class Profiles {

	/** Magic *****************************************************************/

	/**
	 * Profiles uses many variables, most of which can be filtered to
	 * customize the way that it works. To prevent unauthorized access,
	 * these variables are stored in a private array that is magically
	 * updated using PHP 5.2+ methods. This is to prevent third party
	 * plugins from tampering with essential information indirectly, which
	 * would cause issues later.
	 *
	 * @see Profiles::setup_globals()
	 * @var array
	 */
	private $data;

	/** Not Magic *************************************************************/

	/**
	 * @var array Primary Profiles navigation.
	 */
	public $profiles_nav = array();

	/**
	 * @var array Secondary Profiles navigation to $profiles_nav.
	 */
	public $profiles_options_nav = array();

	/**
	 * @var array The unfiltered URI broken down into chunks.
	 * @see profiles_core_set_uri_globals()
	 */
	public $unfiltered_uri = array();

	/**
	 * @var array The canonical URI stack.
	 * @see profiles_redirect_canonical()
	 * @see profiles_core_new_nav_item()
	 */
	public $canonical_stack = array();

	/**
	 * @var array Additional navigation elements (supplemental).
	 */
	public $action_variables = array();

	/**
	 * @var string Current member directory type.
	 */
	public $current_member_type = '';

	/**
	 * @var array Required components (core, members).
	 */
	public $required_components = array();

	/**
	 * @var array Additional active components.
	 */
	public $loaded_components = array();

	/**
	 * @var array Active components.
	 */
	public $active_components = array();

	/**
	 * Whether autoload is in use.
	 *
	 * @since 2.5.0
	 * @var bool
	 */
	public $do_autoload = false;

	/**
	 * Whether to load backward compatibility classes for navigation globals.
	 *
	 * @since 2.6.0
	 * @var bool
	 */
	public $do_nav_backcompat = false;

	/** Option Overload *******************************************************/

	/**
	 * @var array Optional Overloads default options retrieved from get_option().
	 */
	public $options = array();

	/** Singleton *************************************************************/

	/**
	 * Main Profiles Instance.
	 *
	 * Profiles is great.
	 * Please load it only one time.
	 * For this, we thank you.
	 *
	 * Insures that only one instance of Profiles exists in memory at any
	 * one time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.7.0
	 *
	 * @static object $instance
	 * @see profiles()
	 *
	 * @return Profiles The one true Profiles.
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if ( null === $instance ) {
			$instance = new Profiles;
			$instance->constants();
			$instance->setup_globals();
			$instance->legacy_constants();
			$instance->includes();
			$instance->setup_actions();
		}

		// Always return the instance
		return $instance;

		// The last metroid is in captivity. The galaxy is at peace.
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent Profiles from being loaded more than once.
	 *
	 * @since 1.7.0
	 * @see Profiles::instance()
	 * @see profiles()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent Profiles from being cloned.
	 *
	 * @since 1.7.0
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'profiles' ), '1.7' ); }

	/**
	 * A dummy magic method to prevent Profiles from being unserialized.
	 *
	 * @since 1.7.0
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'profiles' ), '1.7' ); }

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since 1.7.0
	 *
	 * @param string $key Key to check the set status for.
	 *
	 * @return bool
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting Profiles variables.
	 *
	 * @since 1.7.0
	 *
	 * @param string $key Key to return the value for.
	 *
	 * @return mixed
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting Profiles variables.
	 *
	 * @since 1.7.0
	 *
	 * @param string $key   Key to set a value for.
	 * @param mixed  $value Value to set.
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method for unsetting Profiles variables.
	 *
	 * @since 1.7.0
	 *
	 * @param string $key Key to unset a value for.
	 */
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls.
	 *
	 * @since 1.7.0
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return null
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods *******************************************************/

	/**
	 * Bootstrap constants.
	 *
	 * @since 1.6.0
	 *
	 */
	private function constants() {

		// Place your custom code (actions/filters) in a file called
		// '/plugins/profiles-custom.php' and it will be loaded before anything else.
		if ( file_exists( WP_PLUGIN_DIR . '/profiles-custom.php' ) ) {
			require( WP_PLUGIN_DIR . '/profiles-custom.php' );
		}

		// Path and URL
		if ( ! defined( 'Profiles_PLUGIN_DIR' ) ) {
			define( 'Profiles_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'Profiles_PLUGIN_URL' ) ) {
			define( 'Profiles_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Only applicable to those running trunk
		if ( ! defined( 'Profiles_SOURCE_SUBDIRECTORY' ) ) {
			define( 'Profiles_SOURCE_SUBDIRECTORY', '' );
		}

		// Define on which blog ID Profiles should run
		if ( ! defined( 'Profiles_ROOT_BLOG' ) ) {

			// Default to use current blog ID
			// Fulfills non-network installs and Profiles_ENABLE_MULTIBLOG installs
			$root_blog_id = get_current_blog_id();

			// Multisite check
			if ( is_multisite() ) {

				// Multiblog isn't enabled
				if ( ! defined( 'Profiles_ENABLE_MULTIBLOG' ) || ( defined( 'Profiles_ENABLE_MULTIBLOG' ) && (int) constant( 'Profiles_ENABLE_MULTIBLOG' ) === 0 ) ) {
					// Check to see if BP is network-activated
					// We're not using is_plugin_active_for_network() b/c you need to include the
					// /wp-admin/includes/plugin.php file in order to use that function.

					// get network-activated plugins
					$plugins = get_site_option( 'active_sitewide_plugins');

					// basename
					$basename = basename( constant( 'Profiles_PLUGIN_DIR' ) ) . '/profiles-loader.php';

					// plugin is network-activated; use main site ID instead
					if ( isset( $plugins[ $basename ] ) ) {
						$current_site = get_current_site();
						$root_blog_id = $current_site->blog_id;
					}
				}

			}

			define( 'Profiles_ROOT_BLOG', $root_blog_id );
		}

		// Whether to refrain from loading deprecated functions
		if ( ! defined( 'Profiles_IGNORE_DEPRECATED' ) ) {
			define( 'Profiles_IGNORE_DEPRECATED', false );
		}

		// The search slug has to be defined nice and early because of the way
		// search requests are loaded
		//
		// @todo Make this better
		if ( ! defined( 'Profiles_SEARCH_SLUG' ) ) {
			define( 'Profiles_SEARCH_SLUG', 'search' );
		}
	}

	/**
	 * Component global variables.
	 *
	 * @since 1.6.0
	 *
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version    = '2.7-alpha';
		$this->db_version = 10469;

		/** Loading ***********************************************************/

		/**
		 * Filters the load_deprecated property value.
		 *
		 * @since 2.0.0
		 *
		 * @const constant Profiles_IGNORE_DEPRECATED Whether or not to ignore deprecated functionality.
		 */
		$this->load_deprecated = ! apply_filters( 'profiles_ignore_deprecated', Profiles_IGNORE_DEPRECATED );

		/** Toolbar ***********************************************************/

		/**
		 * @var string The primary toolbar ID.
		 */
		$this->my_account_menu_id = '';

		/** URIs **************************************************************/

		/**
		 * @var int The current offset of the URI.
		 * @see profiles_core_set_uri_globals()
		 */
		$this->unfiltered_uri_offset = 0;

		/**
		 * @var bool Are status headers already sent?
		 */
		$this->no_status_set = false;

		/** Components ********************************************************/

		/**
		 * @var string Name of the current Profiles component (primary).
		 */
		$this->current_component = '';

		/**
		 * @var string Name of the current Profiles item (secondary).
		 */
		$this->current_item = '';

		/**
		 * @var string Name of the current Profiles action (tertiary).
		 */
		$this->current_action = '';

		/**
		 * @var bool Displaying custom 2nd level navigation menu (I.E a group).
		 */
		$this->is_single_item = false;

		/** Root **************************************************************/

		/**
		 * Filters the Profiles Root blog ID.
		 *
		 * @since 1.5.0
		 *
		 * @const constant Profiles_ROOT_BLOG Profiles Root blog ID.
		 */
		$this->root_blog_id = (int) apply_filters( 'profiles_get_root_blog_id', Profiles_ROOT_BLOG );

		/** Paths**************************************************************/

		// Profiles root directory
		$this->file           = constant( 'Profiles_PLUGIN_DIR' ) . 'profiles-loader.php';
		$this->basename       = basename( constant( 'Profiles_PLUGIN_DIR' ) ) . '/profiles-loader.php';
		$this->plugin_dir     = trailingslashit( constant( 'Profiles_PLUGIN_DIR' ) . constant( 'Profiles_SOURCE_SUBDIRECTORY' ) );
		$this->plugin_url     = trailingslashit( constant( 'Profiles_PLUGIN_URL' ) . constant( 'Profiles_SOURCE_SUBDIRECTORY' ) );

		// Languages
		$this->lang_dir       = $this->plugin_dir . 'profiles-languages';

		// Templates (theme compatibility)
		$this->themes_dir     = $this->plugin_dir . 'profiles-templates';
		$this->themes_url     = $this->plugin_url . 'profiles-templates';

		// Themes (for profiles-default)
		$this->old_themes_dir = $this->plugin_dir . 'profiles-themes';
		$this->old_themes_url = $this->plugin_url . 'profiles-themes';

		/** Theme Compat ******************************************************/

		$this->theme_compat   = new stdClass(); // Base theme compatibility class
		$this->filters        = new stdClass(); // Used when adding/removing filters

		/** Users *************************************************************/

		$this->current_user   = new stdClass();
		$this->displayed_user = new stdClass();

		/** Post types and taxonomies *****************************************/
		$this->email_post_type     = apply_filters( 'profiles_email_post_type', 'profiles-email' );
		$this->email_taxonomy_type = apply_filters( 'profiles_email_tax_type', 'profiles-email-type' );

		/** Navigation backward compatibility *********************************/
		if ( interface_exists( 'ArrayAccess', false ) ) {
			// profiles_nav and profiles_options_nav compatibility depends on SPL.
			$this->do_nav_backcompat = true;
		}
	}

	/**
	 * Legacy Profiles constants.
	 *
	 * Try to avoid using these. Their values have been moved into variables
	 * in the instance, and have matching functions to get/set their values.
	 *
	 * @since 1.7.0
	 */
	private function legacy_constants() {

		// Define the Profiles version
		if ( ! defined( 'Profiles_VERSION' ) ) {
			define( 'Profiles_VERSION', $this->version );
		}

		// Define the database version
		if ( ! defined( 'Profiles_DB_VERSION' ) ) {
			define( 'Profiles_DB_VERSION', $this->db_version );
		}
	}

	/**
	 * Include required files.
	 *
	 * @since 1.6.0
	 *
	 */
	private function includes() {
		if ( function_exists( 'spl_autoload_register' ) ) {
			spl_autoload_register( array( $this, 'autoload' ) );
			$this->do_autoload = true;
		}

		// Load the WP abstraction file so Profiles can run on all WordPress setups.
		require( $this->plugin_dir . 'profiles-core/profiles-core-wpabstraction.php' );

		// Setup the versions (after we include multisite abstraction above)
		$this->versions();

		/** Update/Install ****************************************************/

		// Theme compatibility
		require( $this->plugin_dir . 'profiles-core/profiles-core-template-loader.php'     );
		require( $this->plugin_dir . 'profiles-core/profiles-core-theme-compatibility.php' );

		// Require all of the Profiles core libraries
		require( $this->plugin_dir . 'profiles-core/profiles-core-dependency.php'       );
		require( $this->plugin_dir . 'profiles-core/profiles-core-actions.php'          );
		require( $this->plugin_dir . 'profiles-core/profiles-core-caps.php'             );
		require( $this->plugin_dir . 'profiles-core/profiles-core-cache.php'            );
		require( $this->plugin_dir . 'profiles-core/profiles-core-cssjs.php'            );
		require( $this->plugin_dir . 'profiles-core/profiles-core-update.php'           );
		require( $this->plugin_dir . 'profiles-core/profiles-core-options.php'          );
		require( $this->plugin_dir . 'profiles-core/profiles-core-taxonomy.php'         );
		require( $this->plugin_dir . 'profiles-core/profiles-core-filters.php'          );
		require( $this->plugin_dir . 'profiles-core/profiles-core-attachments.php'      );
		require( $this->plugin_dir . 'profiles-core/profiles-core-avatars.php'          );
		require( $this->plugin_dir . 'profiles-core/profiles-core-widgets.php'          );
		require( $this->plugin_dir . 'profiles-core/profiles-core-template.php'         );
		require( $this->plugin_dir . 'profiles-core/profiles-core-adminbar.php'         );
		require( $this->plugin_dir . 'profiles-core/profiles-core-buddybar.php'         );
		require( $this->plugin_dir . 'profiles-core/profiles-core-catchuri.php'         );
		require( $this->plugin_dir . 'profiles-core/profiles-core-functions.php'        );
		require( $this->plugin_dir . 'profiles-core/profiles-core-moderation.php'       );
		require( $this->plugin_dir . 'profiles-core/profiles-core-loader.php'           );
		require( $this->plugin_dir . 'profiles-core/profiles-core-customizer-email.php' );

		if ( ! $this->do_autoload ) {
			require( $this->plugin_dir . 'profiles-core/profiles-core-classes.php' );
		}
	}

	/**
	 * Autoload classes.
	 *
	 * @since 2.5.0
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {
		$class_parts = explode( '_', strtolower( $class ) );

		if ( 'profiles' !== $class_parts[0] ) {
			return;
		}

		$components = array(
			'activity',
			'core',
			//'groups',
			'members',
			'xprofile',
		);

		// These classes don't have a name that matches their component.
		$irregular_map = array(
			'Profiles_Akismet' => 'activity',

			'Profiles_Admin'                     => 'core',
			'Profiles_Attachment_Avatar'         => 'core',
			'Profiles_Attachment_Cover_Image'    => 'core',
			'Profiles_Attachment'                => 'core',
			'Profiles_Button'                    => 'core',
			'Profiles_Component'                 => 'core',
			'Profiles_Customizer_Control_Range'  => 'core',
			'Profiles_Date_Query'                => 'core',
			'Profiles_Email_Delivery'            => 'core',
			'Profiles_Email_Recipient'           => 'core',
			'Profiles_Email'                     => 'core',
			'Profiles_Embed'                     => 'core',
			'Profiles_Media_Extractor'           => 'core',
			'Profiles_Members_Suggestions'       => 'core',
			'Profiles_PHPMailer'                 => 'core',
			'Profiles_Recursive_Query'           => 'core',
			'Profiles_Suggestions'               => 'core',
			'Profiles_Theme_Compat'              => 'core',
			'Profiles_User_Query'                => 'core',
			'Profiles_Walker_Category_Checklist' => 'core',
			'Profiles_Walker_Nav_Menu_Checklist' => 'core',
			'Profiles_Walker_Nav_Menu'           => 'core',

			//'Profiles_Core_Friends_Widget' => 'friends',

			'Profiles_Group_Extension'    => 'groups',
			'Profiles_Group_Member_Query' => 'groups',

			'Profiles_Core_Members_Template'       => 'members',
			'Profiles_Core_Members_Widget'         => 'members',
			'Profiles_Core_Recently_Active_Widget' => 'members',
			'Profiles_Core_Whos_Online_Widget'     => 'members',
			'Profiles_Registration_Theme_Compat'   => 'members',
			'Profiles_Signup'                      => 'members',
		);

		$component = null;

		// First check to see if the class is one without a properly namespaced name.
		if ( isset( $irregular_map[ $class ] ) ) {
			$component = $irregular_map[ $class ];

		// Next chunk is usually the component name.
		} elseif ( in_array( $class_parts[1], $components, true ) ) {
			$component = $class_parts[1];
		}

		if ( ! $component ) {
			return;
		}

		// Sanitize class name.
		$class = strtolower( str_replace( '_', '-', $class ) );

		$path = dirname( __FILE__ ) . "/profiles-{$component}/classes/class-{$class}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		/*
		 * Sanity check 2 - Check if component is active before loading class.
		 * Skip if PHPUnit is running, or Profiles is installing for the first time.
		 */
		if (
			! in_array( $component, array( 'core', 'members' ), true ) &&
			! profiles_is_active( $component ) &&
			! function_exists( 'tests_add_filter' )
		) {
			return;
		}

		require $path;
	}

	/**
	 * Set up the default hooks and actions.
	 *
	 * @since 1.6.0
	 *
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'profiles_activation'   );
		add_action( 'deactivate_' . $this->basename, 'profiles_deactivation' );

		// If Profiles is being deactivated, do not add any actions
		if ( profiles_is_deactivation( $this->basename ) ) {
			return;
		}

		// Array of Profiles core actions
		$actions = array(
			'setup_theme',              // Setup the default theme compat
			'setup_current_user',       // Setup currently logged in user
			'register_post_types',      // Register post types
			'register_post_statuses',   // Register post statuses
			'register_taxonomies',      // Register taxonomies
			'register_views',           // Register the views
			'register_theme_directory', // Register the theme directory
			'register_theme_packages',  // Register bundled theme packages (profiles-themes)
			'load_textdomain',          // Load textdomain
			'add_rewrite_tags',         // Add rewrite tags
			'generate_rewrite_rules'    // Generate rewrite rules
		);

		// Add the actions
		foreach( $actions as $class_action ) {
			if ( method_exists( $this, $class_action ) ) {
				add_action( 'profiles_' . $class_action, array( $this, $class_action ), 5 );
			}
		}

		/**
		 * Fires after the setup of all Profiles actions.
		 *
		 * Includes bprofiles-core-hooks.php.
		 *
		 * @since 1.7.0
		 *
		 * @param Profiles $this. Current Profiles instance. Passed by reference.
		 */
		do_action_ref_array( 'profiles_after_setup_actions', array( &$this ) );
	}

	/**
	 * Private method to align the active and database versions.
	 *
	 * @since 1.7.0
	 */
	private function versions() {

		// Get the possible DB versions (boy is this gross)
		$versions               = array();
		$versions['1.6-single'] = get_blog_option( $this->root_blog_id, '_profiles_db_version' );

		// 1.6-single exists, so trust it
		if ( !empty( $versions['1.6-single'] ) ) {
			$this->db_version_raw = (int) $versions['1.6-single'];

		// If no 1.6-single exists, use the max of the others
		} else {
			$versions['1.2']        = get_site_option(                      'profiles-core-db-version' );
			$versions['1.5-multi']  = get_site_option(                           'profiles-db-version' );
			$versions['1.6-multi']  = get_site_option(                          '_profiles_db_version' );
			$versions['1.5-single'] = get_blog_option( $this->root_blog_id,      'profiles-db-version' );

			// Remove empty array items
			$versions             = array_filter( $versions );
			$this->db_version_raw = (int) ( !empty( $versions ) ) ? (int) max( $versions ) : 0;
		}
	}

	/** Public Methods ********************************************************/

	/**
	 * Set up Profiles's legacy theme directory.
	 *
	 * Starting with version 1.2, and ending with version 1.8, Profiles
	 * registered a custom theme directory - profiles-themes - which contained
	 * the profiles-default theme. Since Profiles 1.9, profiles-themes is no longer
	 * registered (and profiles-default no longer offered) on new installations.
	 * Sites using profiles-default (or a child theme of profiles-default) will
	 * continue to have profiles-themes registered as before.
	 *
	 * @since 1.5.0
	 *
	 * @todo Move profiles-default to wordpress.org/extend/themes and remove this.
	 */
	public function register_theme_directory() {
		if ( ! profiles_do_register_theme_directory() ) {
			return;
		}

		register_theme_directory( $this->old_themes_dir );
	}

	/**
	 * Register bundled theme packages.
	 *
	 * Note that since we currently have complete control over profiles-themes and
	 * the profiles-legacy folders, it's fine to hardcode these here. If at a
	 * later date we need to automate this, an API will need to be built.
	 *
	 * @since 1.7.0
	 */
	public function register_theme_packages() {

		// Register the default theme compatibility package
		profiles_register_theme_package( array(
			'id'      => 'legacy',
			'name'    => __( 'Profiles Default', 'profiles' ),
			'version' => profiles_get_version(),
			'dir'     => trailingslashit( $this->themes_dir . '/profiles-legacy' ),
			'url'     => trailingslashit( $this->themes_url . '/profiles-legacy' )
		) );

		// Register the basic theme stack. This is really dope.
		profiles_register_template_stack( 'get_stylesheet_directory', 10 );
		profiles_register_template_stack( 'get_template_directory',   12 );
		profiles_register_template_stack( 'profiles_get_theme_compat_dir',  14 );
	}

	/**
	 * Set up the default Profiles theme compatibility location.
	 *
	 * @since 1.7.0
	 */
	public function setup_theme() {

		// Bail if something already has this under control
		if ( ! empty( $this->theme_compat->theme ) ) {
			return;
		}

		// Setup the theme package to use for compatibility
		profiles_setup_theme_compat( profiles_get_theme_package_id() );
	}
}

/**
 * The main function responsible for returning the one true Profiles Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $profiles = profiles(); ?>
 *
 * @return Profiles The one true Profiles Instance.
 */
function profiles() {
	return Profiles::instance();
}

/**
 * Hook Profiles early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before Profiles, to get
 * their actions, filters, and overrides setup without Profiles being in the
 * way.
 */
if ( defined( 'BUDDYPRESS_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'profiles', (int) BUDDYPRESS_LATE_LOAD );

// "And now here's something we hope you'll really like!"
} else {
	$GLOBALS['profiles'] = profiles();
}

endif;
