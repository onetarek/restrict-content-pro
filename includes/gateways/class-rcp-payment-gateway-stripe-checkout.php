<?php
/**
 * Payment Gateway For Stripe Checkout
 *
 * @package     Restrict Content Pro
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
*/

class RCP_Payment_Gateway_Stripe_Checkout extends RCP_Payment_Gateway_Stripe {

	/**
	 * Process registration
	 *
	 * @since 2.5
	 */
	public function process_signup() {

		if( ! empty( $_POST['rcp_stripe_checkout'] ) ) {

			$this->auto_renew = ( '2' === rcp_get_auto_renew_behavior() || '0' === $this->length ) ? false : true;

		}

		parent::process_signup();

	}

	/**
	 * Print fields for this gateway
	 *
	 * @return string
	 */
	public function fields() {
		global $rcp_options;

		if( is_user_logged_in() ) {
			$email = wp_get_current_user()->user_email;
		} else {
			$email = false;
		}

		$data = apply_filters( 'rcp_stripe_checkout_form_data', array(
			'key'               => $this->publishable_key,
			'locale'            => 'auto',
			'allowRememberMe'   => true,
			'email'             => $email,
			'currency'          => rcp_get_currency(),
			'alipay'            => isset( $rcp_options['stripe_alipay'] ) && '1' === $rcp_options['stripe_alipay'] && 'USD' === rcp_get_currency() ? true : false
		) );

		$subscriptions = array();
		foreach ( rcp_get_subscription_levels( 'active' ) as $subscription ) {
			$subscriptions[ $subscription->id ] = array(
				'description' => $subscription->description,
				'name'        => $subscription->name,
				'panelLabel'  => __( 'Register', 'rcp' ),
			);
		}

		$subscriptions = apply_filters( 'rcp_stripe_checkout_subscription_data', $subscriptions );

		ob_start(); ?>

		<script>
			var rcp_script_options;
			var rcpSubscriptions = <?php echo json_encode( $subscriptions ); ?>;
			var checkoutArgs     = <?php echo json_encode( $data ); ?>;

			jQuery('#rcp_submit').val( rcp_script_options.pay_now );

			jQuery('body').on('rcp_level_change', function(event, target) {
				jQuery('#rcp_submit').val(
					jQuery(target).attr('rel') > 0 ? rcp_script_options.pay_now : rcp_script_options.register
				);
			});

			jQuery('#rcp_user_email' ).focusout(function() {
				checkoutArgs.email = jQuery(this).val();
			});

			/**
			 * 'rcp_register_form_submission' is triggered in register.js
			 * if the form data is successfully validated.
			 */
			jQuery('body').on('rcp_register_form_submission', function(e, response, form, submission_form) {

				if ( form.data.gateway !== 'stripe_checkout' ) {
					return;
				}

				var $level = submission_form.find('input[name=rcp_level]:checked');

				var $price = $level.parent().find('.rcp_price').attr('rel') * <?php echo rcp_stripe_get_currency_multiplier(); ?>;

				if( jQuery('.rcp_gateway_fields').hasClass('rcp_discounted_100') ) {
					return true;
				}

				if ( ! $price > 0 || ! form.data.total > 0 ) {
					submission_form.submit();
					return true;
				}

				if ( ! checkoutArgs.email ) {
					checkoutArgs.email = jQuery('#rcp_user_email' ).val();
				}

				var got_token = false;

				var handler = StripeCheckout.configure({
					key: checkoutArgs.key,
					locale: checkoutArgs.locale,
					token: function(token) {
						got_token = true;
						// Add the token to the form and submit it
						submission_form.append('<input type="hidden" name="stripeToken" value="' + token.id + '" />').submit();
					},
					// 'closed' runs when the modal closes, whether the token was successful or not
					closed: function() {
						// Unblock the form if the Checkout modal is closed without a successful payment
						if (! got_token) {
							submission_form.unblock();
						}
					},
					email: checkoutArgs.email,
					currency: checkoutArgs.currency,
					alipay: checkoutArgs.alipay,
					amount: form.data.total * <?php echo rcp_stripe_get_currency_multiplier(); ?>
				});

				handler.open(
					rcpSubscriptions[$level.val()]
				);
			});

			// Close Checkout on page navigation
			jQuery(window).on('popstate', function() {
				rcpStripeCheckout.close();
			});
		</script>

		<?php
		return ob_get_clean();
	}

	/**
	 * Load Stripe JS
	 *
	 * @since 2.5
	 */
	public function scripts() {
		parent::scripts();
		wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ) );

	}

	/**
	 * Validate fields
	 *
	 * @since 2.5
	 */
	public function validate_fields() {}

}
