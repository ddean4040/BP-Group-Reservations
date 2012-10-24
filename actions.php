<?php

add_action( 'bp_core_activated_user', 'bp_group_reservation_activate_reservations', 10, 3 );

/**
 * Activate reservations when a user activates his/her account
 */
function bp_group_reservation_activate_reservations( $user_id, $key, $user ) {
	
	// Just to be sure - many login integration scripts omit this
	wp_set_current_user( $user_id );
	
	$user_name = apply_filters( 'bp_group_reservation_search_key', $user->user_email, $user );
	
	$reservations = bp_group_reservation_get_by_user( $user_name );
	
	foreach( $reservations as $reservation ) {
		
		if( ! groups_join_group( (int)$reservation['group_id'], $user_id ) ) {
			wp_die( 'Error joining user: ' . $user_id . ' to group: ' . (int)$reservation['group_id'] );
		}
		
		// If the user has a 'level' attribute of 'mod' or 'admin', promote him/her
		if( isset( $reservation['extras']['level'] ) && in_array( $reservation['extras']['level'], array( 'mod', 'admin' ) ) ) {
			groups_promote_member( $user_id, (int)$reservation['group_id'], $reservation['extras']['level'] );
		}
		
		foreach( $reservation['extras'] as $extra => $value ) {
			do_action( 'bp_group_reservation_activate_extra_' . $extra , $extra, $value, $user );
		}
		
		bp_group_reservation_delete_by_group( (int)$reservation['group_id'], $user_name );
			
	}
}


?>