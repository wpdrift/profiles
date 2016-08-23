<?php
/**
 * Profiles Filters & Actions.
 *
 * This file contains the actions and filters that are used through-out Profiles.
 * They are consolidated here to make searching for them easier, and to help
 * developers understand at a glance the order in which things occur.
 *
 * @package Profiles
 * @suprofilesackage Hooks
 * @since 0.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attach Profiles to WordPress.
 *
 * Profiles uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when Profiles is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *           v--WordPress Actions       v--Profiles Sub-actions
  */
add_action( 'plugins_loaded',          'profiles_loaded',                 10    );
add_action( 'init',                    'profiles_init',                   10    );
add_action( 'rest_api_init',           'profiles_rest_api_init',          20    ); // After WP core.
add_action( 'customize_register',      'profiles_customize_register',     20    ); // After WP core.
add_action( 'parse_query',             'profiles_parse_query',            2     ); // Early for overrides.
add_action( 'wp',                      'profiles_ready',                  10    );
add_action( 'set_current_user',        'profiles_setup_current_user',     10    );
add_action( 'setup_theme',             'profiles_setup_theme',            10    );
add_action( 'after_setup_theme',       'profiles_after_setup_theme',      100   ); // After WP themes.
add_action( 'wp_enqueue_scripts',      'profiles_enqueue_scripts',        10    );
add_action( 'enqueue_embed_scripts',   'profiles_enqueue_embed_scripts',  10    );
add_action( 'admin_bar_menu',          'profiles_setup_admin_bar',        20    ); // After WP core.
add_action( 'template_redirect',       'profiles_template_redirect',      10    );
add_action( 'widgets_init',            'profiles_widgets_init',           10    );
add_action( 'generate_rewrite_rules',  'profiles_generate_rewrite_rules', 10    );

/**
 * The profiles_loaded hook - Attached to 'plugins_loaded' above.
 *
 * Attach various loader actions to the profiles_loaded action.
 * The load order helps to execute code at the correct time.
 *                                                      v---Load order
 */
add_action( 'profiles_loaded', 'profiles_setup_components',         2  );
add_action( 'profiles_loaded', 'profiles_include',                  4  );
add_action( 'profiles_loaded', 'profiles_setup_cache_groups',       5  );
add_action( 'profiles_loaded', 'profiles_setup_widgets',            6  );
add_action( 'profiles_loaded', 'profiles_register_member_types',    8  );
add_action( 'profiles_loaded', 'profiles_register_theme_packages',  12 );
add_action( 'profiles_loaded', 'profiles_register_theme_directory', 14 );

/**
 * The profiles_init hook - Attached to 'init' above.
 *
 * Attach various initialization actions to the profiles_init action.
 * The load order helps to execute code at the correct time.
 *                                                   v---Load order
 */
add_action( 'profiles_init', 'profiles_core_set_uri_globals',    2  );
add_action( 'profiles_init', 'profiles_register_post_types',     3  );
add_action( 'profiles_init', 'profiles_register_taxonomies',     3  );
add_action( 'profiles_init', 'profiles_setup_globals',           4  );
add_action( 'profiles_init', 'profiles_setup_canonical_stack',   5  );
add_action( 'profiles_init', 'profiles_setup_nav',               6  );
add_action( 'profiles_init', 'profiles_setup_title',             8  );
add_action( 'profiles_init', 'profiles_core_load_admin_bar_css', 12 );
add_action( 'profiles_init', 'profiles_add_rewrite_tags',        20 );
add_action( 'profiles_init', 'profiles_add_rewrite_rules',       30 );
add_action( 'profiles_init', 'profiles_add_permastructs',        40 );

/**
 * The profiles_template_redirect hook - Attached to 'template_redirect' above.
 *
 * Attach various template actions to the profiles_template_redirect action.
 * The load order helps to execute code at the correct time.
 *
 * Note that we currently use template_redirect versus template include because
 * Profiles is a bully and overrides the existing themes output in many
 * places. This won't always be this way, we promise.
 *                                                           v---Load order
 */
add_action( 'profiles_template_redirect', 'profiles_redirect_canonical', 2  );
add_action( 'profiles_template_redirect', 'profiles_actions',            4  );
add_action( 'profiles_template_redirect', 'profiles_screens',            6  );
add_action( 'profiles_template_redirect', 'profiles_post_request',       10 );
add_action( 'profiles_template_redirect', 'profiles_get_request',        10 );

/**
 * Add the Profiles functions file and the Theme Compat Default features.
 */
add_action( 'profiles_after_setup_theme', 'profiles_load_theme_functions',                    1 );
add_action( 'profiles_after_setup_theme', 'profiles_register_theme_compat_default_features', 10 );

// Load the admin.
if ( is_admin() ) {
	add_action( 'profiles_loaded', 'profiles_admin' );
}

// Activation redirect.
add_action( 'profiles_activation', 'profiles_add_activation_redirect' );

// Email unsubscribe.
add_action( 'profiles_get_request_unsubscribe', 'profiles_email_unsubscribe_handler' );
