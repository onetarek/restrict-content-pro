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
	<?php _e( 'Edit Member:', 'rcp' ); echo ' ' . $member->display_name; ?>
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

		}
		?>
	</div>
</div>