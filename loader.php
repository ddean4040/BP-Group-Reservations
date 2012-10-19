<?php
/*
Plugin Name: BuddyPress Group Reservation
Plugin URI: 
Description: Reserve a spot in your BuddyPress groups for certain unregistered users
Version: 1.0
Revision Date: 10/18/2012
Requires at least: WP 3.4, BuddyPress 1.6
Tested up to: WP 3.5-beta2 , BuddyPress 1.6.1
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: David Dean
Author URI: http://www.generalthreat.com/
*/

define ( 'BP_GROUP_RESERVATION__IS_INSTALLED', 1 );
define ( 'BP_GROUP_RESERVATION_VERSION', '1.0' );
define ( 'BP_GROUP_RESERVATION_SLUG', 'group-reservation' );

function bp_group_reservation_load() {
	
	require dirname(__FILE__) . '/functions.php';
	require dirname(__FILE__) . '/actions.php';
	require dirname(__FILE__) . '/extension.php';
	
}

add_action( 'bp_include', 'bp_group_reservation_load' );

?>