<?php
/**
 * Pusher integration admin tab
 *
 * @since   BuddyBoss [BBVERSION]
 * @package BuddyBoss/Pusher
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setup Pusher integration admin tab class.
 *
 * @since BuddyBoss [BBVERSION]
 */
class BB_Pusher_Admin_Integration_Tab extends BP_Admin_Integration_tab {

	/**
	 * Current section.
	 *
	 * @var $current_section
	 */
	protected $current_section;

	/**
	 * Initialize
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function initialize() {
		$this->tab_order       = 50;
		$this->current_section = 'bb_pusher-integration';
		$this->intro_template  = $this->root_path . '/templates/admin/integration-tab-intro.php';

		add_filter( 'bb_admin_icons', array( $this, 'admin_setting_icons' ), 10, 2 );
	}

	/**
	 * Pusher Integration is active?
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @return bool
	 */
	public function is_active() {
		return (bool) apply_filters( 'bb_pusher_integration_is_active', true );
	}

	/**
	 * Pusher integration tab scripts.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function register_admin_script() {

		$active_tab = bp_core_get_admin_active_tab();

		if ( 'bb-pusher' === $active_tab ) {
			$min     = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$rtl_css = is_rtl() ? '-rtl' : '';
			wp_enqueue_style( 'bb-pusher-admin', bb_pusher_integration_url( '/assets/css/bb-pusher-admin' . $rtl_css . $min . '.css' ), false, buddypress()->version );
		}

		parent::register_admin_script();

	}

	/**
	 * Register setting fields for pusher integration.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function register_fields() {

		$sections = $this->get_settings_sections();

		foreach ( (array) $sections as $section_id => $section ) {

			// Only add section and fields if section has fields.
			$fields = $this->get_settings_fields_for_section( $section_id );

			if ( empty( $fields ) ) {
				continue;
			}

			$section_title     = ! empty( $section['title'] ) ? $section['title'] : '';
			$section_callback  = ! empty( $section['callback'] ) ? $section['callback'] : false;
			$tutorial_callback = ! empty( $section['tutorial_callback'] ) ? $section['tutorial_callback'] : false;

			// Add the section.
			$this->add_section( $section_id, $section_title, $section_callback, $tutorial_callback );

			// Loop through fields for this section.
			foreach ( (array) $fields as $field_id => $field ) {

				$field['args'] = isset( $field['args'] ) ? $field['args'] : array();

				if ( ! empty( $field['callback'] ) && ! empty( $field['title'] ) ) {
					$sanitize_callback = isset( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : array();
					$this->add_field( $field_id, $field['title'], $field['callback'], $sanitize_callback, $field['args'] );
				}
			}
		}
	}

	/**
	 * Get setting sections for pusher integration.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @return array $settings Settings sections for pusher integration.
	 */
	public function get_settings_sections() {

		$status      = 'not-connected';
		$status_text = __( 'Not Connected', 'buddyboss' );
		$html        = '<div class="bb-pusher-status">' .
			'<span class="status-line ' . esc_attr( $status ) . '">' . esc_html( $status_text ) . '</span>' .
		'</div>';

		$settings = array(
			'bb_pusher_settings_section' => array(
				'page'              => 'Pusher',
				'title'             => __( 'Pusher', 'buddyboss' ) . $html,
				'tutorial_callback' => array( $this, 'setting_callback_pusher_tutorial' ),
			),
		);

		return $settings;
	}

	/**
	 * Get setting fields for section in pusher integration.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @param string $section_id Section ID.
	 *
	 * @return array|false $fields setting fields for section in pusher integration false otherwise.
	 */
	public function get_settings_fields_for_section( $section_id = '' ) {

		// Bail if section is empty.
		if ( empty( $section_id ) ) {
			return false;
		}

		$fields = $this->get_settings_fields();
		$fields = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

		return $fields;
	}

