<?php
/**
 * Plugin Name: Rotaract Appointments
 * Plugin URI:  https://github.com/rotaract/appointments
 * Description: Plugin for managing "mein Rotaract" appointments.
 * Version: 1.0
 * Author: Ressort IT-Entwicklung - Rotaract Deutschland
 * Author URI: https://rotaract.de/ueber-rotaract/rdk/
 * Text Domain: rotaract-appointments
 * Domain Path: /languages
 *
 * @package Rotaract-Appointments
 * @category Core
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

/**
 * Markdown parser to convert description to HTML.
 */
require 'vendor/autoload.php';
use Parsedown;
/**
 * Logic for receiving the event data from elastic API.
 */
require 'elastic-caller.php';


/**
 * Load the text domain.
 */
function rotaract_appointments_load_textdomain() {
	load_plugin_textdomain( 'rotaract-appointments', false, 'rotaract-appointments/languages' );
}
add_action( 'plugins_loaded', 'rotaract_appointments_load_textdomain' );

/**
 * Enqueues all style and script files for non-admin page.
 */
function appointments_enqueue_scripts() {
	$fullcalendar_version = '5.8.0';

	wp_register_style( 'rotaract-appointments', plugins_url( 'rotaract-appointments.css', __FILE__ ), array(), '1.0' );
	wp_register_style( 'fullcalendar', plugins_url( 'fullcalendar/main.min.css', __FILE__ ), array(), $fullcalendar_version );
	wp_enqueue_style( 'rotaract-appointments' );
	wp_enqueue_style( 'fullcalendar' );

	wp_enqueue_script( 'fullcalendar', plugins_url( 'fullcalendar/main.min.js', __FILE__ ), array(), $fullcalendar_version, true );
	wp_enqueue_script( 'fullcalendar-locales', plugins_url( 'fullcalendar/locales-all.min.js', __FILE__ ), array( 'fullcalendar' ), $fullcalendar_version, true );
}

/**
 * Enqueues all style and script files and init calendar.
 *
 * @see appointments_enqueue_scripts
 * @see init_calendar
 *
 * @return String containing empty div tag with id "rotaract-appointments"
 */
function appointments_shortcode() {
	$output = '<div id="rotaract-appointments"></div>';

	appointments_enqueue_scripts();
	add_action( 'wp_footer', 'init_calendar', 999 );
	return $output;
}

/**
 * Initializes the calendar by receiving event data, parse it, and display it.
 */
function init_calendar() {
	$owner        = get_option( 'rotaract_appointment_options' )['rotaract_appointment_owners'];
	$appointments = read_appointments( $owner );

	$events = array();
	$parser = new Parsedown();
	foreach ( $appointments as $appointment ) {
		array_push(
			$events,
			array(
				'title'       => $appointment->_source->title,
				'start'       => wp_date( 'Y-m-d\TH:i', strtotime( $appointment->_source->begins_at ) ),
				'end'         => wp_date( 'Y-m-d\TH:i', strtotime( $appointment->_source->ends_at ) ),
				'allDay'      => $appointment->_source->all_day,
				'description' => '<div class="event-title">' . $appointment->_source->title . '</div><div class="event-description-inner">' . $parser->text( $appointment->_source->description ) . '</div>',
				'owner'       => $appointment->_source->owner,
			)
		);
	}
	echo sprintf(
		'<script>
		document.addEventListener("DOMContentLoaded", function() {
			const calendarEl = document.getElementById("rotaract-appointments");
			const calendar = new FullCalendar.Calendar(calendarEl, {
				locale: "de",
				initialView: "listYear",
				eventDidMount: function(info) {
					var elem = document.createElement("div");
					elem.innerHTML = info.event.extendedProps.description;
					elem.classList.add("event-description");
					info.el.append(elem);
				},
				eventClick: function(info) {
					if (!info.jsEvent.target.href) {
						info.el.classList.toggle("show");
						if (info.view.type !== "listYear") {
							let descEl = info.el.querySelector(".event-description");
							if (descEl) {
								descEl.style.left = "50%%";
								let newLeft = descEl.getBoundingClientRect().left - screen.width / 10;
								if (newLeft < 0 || newLeft + descEl.offsetWidth > screen.width * 0.8) {
									descEl.style.left = "calc(50%% - " + newLeft + "px)";
								}
							}
						}
					}
				},
				headerToolbar: {
					start: "prev,next today",
					center: "title",
					end: "listYear,dayGridMonth"
				},
				height: "auto",
				events: %1$s
			});
			calendar.render();
		});
		</script>',
		wp_json_encode( $events )
	);
}
add_shortcode( 'rotaract-appointments', 'appointments_shortcode' );

/**
 * Enqueues style and script files for admin settings page.
 */
