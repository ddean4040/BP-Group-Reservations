<?php

// Load localization files if present
if ( file_exists( dirname( __FILE__ ) . '/' . get_locale() . '.mo' ) )
	load_textdomain( 'bp-group-reservation', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );

// Don't initialize the extension if the Groups component is not enabled
if( ! class_exists( 'BP_Group_Extension' ) ) {
	return;
}

class BP_Group_Reservation_Extension extends BP_Group_Extension {
	
	var $visibility = 'public';
	var $enable_create_step = true;
	var $enable_edit_item   = true;
	
	function __construct() {

		global $bp;
		
		$this->name = __( 'Reservations', 'bp-group-reservation' );
		$this->nav_item_name = $this->name;
		
		$this->slug = BP_GROUP_RESERVATION_SLUG;
		
		$this->create_step_position = 7;
		$this->nav_item_position = 64;

		$this->enable_nav_item = false;
//		$this->enable_nav_item = $this->enable_nav_item();
		
	}
	
	function enable_nav_item() {
		global $bp;
		
		if( is_admin() )	return false;
		if( ! is_object( $bp->groups->current_group ) )	return false;
		
		/** Only display the nav item for admins */
		if ( is_super_admin() || bp_group_is_admin() ) {
			return true;
		}
		return false;
	}
	
	function create_screen() {
		
		global $bp;

		if( ! bp_is_group_creation_step( $this->slug ) ) {
			return false;
		}
		?>
		<label for="reserved_names"><?php _e( 'Reserve a space for:', 'bp-group-reservation' ); ?></label>
		<textarea name="group_reservations[reserved_names]" id="reserved_names" title="<?php _e('Enter the email addresses you want to reserve a place for in this group', 'bp-group-reservation') ?>" placeholder="<?php _e('One address per line', 'bp-group-reservation') ?>"></textarea><br />
		<input type="submit" name="group_reservations[submit]" id="reservation_submit" value="<?php _e( 'Create Reservations', 'bp-group-reservation' ); ?>" />
		<?php
		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}
	
	function create_screen_save() {
		global $bp;
		
		check_admin_referer( 'groups_create_save_' . $this->slug );
		
		if( isset( $_POST['group_reservations']['submit'] ) ) {
	
			$reservations = $this->process_reservations( $_POST['group_reservations']['reserved_names'] );
			
			// Save reservations
			foreach( $reservations as $reservation => $extras ) {
				bp_group_reservation_create_reservation( $reservation, bp_get_current_group_id(), $extras );
			}
			
		}
		
	}
	
	function edit_screen() {

		global $bp;

		if( ! bp_is_group_admin_screen( $this->slug ) ) {
			return false;
		}
		
		if( is_super_admin() || bp_group_is_admin() ) {
			
			// Retrieve all reservations for this group
			$reservations = bp_group_reservation_get_by_group( bp_get_current_group_id() );
			
			// Concatenate into a single list
			$reservation_string = $this->format_reservations( $reservations );
			
			?>
			<label for="reserved_names"><?php _e( 'Reserve a space for:', 'bp-group-reservation' ); ?></label>
			<textarea name="group_reservations[reserved_names]" id="reserved_names" title="<?php _e( 'Enter the email addresses you want to reserve a place for in this group', 'bp-group-reservation' ) ?>" placeholder="<?php _e( 'One address per line', 'bp-group-reservation' ) ?>"><?php echo $reservation_string; ?></textarea><br />
			<input type="submit" name="group_reservations[submit]" id="reservation_submit" value="<?php _e( 'Update Reservations', 'bp-group-reservation' ); ?>" />
			<input type="submit" name="group_reservations[clear]" id="reservation_clear" value="<?php _e( 'Clear Reservations', 'bp-group-reservation' ); ?>" />
			<?php
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
			
		}
	}
	
	function edit_screen_save() {
		global $bp;
		
		if( ! isset( $_POST['group_reservations'] ) ) {
			return false;
		}
		
		check_admin_referer( 'groups_edit_save_' . $this->slug );

		// Delete all existing reservations
		bp_group_reservation_delete_by_group( bp_get_current_group_id() );

		if( isset( $_POST['group_reservations']['submit'] ) ) {
	
			$reservations = $this->process_reservations( $_POST['group_reservations']['reserved_names'] );
			
			// Save new reservations
			foreach( $reservations as $reservation => $extras ) {
				bp_group_reservation_create_reservation( $reservation, bp_get_current_group_id(), $extras );
			}
			
		}
		
	}
	
	function display() {
		global $bp, $groups_template;
		?>
		<?php
	}
	
	/**
	 * Convert raw POST data into an array of usernames / email addresses / whatever to create reservations
	 */
	function process_reservations( $reservation_string ) {
		
		$reservations_in = explode( apply_filters( 'bp_group_reservation_default_record_separator', "\n" ), $reservation_string );
		$reservations_in = array_map( 'trim', $reservations_in );
		
		$reservations_out = array();
		foreach( $reservations_in as $key => $reservation ) {
			
			if( $reservation = apply_filters( '', $reservation ) ) {
				
				// Check for embedded membership level but apply default if none found
				$reservation = explode( apply_filters( 'bp_group_reservation_default_extra_separator', ',' ), $reservation );
				
				$user_name = array_shift( $reservation );
				
				$reservation_attrs = array_map( create_function('$val','return explode("=",trim($val),2);'), $reservation );
				
				if( ! empty( $reservation_attrs ) ) {
					
					$reservations_out[$user_name] = array(
						'user'  => $user_name
					);
					foreach( $reservation_attrs as $value) {
						$reservations_out[$user_name][$value[0]] = $value[1];
					}
					if( ! array_key_exists( 'level', $reservations_out[$user_name] ) ) {
						$reservations_out[$user_name]['level'] = apply_filters( 'bp_group_reservation_default_level', 'member');
					}
					
				} else {
					
					$reservations_out[$user_name] = array(
						'user'  => $user_name,
						'level' => apply_filters( 'bp_group_reservation_default_level', 'member')
					);
					
				}

			}
		}
		
		return apply_filters( 'bp_group_reservation_processed', $reservations_out, $reservation_string );
	}
	
	/**
	 * Convert reservation array into a string suitable for display in the edit screen
	 */
	function format_reservations( $reservation_arr ) {
		
		$reservation_string = '';
		foreach( $reservation_arr as $reservation ) {
			
			$reservation_string .= $reservation['extras']['user'] . ( $reservation['extras']['level'] == 'member' ? '' : ', level=' . $reservation['extras']['level'] );
			
			foreach($reservation['extras'] as $key => $value) {
				
				// Skip "user", "level", and any other attributes with this filter
				if( in_array( $key, apply_filters( 'bp_group_reservation_skipped_extras', array( 'user', 'level' ) ) ) )	continue;
				$reservation_string .= ', ' . $key . '=' . $value;
				
			}
			
			$reservation_string .= "\n";
		}
		
		return $reservation_string;
		
	}
	
}

bp_register_group_extension( 'BP_Group_Reservation_Extension' );

?>