	/**
	 * Register setting fields for pusher integration.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @return array $fields setting fields for pusher integration.
	 */
	public function get_settings_fields() {

		$fields = array();

		$fields['bb_pusher_settings_section'] = array(
			'information' => array(
				'title'             => esc_html__( 'Information', 'buddyboss' ),
				'callback'          => array( $this, 'setting_callback_pusher_information' ),
				'sanitize_callback' => 'string',
				'args'              => array( 'class' => 'notes-hidden-header' ),
			),
		);

		if ( ! function_exists( 'bb_platform_pro' ) ) {
			$fields['bb_pusher_settings_section']['infos'] = array(
				'title'             => esc_html__( 'Notes', 'buddyboss' ),
				'callback'          => array( $this, 'setting_callback_pusher_bbp_pro_not_installed' ),
				'sanitize_callback' => 'string',
				'args'              => array( 'class' => 'notes-hidden-header' ),
			);
		} elseif (
			function_exists( 'bb_platform_pro' ) &&
			version_compare( bb_platform_pro()->version, '2.0.5', '<' )
		) {
			$fields['bb_pusher_settings_section']['infos'] = array(
				'title'             => esc_html__( 'Notes', 'buddyboss' ),
				'callback'          => array( $this, 'setting_callback_pusher_bbp_pro_older_version_installed' ),
				'sanitize_callback' => 'string',
				'args'              => array( 'class' => 'notes-hidden-header' ),
			);
		}

		return $fields;
	}

	/**
	 * Link to Pusher Settings tutorial.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function setting_callback_pusher_tutorial() {
		?>
		<p>
			<a class="button" href="
			<?php
				echo esc_url(
					bp_get_admin_url(
						add_query_arg(
							array(
								'page'    => 'bp-help',
								'article' => '',
							),
							'admin.php'
						)
					)
				);
			?>
			"><?php esc_html_e( 'View Tutorial', 'buddyboss' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Callback fields for pusher information.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @return void
	 */
	public function setting_callback_pusher_information() {
		printf(
			/* translators: pusher channels link */
			esc_html__( 'The BuddyBoss Platform has an integration with %s, a WebSocket service which can power realtime features on your BuddyBoss community such as live messaging.', 'buddyboss' ),
			'<a href="https://pusher.com/" target="_blank">' . esc_html__( 'Pusher Channels', 'buddyboss' ) . '</a>'
		);
	}

	/**
	 * Callback fields for platform pro not installed warning.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function setting_callback_pusher_bbp_pro_not_installed() {
		echo '<p class="description notification-information bb-lab-notice">' .
			sprintf(
				wp_kses_post(
					/* translators: BuddyBoss Pro purchase link */
					__( 'Please install %1$s to use Pusher on your site.', 'buddyboss' )
				),
				'<a href="' . esc_url( 'https://www.buddyboss.com/platform' ) . '" target="_blank">' . esc_html__( 'BuddyBoss Platform Pro', 'buddyboss' ) . '</a>'
			) .
		'</p>';
	}

	/**
	 * Callback fields for the platform pro older version installed warning.
	 *
	 * @since BuddyBoss [BBVERSION]
	 */
	public function setting_callback_pusher_bbp_pro_older_version_installed() {
		echo '<p class="description notification-information bb-lab-notice">' .
			sprintf(
				wp_kses_post(
					/* translators: BuddyBoss Pro purchase link */
					__( 'Please update %1$s to version 2.0.5 to use Pusher on your site.', 'buddyboss' )
				),
				'<a target="_blank" href="' . esc_url( 'https://www.buddyboss.com/platform' ) . '">' . esc_html__( 'BuddyBoss Platform Pro', 'buddyboss' ) . '</a>'
			) .
			'</p>';
	}

	/**
	 * Added icon for the pusher admin settings.
	 *
	 * @since BuddyBoss [BBVERSION]
	 *
	 * @param string $meta_icon Icon class.
	 * @param string $id        Section ID.
	 *
	 * @return mixed|string
	 */
	public function admin_setting_icons( $meta_icon, $id = '' ) {
		if ( 'bb_pusher_settings_section' === $id ) {
			$meta_icon = 'bb-icon-bf  bb-icon-brand-pusher';
		}

		return $meta_icon;
	}
}