<?php
/**
 * Admin Single Reported item screen
 *
 * @since   BuddyBoss 1.5.6
 * @package BuddyBoss
 */

$current_tab       = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
$is_content_screen = ! empty( $current_tab ) && 'reported-content' === $current_tab;
$error             = isset( $_REQUEST['error'] ) ? $_REQUEST['error'] : false; // phpcs:ignore
$user_id           = bp_moderation_get_content_owner_id( $moderation_request_data->item_id, $moderation_request_data->item_type );
$admins            = array_map(
	'intval',
	get_users(
		array(
			'role'   => 'administrator',
			'fields' => 'ID',
		)
	)
);
?>
<div class="wrap">
	<p>
		<?php
		if ( $is_content_screen ) {
			printf(
				'<a class="bb-back" href="%1$s"><i class="dashicons dashicons-arrow-left-alt"></i> %2$s</a>',
				esc_url( bp_get_admin_url( 'admin.php?page=bp-moderation&tab=reported-content' ) ),
				esc_html__( 'Back to Reported Content', 'buddyboss' ),
			);
		} else {
			printf(
				'<a class="bb-back" href="%1$s"><i class="dashicons dashicons-arrow-left-alt"></i> %2$s</a>',
				esc_url( bp_get_admin_url( 'admin.php?page=bp-moderation' ) ),
				esc_html__( 'Back to Flagged Members', 'buddyboss' ),
			);
		}
		?>
	</p>
	<h1> <?php esc_html_e( 'View Report', 'buddyboss' ); ?></h1>

	<?php if ( ! empty( $moderation_request_data ) ) : ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-<?php echo 1 === (int) get_current_screen()->get_columns() ? '1' : '2'; ?>">
				<div id="post-body-content">
					<div id="postdiv">
						<div id="bp_moderation_action" class="postbox">
							<div class="inside">

								<?php if ( ! empty( $messages ) ) : ?>
									<div id="moderation" class="<?php echo ( ! empty( $error ) ) ? 'error' : 'updated'; ?>">
										<p><?php echo wp_kses_post( implode( "<br/>\n", $messages ) ); ?></p>
									</div>
								<?php endif; ?>

								<div class="bp-moderation-ajax-msg hidden notice notice-success">
									<p></p>
								</div>

								<!-- Report Header -->

								<?php if ( $is_content_screen ) { ?>

									<div class="report-header">
										<div class="report-header_details">
											<div class="report-header_content_id">
												<?php
												echo esc_html( bp_moderation_get_content_type( $moderation_request_data->item_type ) );
												$view_content_url = bp_moderation_get_permalink( $moderation_request_data->item_id, $moderation_request_data->item_type );
												if ( ! empty( $view_content_url ) ) {
													printf(
														'<a target="_blank" href="%s" title="%s"> <span>#%s</span> <i class="bb-icon-external-link bb-icon-l"></i> </a> ',
														esc_url( $view_content_url ),
														esc_attr__( 'View', 'buddyboss' ),
														esc_html( $moderation_request_data->item_id )
													);
												}
												?>
											</div>
											<div class="report-header_user">
												<?php
												$user_ids = bp_moderation_get_content_owner_id( $moderation_request_data->item_id, $moderation_request_data->item_type );
												if ( ! is_array( $user_ids ) ) {
													$user_ids = array( $user_ids );
												}

												foreach ( $user_ids as $user_id ) {
													printf( '%s <strong>%s</strong> <br/>', get_avatar( $user_id, '32' ), wp_kses_post( bp_core_get_userlink( $user_id ) ) );
												}
												?>
											</div>
										</div>
										<div class="report-header_content">
											<strong class="report-header_number"><?php printf( esc_html( bp_core_number_format( $moderation_request_data->count ) ) ); ?></strong><?php esc_html_e( 'Reports', 'buddyboss' ); ?>
										</div>
										<div class="report-header_action">
											<?php
											$action_type  = ( 1 === (int) $moderation_request_data->hide_sitewide ) ? 'unhide' : 'hide';
											$action_label = ( 'unhide' === $action_type ) ? esc_html__( 'Unhide Content', 'buddyboss' ) : esc_html__( 'Hide Content', 'buddyboss' );

											if ( ! bp_moderation_is_user_suspended( $user_id ) ) {
												?>
												<a href="javascript:void(0);" class="button report-header_button bp-hide-request single-report-btn <?php echo ( 'unhide' === $action_type ) ? 'green' : ''; ?>" data-id="<?php echo esc_attr( $moderation_request_data->item_id ); ?>" data-type="<?php echo esc_attr( $moderation_request_data->item_type ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bp-hide-unhide-moderation' ) ); ?>" data-action="<?php echo esc_attr( $action_type ); ?>" title="<?php echo esc_html( $action_label ); ?>">
													<?php
													echo esc_html( $action_label );
													?>
												</a>
												<?php
											}
											if ( ! is_array( $user_id ) && ! in_array( $user_id, $admins, true ) ) {
												$user_action_type = ( bp_moderation_is_user_suspended( $user_id ) ) ? 'unsuspend' : 'suspend';
												$user_action_text = ( 'unsuspend' === $user_action_type ) ? esc_html__( 'Unsuspend Owner', 'buddyboss' ) : esc_html__( 'Suspend Owner', 'buddyboss' );
												?>
												<a href="javascript:void(0);" class="button report-header_button bp-block-user single-report-btn content-author <?php echo ( 'unsuspend' === $user_action_type ) ? 'green' : ''; ?>" data-id="<?php echo esc_attr( $user_id ); ?>" data-type="user" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bp-hide-unhide-moderation' ) ); ?>" data-action="<?php echo esc_attr( $user_action_type ); ?>" title="<?php echo esc_attr( $user_action_text ); ?>">
													<?php
													echo esc_html( $user_action_text );
													?>
												</a>
												<?php
											}
											?>
										</div>
									</div>

								<?php } else { ?>

									<div class="report-header">
										<div class="report-header_user">
											<?php
											printf( '%s <strong><a href="%s">%s</a></strong>', get_avatar( $moderation_request_data->item_id, '32' ), esc_url( BP_Moderation_Members::get_permalink( $moderation_request_data->item_id ) ), esc_html( bp_core_get_userlink( $moderation_request_data->item_id, true ) ) );
											?>
										</div>
										<div class="report-header_content">
											<strong class="report-header_number"><?php printf( esc_html( bp_core_number_format( $moderation_request_data->count ) ) ); ?></strong><?php esc_html_e( 'Blocks', 'buddyboss' ); ?>
										</div>
										<div class="report-header_content">
											<strong class="report-header_number"><?php printf( esc_html( bp_core_number_format( $moderation_request_data->user_reported ) ) ); ?></strong><?php esc_html_e( 'Reports', 'buddyboss' ); ?>
										</div>
										<div class="report-header_action">
											<?php
											if ( ! is_array( $user_id ) && ! in_array( $user_id, $admins, true ) ) {
												$user_action_type = ( bp_moderation_is_user_suspended( $user_id ) ) ? 'unsuspend' : 'suspend';
												$user_action_text = ( 'unsuspend' === $user_action_type ) ? esc_html__( 'Unsuspend Member', 'buddyboss' ) : esc_html__( 'Suspend Member', 'buddyboss' );
												?>
												<a href="javascript:void(0);" class="button report-header_button <?php echo ( 'unsuspend' === $user_action_type ) ? 'green' : ''; ?> bp-block-user single-report-btn123 content-author123" data-id="<?php echo esc_attr( $user_id ); ?>" data-type="user" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bp-hide-unhide-moderation' ) ); ?>" data-action="<?php echo esc_attr( $user_action_type ); ?>" title="<?php echo esc_attr( $user_action_text ); ?>">
													<?php
													echo esc_html( $user_action_text );
													?>
												</a>
											<?php } ?>
										</div>
									</div>

								<?php } ?>




								<table class="form-table report-table">
									<tbody>
										<?php if ( $is_content_screen ) { ?>
											<tr>
												<td scope="row" style="width: 20%;"></td>
											</tr>
										<?php } else { ?>
											<tr>
												<td scope="row" style="width: 20%;">
													<strong><label>
															<?php
															/* translators: accessibility text */
															esc_html_e( 'Blocked By', 'buddyboss' );
															?>
														</label></strong>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
								<?php if ( ! $is_content_screen ) { ?>
									<?php
									$bp_moderation_report_list_table = new BP_Moderation_Report_List_Table( 'blocked' );
									// Prepare the group items for display.
									$bp_moderation_report_list_table->prepare_items();
									$bp_moderation_report_list_table->views();
									$bp_moderation_report_list_table->display();

									?>
									<table class="form-table report-table">
										<tbody>
											<tr>
												<td scope="row" style="width: 20%;">
													<strong><label>
															<?php
															/* translators: accessibility text */
															esc_html_e( 'Reported By', 'buddyboss' );
															?>
														</label></strong>
												</td>
											</tr>
										</tbody>
									</table>
								<?php } ?>
								<?php
								$bp_moderation_report_list_table = new BP_Moderation_Report_List_Table();
								// Prepare the group items for display.
								$bp_moderation_report_list_table->prepare_items();
								$bp_moderation_report_list_table->views();
								$bp_moderation_report_list_table->display();

								$action_type  = ( 1 === (int) $moderation_request_data->hide_sitewide ) ? 'unhide' : 'hide';
								$action_label = ( 'unhide' === $action_type ) ? esc_html__( 'Unhide Content', 'buddyboss' ) : esc_html__( 'Hide Content', 'buddyboss' );
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<p>
			<?php
			printf(
				'%1$s <a href="%2$s">%3$s</a>',
				esc_html__( 'No moderation found with this ID.', 'buddyboss' ),
				esc_url( bp_get_admin_url( 'admin.php?page=bp-moderation' ) ),
				esc_html__( 'Go back and try again.', 'buddyboss' )
			);
			?>
		</p>
	<?php endif; ?>
</div>
