<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin
 * @author     Ressort IT-Entwicklung - Rotaract Deutschland <it-entwicklung@rotaract.de>
 */
class Rotaract_Appointments_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $rotaract_appointments    The ID of this plugin.
	 */
	private string $rotaract_appointments;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private string $version;

	/**
	 * The version of the JavaScript dependency LC-select.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lc_select_version    The current version of lc_select.
	 */
	private string $lc_select_version = '1.1.7';

	/**
	 * The Elasticsearch caller.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Rotaract_Elastic_Caller $elastic_caller    The object that handles search calls to the Elasticsearch instance.
	 */
	private Rotaract_Elastic_Caller $elastic_caller;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param    string                  $rotaract_appointments The name of this plugin.
	 * @param    string                  $version     The version of this plugin.
	 * @param    Rotaract_Elastic_Caller $elastic_caller Elasticsearch call handler.
	 * @since    1.0.0
	 */
	public function __construct( string $rotaract_appointments, string $version, Rotaract_Elastic_Caller $elastic_caller ) {

		$this->rotaract_appointments = $rotaract_appointments;
		$this->version               = $version;
		$this->elastic_caller        = $elastic_caller;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( '$this->rotaract_appointments', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version, 'all' );
		wp_enqueue_style( 'lc-select-light', plugins_url( 'node_modules/lc-select/themes/light.css', __DIR__ ), array(), $this->lc_select_version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Including the lc_select script in footer results in broken owner selection in appointments submenu page.
		wp_enqueue_script( 'lc-select', plugins_url( 'node_modules/lc-select/lc_select.min.js', __DIR__ ), array(), $this->lc_select_version, true );

		wp_enqueue_script( $this->rotaract_appointments, plugins_url( 'js/settings.js', __FILE__ ), array( 'lc-select' ), $this->version, true );
		wp_localize_script(
			$this->rotaract_appointments,
			'lcData',
			array(
				'labels' => array(
					esc_attr__( 'Search', 'rotaract-appointments' ),
					esc_attr__( 'Add', 'rotaract-appointments' ),
					esc_attr__( 'Select', 'rotaract-appointments' ),
					esc_attr__( 'Nothing found.', 'rotaract-appointments' ),
				),
			)
		);

	}

	/**
	 * Returns the full include path for a partial.
	 *
	 * @param string $filename Name of the file to be included.
	 *
	 * @return string Path for include statement.
	 */
	private function get_partial( string $filename ): string {

		return plugin_dir_path( __FILE__ ) . 'partials/' . $filename;

	}

	/**
	 * HTML notice that elasticsearch configuration is missing.
	 */
	public function elastic_missing_notice() {

		include $this->get_partial( 'notice-elastic-missing.php' );

	}

	/**
	 * Adds setting fields for this plugin.
	 */
	public function admin_init() {

		// Register our settings.
		register_setting(
			'rotaract_appointments',
			'rotaract_appointment_owners',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( $this, 'sanitize_rotaract_appointment_owners' ),
			)
		);

		register_setting(
			'rotaract_appointments',
			'rotaract_appointment_ics',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( $this, 'sanitize_rotaract_appointment_ics' ),
			)
		);

		add_settings_section(
			'rotaract_appointment_settings',
			__( 'Rotaract Events', 'rotaract-appointments' ),
			array( $this, 'rotaract_appointment_section' ),
			'rotaract'
		);

		add_settings_field(
			'rotaract_appointment_owners',
			__( 'Owners', 'rotaract-appointments' ),
			array( $this, 'appointment_owners_field' ),
			'rotaract',
			'rotaract_appointment_settings',
			array(
				'label_for' => 'rotaract_appointment_owners',
				'class'     => 'appointment_owners',
			)
		);

		add_settings_field(
			'rotaract_appointment_ics',
			__( 'External Calendar (ics)', 'rotaract-appointments' ),
			array( $this, 'appointment_ics_field' ),
			'rotaract',
			'rotaract_appointment_settings',
			array(
				'label_for' => 'rotaract_appointment_ics',
				'class'     => 'appointment_ics',
			)
		);

		add_settings_field(
			'rotaract_appointment_shortcode_manual',
			__( 'Manual', 'rotaract-appointments' ),
			array( $this, 'appointment_owners_shortcode_manual' ),
			'rotaract',
			'rotaract_appointment_settings'
		);
	}

	/**
	 * Adds setting menu and submenu page for this plugin.
	 */
	public function admin_menu() {
		if ( empty( $GLOBALS['admin_page_hooks']['rotaract'] ) ) {

			add_menu_page(
				'Rotaract',
				'Rotaract',
				'administrator',
				'rotaract',
				array( $this, 'rotaract_settings_html' ),
				plugins_url( 'images/wheel.svg', __DIR__ ),
			);

		}
	}

	/**
	 * Builds the HTML for the appointments submenu page.
	 */
	public function rotaract_settings_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add error/update messages.

		// Check if the user has submitted the settings.
		// WordPress will add the "settings-updated" $_GET parameter to the URL.
		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore
			// Add settings saved message with the class of "updated".
			add_settings_error( 'rotaract_messages', 'rotaract_message', __( 'Settings Saved', 'rotaract-appointments' ), 'updated' );
		}

		// Show error/update messages.
		settings_errors( 'rotaract_messages' );

		include $this->get_partial( 'page-rotaract-settings.php' );

	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @return array
	 */
	private function get_palette() {
		return array(
			'#d41367' => __( 'Cranberry', 'rotaract-appointments' ),
			'#0050a2' => __( 'Azure', 'rotaract-appointments' ),
			'#0c3c7c' => __( 'Royal Blue', 'rotaract-appointments' ),
			'#019fcb' => __( 'Sky Blue', 'rotaract-appointments' ),
			'#f7a81b' => __( 'Gold', 'rotaract-appointments' ),
			'#ff7600' => __( 'Orange', 'rotaract-appointments' ),
			'#872175' => __( 'Violet', 'rotaract-appointments' ),
			'#018d8d' => __( 'Turquoise', 'rotaract-appointments' ),
		);
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	public function rotaract_appointment_section( array $args ) {

		include $this->get_partial( 'section-rotaract-appointments.php' );

	}

	/**
	 * Builds select tag containing grouped appointment owners options.
	 *
	 * @param array $args  The settings array, defining ...
	 */
	public function appointment_owners_field( array $args ) {
		// Get the value of the setting we've registered with register_setting().
		$selected_owners = get_option( 'rotaract_appointment_owners' );

		include $this->get_partial( 'field-appointment-owners.php' );
	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param bool        $is_new True if this intends to be a new owner.
	 * @param int         $index Index of the parameter.
	 * @param string|null $owner_name The owner's name.
	 * @param string|null $owner_color Selected color.
	 */
	private function print_appointment_owners_line( bool $is_new, int $index, string $owner_name = null, string $owner_color = null ) {
		$owners        = $this->elastic_caller->get_all_owners();
		$color_palette = $this->get_palette();

		include $this->get_partial( 'field-appointment-owner.php' );
	}

	/**
	 * Builds select tag containing grouped appointment ics options.
	 *
	 * @param array $args  The settings array, defining ...
	 */
	public function appointment_ics_field( array $args ) {
		// Get the value of the setting we've registered with register_setting().
		$ics_feeds = get_option( 'rotaract_appointment_ics' );
		$color_palette = $this->get_palette();

		include $this->get_partial( 'field-ics.php' );
	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param bool        $is_new True if this intends to be a new owner.
	 * @param int         $index Index of the parameter.
	 * @param string|null $owner_name The owner's name.
	 * @param string|null $owner_color Selected color.
	 */
	private function print_ics_line( bool $is_new, int $index, string $feed_name = null, string $feed_url = null, string $feed_color = null ) {
		$color_palette = $this->get_palette();

		include $this->get_partial( 'field-ics-line.php' );
	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param array|null $input The POST data of the request on saving.
	 * @return array
	 */
	public function sanitize_rotaract_appointment_owners( ?array $input ): array {

		$new_input = array();
		// Re-indexing the array.
		foreach ( $input as $owner ) {
			$name  = sanitize_text_field( $owner['name'] );
			$color = sanitize_hex_color( $owner['color'] );
			if ( empty( $name ) || empty( $color ) ) {
				continue;
			}
			$new_input[] = array(
				'name'  => $name,
				'color' => $color,
			);
		}
		return array_values( $new_input );
	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param array|null $input The POST data of the request on saving.
	 * @return array
	 */
	public function sanitize_rotaract_appointment_ics( ?array $input ): array {

		$new_input = array();
		// Re-indexing the array.
		foreach ( $input as $feed ) {
			$name  = sanitize_text_field( $feed['name'] );
			$url   = sanitize_url( $feed['url'] );
			$color = sanitize_hex_color( $feed['color'] );
			if ( empty( $name ) || empty( $url ) || empty( $color ) ) {
				continue;
			}
			$new_input[] = array(
				'name'  => $name,
				'url'   => $url,
				'color' => $color,
			);
		}
		return array_values( $new_input );
	}

	/**
	 * Builds manual text that tells user how to add and customize the shortcode.
	 *
	 * @param array $args  The settings array, defining ...
	 */
	public function appointment_owners_shortcode_manual( array $args ) {

		include $this->get_partial( 'field-shortcode-manual.php' );

	}

}
