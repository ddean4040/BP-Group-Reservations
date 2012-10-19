=== BuddyPress Group Reservation ===
Contributors: ddean
Tags: buddypress, groups, users, membership
Requires at least: 3.4
Tested up to: 3.5-beta2
Stable tag: 1.0

Allows BuddyPress group or site administrators to reserve a space in groups for unregistered users by email address.

== Description ==

Used by exclusive sites everywhere to attract the A-listers. Upon registering, a user with a reservation is automatically added to one or more groups.

== Installation ==

1. Extract the plugin archive 
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I specify users who aren't registered? =
* By email address, of course! Integrators can override this with the `bp_group_reservation_search_key` hook

= What kind of memberships am I reserving? =
* Reserved spots are at `member` level. See below for how to change this for an extra-special user.

= Can I reserve a moderator or admin spot for a user? =
* Yes! Use the following format when creating the reservation:
`jdoe@example.com, level=mod` (replace `mod` with `admin` to save a group admin spot.)

= Can I store other data in the reservation? =
* Sure! You can store attributes with '`key=value` pairs, separated by commas. This plugin won't process those values, but it will fire actions for you to use them.

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==
