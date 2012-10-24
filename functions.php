<?php
/**
 * Functions
 */

/**
 * Reserve space for an unregistered user in a specified group
 * 
 * @param string User Email Email address (or other unique identifier) of the unregistered user
 * @param int Group ID ID of the group in which to create the reservation
 * @param array Extras (optional) any attributes of the reservation
 * 
 * @return boolean TRUE on successful creation, FALSE on failure
 */
function bp_group_reservation_create_reservation( $user_email, $group_id, $extras = false ) {
	
	// Only allow admins and group admins to create reservations
	if( ! is_super_admin() && ! groups_is_user_admin( bp_loggedin_user_id(), $group_id ) ) {
		return false;
	}
	
	$meta_name = 'group_reservation_' . md5( strtolower( $user_email ) );
	return groups_update_groupmeta( $group_id, $meta_name, $extras );
}

/**
 * Retrieve all reservations for the specified group
 */
function bp_group_reservation_get_by_group( $group_id ) {
	global $bp, $wpdb;
	
	$meta_name_format = 'group_reservation_%';
	$reservations = $wpdb->get_col( $wpdb->prepare( 'SELECT meta_value FROM ' . $bp->groups->table_name_groupmeta . ' WHERE group_id = %d AND meta_key LIKE %s', $group_id, $meta_name_format ) );
	foreach( $reservations as $key => $reservation ) {
		$reservations[$key] = array(
			'group_id'	=> (int)$group_id,
			'extras'	=> apply_filters( '', maybe_unserialize( $reservation ) )
		);
	}
	
	return $reservations;
}

/**
 * Retrieve all reservations for the specified user
 */
function bp_group_reservation_get_by_user( $user_name ) {
	
	global $bp, $wpdb;
	
	$meta_name = 'group_reservation_' . md5( strtolower( $user_name ) );
	$reservation_results = $wpdb->get_results( $wpdb->prepare( 'SELECT group_id, meta_value FROM ' . $bp->groups->table_name_groupmeta . ' WHERE meta_key = %s', $meta_name ) );
	
	if( ! $reservation_results ) {
		// Check case-sensitive as a backup, for 1.0-compatibility
		$meta_name = 'group_reservation_' . md5( $user_name );
		$reservation_results = $wpdb->get_results( $wpdb->prepare( 'SELECT group_id, meta_value FROM ' . $bp->groups->table_name_groupmeta . ' WHERE meta_key = %s', $meta_name ) );
	}
	
	if( ! $reservation_results ) return false;
	
	$reservations = array();
	
	foreach( $reservation_results as $reservation ) {
		$reservations[] = array(
			'group_id'	=> (int)$reservation->group_id,
			'extras'	=> maybe_unserialize( $reservation->meta_value )
		);
	}
	
	return $reservations;
}

/**
 * Delete all reservations attached to a specified group (optionally only those attached to a particular user)
 * 
 * @param int Group ID ID of the group whose reservations are to be deleted
 * @param string User Name (optional) restrict deletion to a particular user
 * 
 * @return boolean TRUE for success, FALSE for failure
 * 
 */
function bp_group_reservation_delete_by_group( $group_id, $user_name = false ) {
	
	global $bp, $wpdb, $current_user;

	$group_id = (int)$group_id;
	
	// Only allow admins and group admins to delete reservations except one's own
	if( ! is_super_admin() && ! groups_is_user_admin( bp_loggedin_user_id(), $group_id ) ) {
		if( ! $user_name ) {
			return false;
		}
		
		$userdata = get_userdata( $current_user->ID );
		
		if( strcasecmp( $userdata->user_email, $user_name ) != 0 ) {
			return false;
		}
	}
	
	if( ! empty( $user_name ) ) {

		$meta_name = 'group_reservation_' . md5( strtolower( $user_name ) );
		if( ! groups_delete_groupmeta( $group_id, $meta_name ) ) return false;
		
		// Also delete a reservation with uppercase letters for 1.0-compatibility
		$meta_name = 'group_reservation_' . md5( $user_name );
		if( ! groups_delete_groupmeta( $group_id, $meta_name ) ) return false;
		
		return true;
	}
	
	$meta_name_format = 'group_reservation_%';
	if( ! $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $bp->groups->table_name_groupmeta . ' WHERE group_id = %d AND meta_key LIKE %s', $group_id, $meta_name_format ) ) ) {
		$wpdb->print_error();
	}
	return true;
	
}

/**
 * 
 */
function bp_group_reservation_delete_by_user( $user_name ) {

	global $bp, $wpdb;
	
	if( ! is_super_admin() )	return false;

	$meta_name = 'group_reservation_' . md5( strtolower( $user_name ) );
	if( ! $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $bp->groups->table_name_groupmeta . ' WHERE meta_key = %s', $meta_name ) ) ) {
		$wpdb->print_error();
	}
	return true;
	
	
}

?>