<?php
/**
 * Caching functions specific to Profiles Members.
 *
 * @package Profiles
 * @suprofilesackage MembersCache
 * @since 2.2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Pre-fetch member type data when initializing a Members loop.
 *
 * @since 2.2.0
 *
 * @param Profiles_User_Query $profiles_user_query Profiles_User_Query object.
 */
function profiles_members_prefetch_member_type( Profiles_User_Query $profiles_user_query ) {
	$uncached_member_ids = profiles_get_non_cached_ids( $profiles_user_query->user_ids, 'profiles_member_member_type' );

	$member_types = profiles_get_object_terms( $uncached_member_ids, 'profiles_member_type', array(
		'fields' => 'all_with_object_id',
	) );

	// Rekey by user ID.
	$keyed_member_types = array();
	foreach ( $member_types as $member_type ) {
		if ( ! isset( $keyed_member_types[ $member_type->object_id ] ) ) {
			$keyed_member_types[ $member_type->object_id ] = array();
		}

		$keyed_member_types[ $member_type->object_id ][] = $member_type->name;
	}

	$cached_member_ids = array();
	foreach ( $keyed_member_types as $user_id => $user_member_types ) {
		wp_cache_set( $user_id, $user_member_types, 'profiles_member_member_type' );
		$cached_member_ids[] = $user_id;
	}

	// Cache an empty value for users with no type.
	foreach ( array_diff( $uncached_member_ids, $cached_member_ids ) as $no_type_id ) {
		wp_cache_set( $no_type_id, '', 'profiles_member_member_type' );
	}
}
add_action( 'profiles_user_query_populate_extras', 'profiles_members_prefetch_member_type' );

/**
 * Clear the member_type cache for a user.
 *
 * Called when the user is deleted or marked as spam.
 *
 * @since 2.2.0
 *
 * @param int $user_id ID of the deleted user.
 */
function profiles_members_clear_member_type_cache( $user_id ) {
	wp_cache_delete( $user_id, 'profiles_member_member_type' );
}
add_action( 'wpmu_delete_user', 'profiles_members_clear_member_type_cache' );
add_action( 'delete_user', 'profiles_members_clear_member_type_cache' );