function admin_scripts() {
	wp_enqueue_style( 'appointments-admin-style-select', plugins_url( 'select/light.css', __FILE__ ), array(), '1.1.4' );
	// Including the lc_select script in footer results in broken owner selection in appointments submenu page.
	wp_enqueue_script( 'appointments-admin-script-select', plugins_url( 'select/lc_select.min.js', __FILE__ ), array(), '1.1.4', true );

	wp_enqueue_script( 'rotaract-appointments-settings', plugins_url( 'settings.js', __FILE__ ), array( 'appointments-admin-script-select' ), '1.0', true );
	wp_localize_script(
		'rotaract-appointments-settings',
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
 * Adds setting menu and submenu page for this plugin.
 */
function rotaract_appointments_settings_menu() {
	add_menu_page(
		'Rotaract',
		'Rotaract',
		'administrator',
		'rotaract',
		'rotaract_html',
		plugins_url( 'images/wheel.svg', __FILE__ ),
	);

	$submenu = add_submenu_page(
		'rotaract',
		__( 'Settings for Appointments', 'rotaract-appointments' ),
		__( 'Appointments', 'rotaract-appointments' ),
		'administrator',
		'rotaract_appointments',
		'appointments_settings_html'
	);

	add_action( 'admin_print_scripts-' . $submenu, 'admin_scripts' );
}
add_action( 'admin_menu', 'rotaract_appointments_settings_menu' );

/**
 * Adds setting fields for this plugin.
 */
function appointments_settings_init() {
	if ( ! defined( 'ROTARACT_ELASTIC_HOST' ) ) {
		add_action( 'admin_notices', 'elastic_missing_notice' );
	}

	// Register our settings.
	register_setting( 'rotaract_appointments', 'rotaract_appointment_options' );

	add_settings_section(
		'rotaract_appointment_settings',
		__( 'Rotaract Events', 'rotaract-appointments' ),
		'rotaract_appointment_section_cb',
		'rotaract_appointments'
	);

	add_settings_field(
		'rotaract_appointment_owners',
		__( 'Owners', 'rotaract-appointments' ),
		'appointment_owners_field_cb',
		'rotaract_appointments',
		'rotaract_appointment_settings',
		array(
			'label_for' => 'rotaract_appointment_owners',
			'class'     => 'appointment_owners',
		)
	);
}
add_action( 'admin_init', 'appointments_settings_init' );

/**
 * HTML notice that elasticsearch configuration is missing.
 */
function elastic_missing_notice() {
	?>
	<div class="error notice">
		<p>
			<strong><?php esc_html_e( 'Rotaract Events', 'rotaract-appointments' ); ?>:</strong>
			<?php esc_html_e( 'Please set Elasticsearch Host in your WordPress configuration!', 'rotaract-appointments' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function rotaract_appointment_section_cb( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Customize your calendar events here.', 'rotaract-appointments' ); ?></p>
	<?php
}

/**
 * Builds select tag containing grouped appointment options.
 *
 * @param array $args  The settings array, defining ...
 */
function appointment_owners_field_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$options = get_option( 'rotaract_appointment_options' );
	$owners  = get_all_owner();
	?>
	<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
		name="rotaract_appointment_options[<?php echo esc_attr( $args['label_for'] ); ?>][]"
		class="lc_select"
		multiple>
		<optgroup label="<?php esc_attr_e( 'Rotaract Deutschland', 'rotaract-appointments' ); ?>">
			<option value="Rotaract Deutschland Komitee"<?php echo in_array( 'Rotaract Deutschland Komitee', $options[ $args['label_for'] ], true ) ? ' selected' : ''; ?>>Rotaract Deutschland Komitee</option>
		</optgroup>
		<?php foreach ( $owners as $type => $items ) : ?>
		<optgroup label="<?php echo esc_attr( $type ); ?>">
			<?php foreach ( $items as $item ) : ?>
			<option value="<?php echo esc_attr( $item ); ?>"<?php echo in_array( $item, $options[ $args['label_for'] ], true ) ? ' selected' : ''; ?>><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</optgroup>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Builds the HTML for the appointments submenu page.
 */
function appointments_settings_html() {
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
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
			<?php
			// Output security fields for the registered setting "rotaract_appointments".
			settings_fields( 'rotaract_appointments' );
			// Output setting sections and their fields.
			// Sections are registered for "rotaract_appointments", each field is registered to a specific section.
			do_settings_sections( 'rotaract_appointments' );
			// Output save settings button.
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Builds the HTML for the rotaract menu page.
 */
function rotaract_html() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p>Wir freuen uns, dass du hier bist!</p>
		<p><i>Dein Ressort IT Entwicklung</i></p>
	</div>
	<?php
}
