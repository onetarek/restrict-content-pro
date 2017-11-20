<?php
/**
 * Edit Member's Subscription
 *
 * @package     Restrict Content Pro
 * @subpackage  Admin/Members/Edit Subscription
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( isset( $_GET['edit_member'] ) ) {
	$member_id = absint( $_GET['edit_member'] );
} elseif ( isset( $_GET['view_member'] ) ) {
	$member_id = absint( $_GET['view_member'] );
}
$member = new RCP_Member( $member_id );

$current_status        = $member->get_status();
$subscription_level_id = $member->get_subscription_id();
$expiration_date       = $member->get_expiration_date( false );

// If member is pending, get pending details.
if ( 'pending' == $current_status ) {

	$pending_subscription_id = $member->get_pending_subscription_id();

	if ( ! empty( $pending_subscription_id ) ) {
		$subscription_level_id = $pending_subscription_id;
	}

	if ( empty( $expiration_date ) ) {
		$expiration_date = $member->calculate_expiration( true );
	}
}

$subscription = rcp_get_subscription_details( $subscription_level_id );
?>
<h1>
	<?php _e( 'Subscription Details', 'rcp' ); ?>
</h1>

<?php if ( ! $member->exists() ) : ?>
	<div class="error settings-error">
		<p><?php _e( 'Error: Invalid member ID.', 'rcp' ); ?></p>
	</div>
	<?php return; ?>
<?php endif; ?>

<div id="rcp-item-card-wrapper">
	<div class="rcp-info-wrapper rcp-item-section">
		<form id="rcp-edit-member-subscription-info" method="POST" action="<?php echo esc_url( admin_url( 'admin.php?page=rcp-members&edit_member=' . absint( $member->ID ) . '&edit_subscription' ) ); ?>">
			<div class="rcp-item-info">
				<table class="widefat striped">
					<tbody>
					<tr>
						<th scope="row" class="row-title">
							<label for="tablecell"><?php _e( 'Member:', 'rcp' ); ?></label>
						</th>
						<td>
							<a href="<?php echo esc_url( remove_query_arg( 'edit_subscription' ) ); ?>"><?php echo esc_html( $member->display_name ); ?></a>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label><?php _e( 'Date Created:', 'rcp' ); ?></label>
						</th>
						<td>
							<?php // @todo date of first payment for this sub ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="tablecell"><?php _e( 'Billing Cycle:', 'rcp' ); ?></label>
						</th>
						<td>
							<?php // @todo ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="tablecell"><?php _e( 'Times Billed:', 'rcp' ); ?></label>
						</th>
						<td>
							<?php // @todo ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="rcp-status"><?php _e( 'Subscription Status', 'rcp' ); ?></label>
						</th>
						<td>
							<select name="status" id="rcp-status">
								<?php
								$statuses = array( 'active', 'expired', 'cancelled', 'pending', 'free' );
								foreach ( $statuses as $status ) :
									echo '<option value="' . esc_attr( $status ) . '"' . selected( $status, rcp_get_status( $member->ID ), false ) . '>' . ucwords( $status ) . '</option>';
								endforeach;
								?>
							</select>
							<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help" title="<?php _e( 'An Active status is required to access paid content. Members with a status of Cancelled may continue to access paid content until the expiration date on their account is reached.', 'rcp' ); ?>"></span>
							<p id="rcp-revoke-access-wrap">
								<input type="checkbox" id="rcp-revoke-access" name="rcp-revoke-access" value="1">
								<label for="rcp-revoke-access"><?php _e( 'Revoke access now', 'rcp' ); ?></label>
								<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help" title="<?php esc_attr_e( 'If not enabled, the member will retain access until the end of their current term. If checked, access will be revoked immediately.', 'rcp' ); ?>"></span>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="rcp-level"><?php _e( 'Subscription Level:', 'rcp' ); ?></label>
						</th>
						<td>
							<select name="level" id="rcp-level">
								<?php
								foreach ( rcp_get_subscription_levels( 'all' ) as $key => $level ) :
									echo '<option value="' . esc_attr( absint( $level->id ) ) . '"' . selected( $level->id, $subscription_level_id, false ) . '>' . esc_html( $level->name ) . '</option>';
								endforeach;
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="rcp-sub-expiration">
								<?php echo $member->is_trialing() ? __( 'Trialling Until:', 'rcp' ) : __( 'Expiration Date:', 'rcp' ); ?>
							</label>
						</th>
						<td>
							<span class="rcp-sub-expiration"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $expiration_date, current_time( 'timestamp' ) ) ); ?></span>
							<input type="text" id="rcp-sub-expiration" name="expiration" class="rcp-datepicker hidden" value="<?php echo esc_attr( $expiration_date ); ?>"/>
							<span>&nbsp;&ndash;&nbsp;</span>
							<a href="#" class="rcp-edit-sub-expiration"><?php _e( 'Edit', 'rcp' ); ?></a>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="rcp-recurring"><?php _e( 'Recurring', 'rcp' ); ?></label>
						</th>
						<td>
							<input type="checkbox" name="recurring" id="rcp-recurring" value="1" <?php checked( rcp_is_recurring( $member->ID ) ); ?>/>
							<span alt="f223" class="rcp-help-tip dashicons dashicons-editor-help" title="<?php _e( 'If checked, this member has a recurring subscription. Only customers with recurring memberships will be given the option to cancel their membership on their subscription details page.', 'rcp' ); ?>"></span>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="tablecell"><?php _e( 'Initial Purchase ID:', 'rcp' ); ?></label>
						</th>
						<td>
							<?php // @todo Fetch initial purchase ID. ?>
							<a href="<?php echo esc_url( add_query_arg( 'payment_id', 0, admin_url( 'admin.php?page=rcp-payments&view=edit-payment' ) ) ); ?>">0</a>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="tablecell"><?php _e( 'Payment Method:', 'rcp' ); ?></label>
						</th>
						<td>
							<?php // @todo ?>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="rcp-payment-profile-id"><?php _e( 'Payment Profile ID:', 'rcp' ); ?></label>
						</th>
						<td>
							<span class="rcp-payment-profile-id">
								<?php echo $member->get_payment_profile_id(); ?>
							</span>
							<input type="text" id="rcp-payment-profile-id" name="payment-profile-id" class="hidden" value="<?php echo esc_attr( $member->get_payment_profile_id() ); ?>"/>
							<span>&nbsp;&ndash;&nbsp;</span>
							<a href="#" id="rcp-edit-payment-profile-id"><?php _e( 'Edit', 'rcp' ); ?></a>
						</td>
					</tr>
					<tr>
						<th scope="row" class="row-title">
							<label for="rcp-merchant-sub-id"><?php _e( 'Merchant Subscription ID:', 'rcp' ); ?></label>
						</th>
						<td>
							<span class="rcp-merchant-sub-id">
								<?php echo $member->get_merchant_subscription_id(); ?>
							</span>
							<input type="text" id="rcp-merchant-sub-id" name="merchant-sub-id" class="hidden" value="<?php echo esc_attr( $member->get_merchant_subscription_id() ); ?>"/>
							<span>&nbsp;&ndash;&nbsp;</span>
							<a href="#" id="rcp-edit-merchant-sub-id"><?php _e( 'Edit', 'rcp' ); ?></a>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
			<?php // @todo make these work ?>
			<div id="rcp-sub-notices">
				<div class="notice notice-info inline hidden" id="rcp-sub-level-update-notice">
					<p><?php _e( 'Changing the subscription level assigned will not automatically adjust any pricing.', 'rcp' ); ?></p>
				</div>
				<div class="notice notice-info inline hidden" id="rcp-sub-expiration-update-notice">
					<p><?php _e( 'Changing the expiration date will not affect when renewal payments are processed.', 'rcp' ); ?></p>
				</div>
				<div class="notice notice-info inline hidden" id="rcp-sub-recurring-update-notice">
					<p><?php _e( 'Changing the recurring indicator will not set up or remove a subscription with the gateway. This checkbox is for updating RCP records only.', 'rcp' ); ?></p>
				</div>
				<div class="notice notice-warning inline hidden" id="rcp-payment-profile-id-update-notice">
					<p><?php _e( 'Changing the payment profile ID can result in renewals not being processed. Do this with caution.', 'rcp' ); ?></p>
				</div>
			</div>
			<div id="rcp-item-edit-actions" class="edit-item" style="float:right; margin: 10px 0 0; display: block;">
				<?php wp_nonce_field( 'rcp_edit_member_subscription', 'rcp_edit_member_subscription_nonce' ); ?>
				<input type="submit" name="rcp_update_member_subscription" id="rcp_update_member_subscription" class="button button-primary" value="<?php _e( 'Update Subscription', 'rcp' ); ?>"/>
				<?php if ( $member->can_cancel() ) : ?>
					<a class="button button-primary" href="<?php // @todo ?>"><?php _e( 'Cancel Subscription', 'rcp' ); ?></a>
				<?php endif; ?>
				&nbsp;<input type="submit" name="rcp_delete_member_subscription" class="rcp-delete-subscription button" value="<?php _e( 'Delete Subscription', 'rcp' ); ?>"/>
			</div>
		</form>
	</div>
</div>
