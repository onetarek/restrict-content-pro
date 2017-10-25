<?php
/**
 * Edit Member Page
 *
 * @package     Restrict Content Pro
 * @subpackage  Admin/Edit Member
 * @copyright   Copyright (c) 2017, Restrict Content Pro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

$view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'overview';

if( isset( $_GET['edit_member'] ) ) {
	$member_id = absint( $_GET['edit_member'] );
} elseif( isset( $_GET['view_member'] ) ) {
	$member_id = absint( $_GET['view_member'] );
}
$member = new RCP_Member( $member_id );

$member_tabs           = rcp_member_tabs();
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
?>
<h1>
	<?php _e( 'Member Details', 'rcp' ); ?>
</h1>

<?php if( ! $member->exists() ) : ?>
	<div class="error settings-error">
		<p><?php _e( 'Error: Invalid member ID.', 'rcp' ); ?></p>
	</div>
	<?php return; ?>
<?php endif; ?>

<div id="rcp-item-wrapper" class="rcp-item-has-tabs">
	<div id="rcp-item-tab-wrapper" class="rcp-member-tab-wrapper-list">
		<ul id="rcp-item-tab-wrapper-list" class="member-tab-wrapper-list">
			<?php foreach ( $member_tabs as $key => $tab ) :
				$active = ( $key === $view ) ? true : false;
				$class  = $active ? 'active' : 'inactive';
				?>

				<li class="<?php echo sanitize_html_class( $class ); ?>">

					<?php if ( ! $active ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rcp-members&edit_member=' . urlencode( $member_id ) . '&view=' . urlencode( $key ) ) ); ?>" aria-label="<?php echo esc_attr( $tab['title'] ); ?>">
					<?php endif; ?>

					<span class="rcp-item-tab-label-wrap"<?php echo $active ? ' aria-label="' . esc_attr( $tab['title'] ) . '"' : ''; ?>>
						<span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>" aria-hidden="true"></span>
						<span class="rcp-item-tab-label"><?php echo esc_html( $tab['title'] ); ?></span>
					</span>

					<?php if ( ! $active ) : ?>
						</a>
					<?php endif; ?>

				</li>

			<?php endforeach; ?>
		</ul>
	</div>

	<div id="rcp-item-card-wrapper" class="rcp-member-card-wrapper">
		<?php
		switch ( $view ) {

			case 'notes' :
				/**
				 * Render member notes.
				 */
				// @todo
				break;

			default :
				/**
				 * Render member overview.
				 */
				?>
				<div class="rcp-info-wrapper rcp-member-section">

					<form id="rcp-edit-member-info" method="POST">

						<div class="rcp-item-info rcp-member-info">

							<div id="rcp-member-avatar" class="rcp-avatar-wrap left">
								<?php echo get_avatar( $member->user_email ); ?>
								<span class="rcp-info-item rcp-member-edit-link"><a href="#" id="rcp-edit-member"><?php _e( 'Edit Member', 'rcp' ); ?></a></span>
							</div>

							<div class="rcp-member-id right">
								#<?php echo $member->ID; ?>
							</div>

							<div class="rcp-member-main-wrapper left">

								<span class="rcp-member-first-name rcp-edit-item">
									<label for="rcp-member-first-name" class="screen-reader-text"><?php _e( 'First name', 'rcp' ); ?></label>
									<input type="text" id="rcp-member-first-name" name="first_name" value="<?php echo esc_attr( $member->first_name ); ?>">
								</span>
								<span class="rcp-member-first-name rcp-info-item rcp-editable">
									<?php echo $member->first_name; ?>
								</span>

								<span class="rcp-member-last-name rcp-edit-item">
									<label for="rcp-member-last-name" class="screen-reader-text"><?php _e( 'Last name', 'rcp' ); ?></label>
									<input type="text" id="rcp-member-last-name" name="last_name" value="<?php echo esc_attr( $member->last_name ); ?>">
								</span>
								<span class="rcp-member-last-name rcp-info-item rcp-editable">
									<?php echo $member->last_name; ?>
								</span>

								<span class="rcp-member-login rcp-info-item">
									<?php echo $member->user_login; ?>
								</span>

								<span class="rcp-member-email rcp-edit-item">
									<label for="rcp-member-email" class="screen-reader-text"><?php _e( 'Email address', 'rcp' ); ?></label>
									<input type="text" id="rcp-member-email" name="email" value="<?php echo esc_attr( $member->user_email ); ?>">
								</span>
								<span class="rcp-member-email rcp-info-item rcp-editable">
									<?php echo $member->user_email; ?>
								</span>

								<span class="rcp-member-since rcp-info-item">
									<?php _e( 'Member since:', 'rcp' ); ?>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $member->get_joined_date() ) ); ?>
								</span>

								<span class="rcp-member-user-id">
									<?php _e( 'User ID:', 'rcp' ); ?> <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . urlencode( $member->ID ) ) ); ?>" title="<?php esc_attr_e( 'Edit user account', 'rcp' ); ?>"><?php echo $member->ID; ?></a>
								</span>

							</div>

						</div>

						<span id="rcp-member-edit-actions" class="rcp-edit-item">
							<input type="hidden" name="rcp-action" value="edit-member"/>
							<input type="hidden" name="user" value="<?php echo esc_attr( $member->ID ); ?>"/>
							<?php wp_nonce_field( 'rcp_edit_member_nonce', 'rcp_edit_member_nonce' ); ?>
							<input type="submit" id="rcp-edit-member-save" class="button-secondary" value="<?php esc_attr_e( 'Update Member', 'rcp' ); ?>" />
							<a href="#" id="rcp-edit-member-cancel" class="delete"><?php _e( 'Cancel', 'rcp' ); ?></a>
						</span>

					</form>

				</div>

				<div id="rcp-item-stats-wrapper" class="rcp-member-stats-wrapper rcp-member-section">
					<span class="dashicons dashicons-chart-area"></span>
					<?php echo rcp_currency_filter( $member->get_lifetime_value() ); ?> <?php _e( 'Lifetime Value', 'rcp' ); ?>
				</div>
				<?php

		}
		?>
	</div>
</div>