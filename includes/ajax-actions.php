<?php
/**
 * Ajax Actions
 *
 * Process the front-end ajax actions.
 *
 * @package     Restrict Content Pro
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/license/gpl-2.1.php GNU Public License
 */

/**
 * Check whether a discount code is valid. Used during registration to validate a discount code on the fly.
 *
 * @return void
 */
function rcp_validate_discount_with_ajax() {
	if( isset( $_POST['code'] ) ) {

		global $rcp_options;

		$return          = array();
		$return['valid'] = false;
		$return['full']  = false;
		$subscription_id = isset( $_POST['subscription_id'] ) ? absint( $_POST['subscription_id'] ) : 0;

		rcp_setup_registration( $subscription_id, $_POST['code'] );

		if( rcp_validate_discount( $_POST['code'], $subscription_id ) ) {

			$code_details = rcp_get_discount_details_by_code( sanitize_text_field( $_POST['code'] ) );

			if( ( ! rcp_registration_is_recurring() && rcp_get_registration()->get_total() == 0.00 )
				|| ( rcp_registration_is_recurring() && rcp_get_registration()->get_recurring_total() == 0.00 && ! isset( $rcp_options['one_time_discounts'] ) )
			) {

				// this is a 100% discount
				$return['full']   = true;

			}

			$return['valid']  = true;
			$return['amount'] = rcp_discount_sign_filter( $code_details->amount, $code_details->unit );

		}

		wp_send_json( $return );
	}
	die();
}
add_action( 'wp_ajax_validate_discount', 'rcp_validate_discount_with_ajax' );
add_action( 'wp_ajax_nopriv_validate_discount', 'rcp_validate_discount_with_ajax' );

/**
 * Calls the load_fields() method for gateways when a gateway selection is made
 *
 * @since  2.1
 * @return void
 */
function rcp_load_gateway_fields() {

	$gateways = new RCP_Payment_Gateways;
	$gateways->load_fields();
	die();
}
add_action( 'wp_ajax_rcp_load_gateway_fields', 'rcp_load_gateway_fields' );
add_action( 'wp_ajax_nopriv_rcp_load_gateway_fields', 'rcp_load_gateway_fields' );

/**
 * Setup the registration details
 *
 * @since  2.5
 * @return void
 */
function rcp_calc_total_ajax() {
	$return = array(
		'valid' => false,
		'total' => __( 'No available subscription levels for your account.', 'rcp' ),
	);

	if ( ! rcp_is_registration() ) {
		wp_send_json( $return );
	}

	ob_start();

	rcp_get_template_part( 'register-total-details' );

	$return['total'] = ob_get_clean();

	wp_send_json( $return );
}
add_action( 'wp_ajax_rcp_calc_discount', 'rcp_calc_total_ajax' );
add_action( 'wp_ajax_nopriv_rcp_calc_discount', 'rcp_calc_total_ajax' );




function rcp_validate_registration_state( array $args ) {
error_log('args in rcp_validate_registration_total');
error_log(print_r($args,true));
	if( empty( $args ) ) {
		return false;
	}

	global $rcp_options;

	$return = array();
	$return['level_id'] = ! empty( $args['rcp_level'] ) ? absint( $args['rcp_level'] ) : 0;
	$return['gateway'] = ! empty( $args['rcp_gateway'] ) ? sanitize_text_field( $args['rcp_gateway'] ) : false;
	$return['gateway_fields'] = false;
	$return['discount_code'] = ! empty( $args['discount_code'] ) ? sanitize_text_field( $args['discount_code'] ) : false;
	$return['discount_valid'] = false;
	$return['discount_amount'] = false;
	$return['full_discount'] = false;
	$return['is_free'] = ! empty( $args['is_free'] ) && 'true' == $args['is_free'];
	$return['lifetime'] = ! empty( $args['lifetime'] ) && 'true' == $args['lifetime'];
	$return['level_has_trial'] = ! empty( $args['level_has_trial'] ) && 'true' == $args['level_has_trial'];
	$return['total'] = __( 'No available subscription levels for your account.', 'rcp' );
	$return['event_type'] = ! empty( $args['event_type'] ) ? sanitize_text_field( $args['event_type'] ) : false;

	rcp_setup_registration( $return['level_id'], $return['discount_code'] );

	/** Discount */
	if( ! empty( $return['discount_code'] ) ) {
		if( rcp_validate_discount( $return['discount_code'], $return['level_id'] ) ) {
			$code_details = rcp_get_discount_details_by_code( $return['discount_code'] );

			if( ( ! rcp_registration_is_recurring() && rcp_get_registration()->get_total() == 0.00 )
			    || ( rcp_registration_is_recurring() && rcp_get_registration()->get_recurring_total() == 0.00 && ! isset( $rcp_options['one_time_discounts'] ) )
			) {
				// this is a 100% discount
				$return['full'] = true;
			}

			$return['discount_valid'] = true;
			$return['discount_amount'] = rcp_discount_sign_filter( $code_details->amount, $code_details->unit );
		}
	}

	/** Totals */
	if ( rcp_is_registration() ) {
		$return['total'] = rcp_get_registration()->get_total();
		$return['recurring_total'] = rcp_get_registration()->get_recurring_total();
		$return['recurring'] = rcp_registration_is_recurring();
		$return['one_time_discounts'] = isset( $rcp_options['one_time_discounts'] );

		ob_start();

		rcp_get_template_part( 'register-total-details' );

		$return['total_html'] = ob_get_clean();
	}

	/** Gateway fields */
	if( ! empty( $return['gateway'] ) ) {
		$gateways = new RCP_Payment_Gateways;
		$fields = $gateways->get_gateway_fields( $return['gateway'] );
		if( ! empty( $fields ) ) {
			$return['gateway_fields'] = $fields;
		}
	}

	return $return;
}

function rcp_validate_registration_state_ajax() {
	wp_send_json_success( rcp_validate_registration_state( $_POST ) );
}
add_action( 'wp_ajax_rcp_validate_registration_state', 'rcp_validate_registration_state_ajax' );
add_action( 'wp_ajax_nopriv_rcp_validate_registration_state', 'rcp_validate_registration_state_ajax' );