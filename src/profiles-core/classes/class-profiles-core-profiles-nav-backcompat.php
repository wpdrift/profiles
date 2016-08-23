<?php
/**
 * Backward compatibility for the $profiles->profiles_nav global.
 *
 * @since 2.6.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * profiles_nav backward compatibility class.
 *
 * This class is used to provide backward compatibility for extensions that access and modify
 * the $profiles->profiles_nav global.
 *
 * @since 2.6.0
 */
class Profiles_Core_Profiles_Nav_BackCompat implements ArrayAccess {
	/**
	 * Nav items.
	 *
	 * @since 2.6.0
	 * @access public
	 * @var array
	 */
	public $backcompat_nav = array();

	/**
	 * Component to which nav items belong.
	 *
	 * @since 2.6.0
	 * @access public
	 * @var array
	 */
	public $component;

	/**
	 * Constructor.
	 *
	 * @since 2.6.0
	 *
	 * @param array $backcompat_nav Optional. Array of nav items.
	 */
	public function __construct( $backcompat_nav = array() ) {
		foreach ( $backcompat_nav as $key => $value ) {
			if ( is_array( $value ) ) {
				$this->backcompat_nav[ $key ] = new self( $value );
			} else {
				$this->backcompat_nav[ $key ] = $value;
			}
		}
	}

	/**
	 * Assign a value to the nav array at the specified offset.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $offset Array offset.
	 * @param array $value  Nav item.
	 */
	public function offsetSet( $offset, $value ) {
		_doing_it_wrong(
			'profiles_nav',
			__( 'The profiles_nav and profiles_options_nav globals should not be used directly and are deprecated. Please use the Profiles nav functions instead.', 'profiles' ),
			'2.6.0'
		);

		$profiles = profiles();

		if ( is_array( $value ) ) {
			$value = new self( $value );
		}

		if ( $offset !== null ) {
			// Temporarily set the backcompat_nav.
			$this->backcompat_nav[ $offset ] = $value;

			$args = $this->to_array();
			if ( isset( $args['parent_slug'] ) ) {
				$this->get_component_nav( $args['parent_slug'] )->edit_nav( $args, $args['slug'], $args['parent_slug'] );
			} elseif ( isset( $args['slug'] ) ) {
				$profiles->members->nav->edit_nav( $args, $args['slug'] );
			}
		}
	}

	/**
	 * Get a value of the nav array at the specified offset.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $offset Array offset.
	 * @return Profiles_Core_Profiles_Nav_BackCompat
	 */
	public function offsetGet( $offset ) {
		_doing_it_wrong(
			'profiles_nav',
			__( 'The profiles_nav and profiles_options_nav globals should not be used directly and are deprecated. Please use the Profiles nav functions instead.', 'profiles' ),
			'2.6.0'
		);

//		if ( ! isset( $this->backcompat_nav[ $offset ] ) ) {
			$nav = $this->get_nav( $offset );
			if ( $nav && isset( $nav[ $offset ] ) ) {
				$this->backcompat_nav[ $offset ] = new self( $nav[ $offset ] );
			}
//		}

		return $this->backcompat_nav[ $offset ];
	}

	/**
	 * Check whether nav array has a value at the specified offset.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $offset Array offset.
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		_doing_it_wrong(
			'profiles_nav',
			__( 'The profiles_nav and profiles_options_nav globals should not be used directly and are deprecated. Please use the Profiles nav functions instead.', 'profiles' ),
			'2.6.0'
		);

		if ( isset( $this->backcompat_nav[ $offset ] ) ) {
			return true;
		}

		$nav = $this->get_nav( $offset );
		if ( $nav && isset( $nav[ $offset ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Unset a nav array value at the specified offset.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $offset Array offset.
	 */
	public function offsetUnset( $offset ) {
		_doing_it_wrong(
			'profiles_nav',
			__( 'The profiles_nav and profiles_options_nav globals should not be used directly and are deprecated. Please use the Profiles nav functions instead.', 'profiles' ),
			'2.6.0'
		);

		// For top-level nav items, the backcompat nav hasn't yet been initialized.
		if ( ! isset( $this->backcompat_nav[ $offset ] ) ) {
			profiles()->members->nav->delete_nav( $offset );
			unset( $this->backcompat_nav[ $offset ] );
		}
	}

	/**
	 * Set the component to which the nav belongs.
	 *
	 * @since 2.6.0
	 *
	 * @param string $component
	 */
	public function set_component( $component ) {
		$this->component = $component;
	}

	/**
	 * Get the component to which the a nav item belongs.
	 *
	 * We use the following heuristic to guess, based on an offset, which component the item belongs to:
	 *   - If this is a group, and the offset is the same as the current group's slug, it's a group nav item.
	 *   - Otherwise, it's a member nav item.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $offset Array offset.
	 * @return string
	 */
	public function get_component( $offset = '' ) {
		if ( ! isset( $this->component ) ) {
			if ( profiles_is_active( 'groups' ) && $offset === profiles_get_current_group_slug() ) {
				$this->component = 'groups';
			} else {
				$this->component = 'members';
			}
		}

		return $this->component;
	}

	/**
	 * Reset the cached nav items.
	 *
	 * Called when the nav API removes items from the nav array.
	 *
	 * @since 2.6.0
	 */
	public function reset() {
		$this->backcompat_nav = array();
	}

	/**
	 * Get the nav object corresponding to the specified offset.
	 *
	 * @since 2.6.0
	 *
	 * @param mixed $offset Array offset.
	 * @return bool|array
	 */
	protected function get_nav( $offset ) {
		$profiles = profiles();

		$component_nav = $this->get_component_nav( $offset );
		$primary_nav   = $component_nav->get_primary( array( 'slug' => $offset ), false );

		$nav = array();

		if ( empty( $primary_nav ) ) {
			return $nav;
		}

		foreach ( $primary_nav as $item ) {
			$nav[ $item->slug ] = (array) $item;
		}

		return $nav;
	}

	/**
	 * Get the Profiles_Core_Nav object corresponding to the component, based on a nav item name.
	 *
	 * The way profiles_nav was previously organized makes it impossible to know for sure which component's nav is
	 * being referenced by a given nav item name. We guess in the following manner:
	 *   - If we're looking at a group, and the nav item name (`$offset`) is the same as the slug of the current
	 *     group, we assume that the proper component nav is 'groups'.
	 *   - Otherwise, fall back on 'members'.
	 *
	 * @since 2.6.0
	 *
	 * @param string $offset Nav item name.
	 * @return Profiles_Core_Nav
	 */
	protected function get_component_nav( $offset = '' ) {
		$component = $this->get_component( $offset );

		$profiles = profiles();
		if ( ! isset( $profiles->{$component}->nav ) ) {
			return false;
		}

		return $profiles->{$component}->nav;
	}

	/**
	 * Get the nav data, formatted as a flat array.
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	protected function to_array() {
		return $this->backcompat_nav;
	}
}
