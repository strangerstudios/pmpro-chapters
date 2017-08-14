<?php
/*
Plugin Name: Paid Memberships Pro - Chapters
Plugin URI: https://www.paidmembershipspro.com/add-ons/chapters/
Description: Adds a user option called "chapter" controlled by a membership_chapter custom post type.
Version: .1
Author: Stranger Studios
Author URI: https://www.paidmembershipspro.com
Text Domain: pmpro-chapters
*/

define('PMPROCH_DIR', dirname(__FILE__));
require_once(PMPROCH_DIR . '/classes/class-membership-chapter.php');
require_once(PMPROCH_DIR . '/shortcodes/membership-chapter-content.php');

//initialize the CPT
add_action( 'init', array( 'Membership_Chapter', 'init' ) );

//add chapter to checkout
add_action( 'pmpro_checkout_boxes', array( 'Membership_Chapter', 'pmpro_checkout_boxes' ), 5 );
add_action( 'pmpro_paypalexpress_session_vars', array( 'Membership_Chapter', 'pmpro_paypalexpress_session_vars' ) );

//add chapter to profile pages
add_action( 'show_user_profile', array( 'Membership_Chapter', 'show_extra_profile_fields' ) );
add_action( 'edit_user_profile', array( 'Membership_Chapter', 'show_extra_profile_fields' ) );
add_action( 'personal_options_update', array( 'Membership_Chapter', 'save_extra_profile_fields' ) );
add_action( 'edit_user_profile_update', array( 'Membership_Chapter', 'save_extra_profile_fields' ) );

/**
 * Run code on activation
 */
function pmproch_activation() {
	/*
	Add Chapter Caps to Admin
	*/
	$role = get_role( 'administrator' );
	$role->add_cap('manage_chapters');
	$role->add_cap('publish_membership_chapters');
	$role->add_cap('edit_membership_chapters');
	$role->add_cap('edit_others_membership_chapters');
	$role->add_cap('delete_membership_chapters');
	$role->add_cap('read_private_chapters');
	$role->add_cap('edit_membership_chapter');
	$role->add_cap('delete_membership_chapter');
	$role->add_cap('read_membership_chapter');
}
register_activation_hook( __FILE__, 'pmproch_activation' );