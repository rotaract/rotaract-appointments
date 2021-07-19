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
	private string $lc_select_version = '1.1.4';

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

		wp_enqueue_script( $this->rotaract_appointments, plugins_url( 'js/settings.js', __FILE__ ), array( 'appointments-admin-script-select' ), $this->version, true );
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

		include $this->get_partial( 'notice_elastic_missing.php' );

	}

	/**
	 * Adds setting fields for this plugin.
	 */
	public function admin_init() {

		// Register our settings.
		register_setting( 'rotaract_appointments', 'rotaract_appointment_options' );

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
				'rotaract_settings_html',
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

		include $this->get_partial( 'page_rotaract_settings.php' );

	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	public function rotaract_appointment_section( array $args ) {

		include $this->get_partial( 'section_rotaract_appointments.php' );

	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param array $args  The settings array, defining ...
	 */
	public function appointment_owners_field( array $args ) {
		// Get the value of the setting we've registered with register_setting().
		$options = get_option( 'rotaract_appointment_options' );
		$owners  = get_all_owner();

		include $this->get_partial( 'field_appointment_owners.php' );

	}

}
