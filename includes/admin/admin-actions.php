<?php
/**
 * Admin Actions
 *
 * @package     restrict-content-pro
 * @subpackage  Admin/Admin Actions
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process all RCP actions sent via POST and GET
 *
 * @since 2.9
 * @return void
 */
function rcp_process_actions() {
	if ( isset( $_POST['rcp-action'] ) ) {
		do_action( 'rcp_action_' . $_POST['rcp-action'], $_POST );
	}

	if ( isset( $_GET['rcp-action'] ) ) {
		do_action( 'rcp_action_' . $_GET['rcp-action'], $_GET );
	}
}
add_action( 'admin_init', 'rcp_process_actions' );

/**
 * Denotes the various RCP pages as such in the pages list table.
 *
 * @param array $post_states An array of post display states.
 * @param WP_Post $post The current post object.
 */
function rcp_display_post_states( $post_states, $post ) {
	$rcp_options = get_option( 'rcp_settings' );
	$pages       = array(
		'registration_page' => 'Registration Page',
		'redirect'          => 'Redirect Page',
		'account_page'      => 'Account Page',
		'edit_profile'      => 'Edit Profile Page',
		'update_card'       => 'Update Card Page'
	);
	$pages_array = array();
	foreach ( $pages as $key => $value ) {
		$pages_array[ $rcp_options[ $key ] ] = isset( $rcp_options[ $key ] ) ? $pages[ $key ] : false;
	}
	if ( array_key_exists( $post->ID, $pages_array ) ) {
		$post_states[ 'rcp_' . array_search( $pages_array[ $post->ID ], $pages ) ] = $pages_array[ $post->ID ];
	}

	return $post_states;
}

add_filter( 'display_post_states', 'rcp_display_post_states', 10, 2 );