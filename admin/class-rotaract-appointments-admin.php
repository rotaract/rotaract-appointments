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
	 * The version of the JavaScript dependency Instantsearch.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $instantsearch_version    The current version of Meilisearch.
	 */
	private string $instantsearch_version = '4.60.0';

	/**
	 * The version of the JavaScript dependency Meilisearch.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $instant_meilisearch_version    The current version of Meilisearch.
	 */
	private string $instant_meilisearch_version = '0.13.6';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param    string                  $rotaract_appointments The name of this plugin.
	 * @param    string                  $version     The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct( string $rotaract_appointments, string $version ) {

		$this->rotaract_appointments = $rotaract_appointments;
		$this->version               = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( '$this->rotaract_appointments', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Including the Meilisearch script in footer results in broken owner selection in appointments submenu page.
		wp_enqueue_script( 'instantsearch', plugins_url( 'node_modules/instantsearch.js/dist/instantsearch.production.min.js', __DIR__ ), array(), $this->instantsearch_version, true );
		wp_enqueue_script( 'instant-meilisearch', plugins_url( 'node_modules/@meilisearch/instant-meilisearch/dist/instant-meilisearch.umd.min.js', __DIR__ ), array( 'instantsearch' ), $this->instant_meilisearch_version, true );

		wp_enqueue_script( $this->rotaract_appointments, plugins_url( 'js/settings.js', __FILE__ ), array( 'instantsearch', 'instant-meilisearch' ), $this->version, true );
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
	 * HTML notice that meilisearch configuration is missing.
	 */
	public function meilisearch_missing_notice() {

		include $this->get_partial( 'notice-meilisearch-missing.php' );
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
				'manage_options',
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
	public function rotaract_appointment_section( array $args ) { // phpcs:ignore

		include $this->get_partial( 'section-rotaract-appointments.php' );
	}

	/**
	 * Builds select tag containing grouped appointment owners options.
	 *
	 * @param array $args  The settings array, defining ...
	 */
	public function appointment_owners_field( array $args ) { // phpcs:ignore
		// Get the value of the setting we've registered with register_setting().
		$selected_owners = get_option( 'rotaract_appointment_owners' );

		include $this->get_partial( 'field-appointment-owners.php' );
	}


	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param int         $index Index of the parameter.
	 * @param string|null $owner_name The owner's name.
	 * @param string|null $owner_slug The owner's slug.
	 * @param string|null $owner_type The owner's type.
	 * @param string|null $owner_color Selected color.
	 */
	private function print_appointment_owners_line( bool $is_prototype, int $index = -1, string $owner_name = null, string $owner_slug = null, string $owner_type = null, string $owner_color = null ) {
		$color_palette = $this->get_palette();

		include $this->get_partial( 'field-appointment-owner.php' );
	}

	/**
	 * Builds select tag containing grouped appointment ics options.
	 *
	 * @param array $args  The settings array, defining ...
	 */
	public function appointment_ics_field( array $args ) { // phpcs:ignore
		// Get the value of the setting we've registered with register_setting().
		$ics_feeds = get_option( 'rotaract_appointment_ics' );

		include $this->get_partial( 'field-ics.php' );
	}

	/**
	 * Builds select tag containing grouped appointment options.
	 *
	 * @param bool        $is_new True if this intends to be a new owner.
	 * @param int         $index Index of the parameter.
	 * @param string|null $feed_name The owner's name.
	 * @param string|null $feed_url The ICS URL.
	 * @param string|null $feed_color Selected color.
	 */
	private function print_ics_line( bool $is_new, int $index, string $feed_name = null, string $feed_url = null, string $feed_color = null ) { // phpcs:ignore
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
			$name         = sanitize_text_field( $owner['name'] );
			$type         = sanitize_text_field( $owner['type'] );
			$slug = sanitize_text_field( $owner['slug'] );
			$color        = sanitize_hex_color( $owner['color'] );
			if ( empty( $name ) || empty( $type ) || empty( $slug ) || empty( $color ) ) {
				continue;
			}
			$new_input[] = array(
				'name'         => $name,
				'type'         => $type,
				'slug' => $slug,
				'color'        => $color,
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
			$url   = $feed['url'];
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
	public function appointment_owners_shortcode_manual( array $args ) { // phpcs:ignore

		include $this->get_partial( 'field-shortcode-manual.php' );
	}
}
