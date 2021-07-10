<?php

/**
 * Plugin Name: Rotaract Appointments
 * Plugin URI:  https://github.com/rotaract/appointments
 * Description: Plugin for managing "mein Rotaract" appointments.
 * Version: 1.0
 * Author: Ressort IT-Entwicklung - Rotaract Deutschland
 * Author URI: https://rotaract.de/ueber-rotaract/rdk/
 */

require 'elastic-caller.php';
require 'Parsedown.php';


function appointments_enqueue_scripts() {
	wp_register_style( 'rotaract-appointments', plugins_url( 'rotaract-appointments.css', __FILE__ ) );
	wp_register_style( 'full-calendar', plugins_url( 'full-calendar/main.min.css', __FILE__ ) );
	wp_enqueue_style( 'rotaract-appointments' );
	wp_enqueue_style( 'full-calendar' );

	wp_enqueue_script( 'full-calendar', plugins_url( 'full-calendar/main.min.js', __FILE__ ) );
	wp_enqueue_script( 'full-calendar-de', plugins_url( 'full-calendar/de.js', __FILE__ ) );
}

function appointments_shortcode( $atts ) {
	$output = '<div id="rotaract-appointments"></div>';

	appointments_enqueue_scripts();
	add_action( 'wp_footer', 'init_calendar', 999 );
	return $output;
}
function init_calendar() {
	$owner        = get_option( 'rotaract_appointment_options' )['rotaract_appointment_owners'];
	$appointments = readAppointments( $owner )->hits->hits;

	$events = array();
	$parser = new Parsedown();
	foreach ( $appointments as $appointment ) {
		array_push(
			$events,
			array(
				'title'       => $appointment->_source->title,
				'start'       => date( 'Y-m-d\TH:i', strtotime( $appointment->_source->begins_at ) ),
				'end'         => date( 'Y-m-d\TH:i', strtotime( $appointment->_source->ends_at ) ),
				'allDay'      => $appointment->_source->all_day,
				'description' => '<div class="event-title">' . $appointment->_source->title . '</div><div class="event-description-inner">' . $parser->text( $appointment->_source->description ) . '</div>',
				'owner'       => $appointment->_source->owner,
			)
		);
	}
	echo sprintf(
		'<script>
		document.addEventListener("DOMContentLoaded", function() {
			var calendarEl = document.getElementById("rotaract-appointments");
			var calendar = new FullCalendar.Calendar(calendarEl, {
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

function admin_scripts( $hook ) {
	wp_enqueue_style( 'appointments-admin-style-select', plugin_dir_url( __FILE__ ) . 'select/light.css' );
	wp_enqueue_script( 'appointments-admin-script-select', plugin_dir_url( __FILE__ ) . 'select/lc_select.min.js' );
}
add_action( 'admin_enqueue_scripts', 'admin_scripts' );

function rotaract_appointments_settings_menu() {
	add_menu_page(
		'Rotaract',
		'Rotaract',
		'administrator',
		'rotaract',
		'rotaract_html',
		'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA2MCA2MCI+PHBhdGggZD0ibTU5LjkgMjkuMS0uMS0uMWMtMS4zLS44LTIuOS0xLjItNC42LTEuMiAwLS4yLS4yLTEuNy0uMy0yIDEuNi0uNCAzLjEtMS4zIDQuMS0yLjRsLjEtLjF2LS4xYy0uMS0uNi0uMy0xLjQtLjUtMS45di0uMWgtLjFjLTEuNC0uNC0zLjItLjQtNC43IDAtLjEtLjItLjctMS42LS43LTEuOCAxLjQtLjggMi42LTIgMy4zLTMuNHYtLjJjLS4yLS41LS42LTEuMi0xLTEuN1YxNGgtLjFjLTEuNSAwLTMuMS40LTQuNiAxLjItLjEtLjItMS4xLTEuNC0xLjItMS42IDEuMS0xLjIgMi0yLjYgMi40LTQuMXYtLjFsLS4xLS4xYy0uMy0uNC0uOS0xLTEuNC0xLjRoLS4yYy0xLjUuNC0yLjkgMS4yLTQuMSAyLjQtLjItLjEtMS40LTEuMS0xLjYtMS4yLjgtMS40IDEuMi0zIDEuMi00LjV2LS4yaC0uMWMtLjQtLjQtMS4yLS44LTEuNy0xaC0uMmMtMS4zLjctMi41IDEuOS0zLjMgMy4zLS4yLS4xLTEuNy0uNy0xLjktLjguMi0uOC4zLTEuNi4zLTIuNCAwLS44LS4xLTEuNi0uMy0yLjN2LS4xaC0uMWMtLjUtLjItMS4zLS40LTEuOS0uNWgtLjFsLS4xLjFjLTEuMSAxLTEuOSAyLjUtMi40IDQuMS0uMiAwLTEuOC0uMi0yLS4zIDAtMS42LS41LTMuMy0xLjItNC42di0uMWguM2MtLjYuMi0xLjQuMi0yIC4zaC0uMXYuMWMtLjggMS4zLTEuMiAyLjktMS4yIDQuNi0uMiAwLTEuOC4yLTIgLjMtLjUtMS42LTEuMy0zLjEtMi40LTQuMWwtLjEtLjFoLS4xYy0uNi4xLTEuNC4zLTEuOS41aC0uMXYuMWMtLjIuNy0uMyAxLjUtLjMgMi40IDAgLjguMSAxLjYuMyAyLjQtLjEtLjEtMS42LjYtMS44LjctLjgtMS40LTItMi42LTMuMy0zLjNoLS4yYy0uNS4yLTEuMi42LTEuNyAxSDE0di4yYzAgMS41LjUgMy4xIDEuMiA0LjUtLjIuMS0xLjMgMS0xLjUgMS4yLTEuMi0xLjEtMi42LTItNC4xLTIuNGgtLjF2LS4xYy0uNC4zLTEgLjktMS4zIDEuNHYuMmMuNCAxLjQgMS4yIDIuOSAyLjQgNC4xLS4xLjItMS4xIDEuNS0xLjIgMS42LTEuNC0uOC0zLjEtMS4zLTQuNi0xLjJoLS4xdi4xYy0uMy40LS43IDEuMS0xIDEuN3YuMmMuNyAxLjMgMS45IDIuNSAzLjMgMy40LS4xLjItLjYgMS42LS43IDEuOC0xLjYtLjQtMy4zLS40LTQuNyAwaC0uMXYuMWMtLjIuNS0uNCAxLjMtLjUgMS45di4xbC4xLjFjMSAxLjEgMi41IDEuOSA0LjEgMi40IDAgLjItLjIgMS44LS4zIDItMS42IDAtMy4zLjUtNC42IDEuMkguMnYuMWMtLjIgMC0uMi4zLS4yLjcgMCAuNCAwIC43LjEgMXYuMWguMWMxLjMuOCAyLjkgMS4yIDQuNiAxLjIgMCAuMi4yIDEuOC4zIDItMS42LjUtMyAxLjMtNC4xIDIuNGwtLjEuMXYuMWMuMS42LjMgMS40LjUgMS45di4xaC4xYzEuNC40IDMuMi40IDQuNyAwIC4xLjEuNyAxLjUuOCAxLjctMS40LjgtMi42IDItMy4zIDMuM3YuMmMuMi41LjYgMS4yIDEgMS43bC4xLjFoLjFjMS41IDAgMy4xLS40IDQuNi0xLjIuMS4yIDEuMSAxLjQgMS4yIDEuNi0xLjEgMS4xLTIgMi42LTIuNCA0LjF2LjJjLjMuNC45IDEgMS40IDEuNGwuMS4xaC4xYzEuNS0uNCAyLjktMS4yIDQuMS0yLjQuMi4xIDEuNCAxLjEgMS42IDEuMi0uOCAxLjQtMS4yIDMtMS4yIDQuNXYuMmwuMS4xYy40LjMgMS4xLjcgMS43IDFoLjJjMS4zLS43IDIuNS0yIDMuNC0zLjQuMi4xIDEuNi42IDEuOC43LS4yLjgtLjMgMS42LS4zIDIuNCAwIC44LjEgMS42LjMgMi40di4xaC4xYy41LjIgMS4zLjQgMS45LjVoLjFsLjEtLjFjMS4xLTEuMSAxLjktMi41IDIuMy00LjEuMiAwIDEuOC4zIDIgLjMgMCAxLjYuNSAzLjMgMS4yIDQuNnYuMWguMWMuNi4xIDEuNC4xIDEuOSAwaC4xdi0uMWMuOC0xLjMgMS4yLTIuOSAxLjItNC42LjIgMCAxLjctLjIgMS45LS4zLjQgMS42IDEuMyAzLjEgMi40IDQuMWguMmMuNi0uMSAxLjQtLjMgMS45LS41aC4xdi0uMWMuMi0uNy4zLTEuNS4zLTIuNCAwLS44LS4xLTEuNi0uMy0yLjQuMi0uMSAxLjYtLjcgMS44LS44LjggMS40IDIgMi42IDMuMyAzLjRoLjJjLjUtLjIgMS4yLS42IDEuNy0xaC4xdi0uMmMwLTEuNC0uNS0zLTEuMi00LjUuMi0uMSAxLjQtMS4xIDEuNS0xLjIgMS4yIDEuMSAyLjcgMiA0LjEgMi40aC4xbC4xLS4xYy40LS40IDEtLjkgMS40LTEuNGwuMS0uMXYtLjFjLS40LTEuNC0xLjItMi45LTIuNC00LjEuMS0uMiAxLjEtMS40IDEuMi0xLjYgMS40LjggMy4xIDEuMiA0LjYgMS4yaC4xbC4xLS4xYy40LS41LjgtMS4yIDEtMS43di0uMmMtLjctMS4zLTEuOS0yLjUtMy4zLTMuNC4xLS4yLjYtMS42LjctMS44IDEuNi40IDMuMy40IDQuNyAwaC4xdi0uMWMuMi0uNS40LTEuMy41LTEuOXYtLjFsLS4xLS4xYy0xLTEuMS0yLjUtMS45LTQuMS0yLjQgMC0uMi4yLTEuNy4zLTEuOSAxLjYgMCAzLjMtLjUgNC42LTEuMmguMXYtLjFjMC0uMy4xLS42LjEtMXMtLjUtLjUtLjYtLjdNMzAgNTIuM2MtMTIuNCAwLTIyLjUtMTAuMS0yMi41LTIyLjVTMTcuNiA3LjMgMzAgNy4zczIyLjUgMTAuMSAyMi41IDIyLjVTNDIuNCA1Mi4zIDMwIDUyLjNNMzAgMTBjLTEwLjkgMC0xOS44IDguOS0xOS44IDE5LjhTMTkuMSA0OS42IDMwIDQ5LjZzMTkuOC04LjkgMTkuOC0xOS44UzQwLjkgMTAgMzAgMTBtMi41IDMuMmMuMi0uMi41LS4xLjctLjEgNCAxIDYuOSAyLjYgOS43IDUuNi4yLjIuMy40LjMuNnYuMWMtLjEuMy0uNC40LS42LjZsLTguMyA0Yy0uMy4yLS42LjItLjkgMC0uMi0uMS0uNC0uNC0uNC0uOGwtLjctOS4yYzAtLjQgMC0uNi4yLS44bS0xNS40IDUuN2MyLjktMyA1LjgtNC43IDkuNy01LjYuMi0uMS41LS4xLjcuMS4yLjEuMi40LjIuOWwtLjcgOS4xYzAgLjQtLjIuNi0uNC44LS4yLjEtLjYuMS0uOSAwbC04LjMtNGMtLjMtLjEtLjYtLjMtLjYtLjYtLjEtLjMgMC0uNS4zLS43bS0yIDE3LjFjLS40LjItLjYuMy0uOC4zLS4yLS4xLS4zLS4zLS40LS42LTEuMi0zLjktMS4yLTcuMyAwLTExLjMuMS0uMy4yLS41LjQtLjYuMy0uMS41LjEuOC4zbDcuNiA1LjJjLjMuMi41LjUuNS43IDAgLjMtLjIuNS0uNC43TDE1LjEgMzZ6bTEyLjUgMTAuOGMtLjIuMi0uNC4xLS43IDAtNC0uOS02LjktMi42LTkuNy01LjYtLjItLjItLjMtLjQtLjMtLjZ2LS4xYzAtLjMuNC0uNC42LS42bDguMy00Yy4zLS4yLjYtLjIuOSAwIC4yLjEuNC40LjQuOGwuNyA5LjJjLjEuNSAwIC43LS4yLjlNMzAgMzUuM2MtMyAwLTUuNC0yLjQtNS40LTUuNCAwLTEuOC45LTMuNSAyLjQtNC41LjUtLjMgMS4yLS4yIDEuNS4zLjMuNS4yIDEuMi0uMyAxLjUtLjkuNi0xLjQgMS42LTEuNCAyLjcgMCAxLjggMS40IDMuMiAzLjIgMy4yIDEuOCAwIDMuMi0xLjQgMy4yLTMuMiAwLTEuMS0uNS0yLjEtMS41LTIuNy0uNS0uMy0uNi0xLS4zLTEuNXMxLS42IDEuNS0uM2MxLjUgMSAyLjQgMi43IDIuNCA0LjUuMSAyLjktMi4zIDUuNC01LjMgNS40bTEyLjkgNS44Yy0yLjggMy01LjcgNC43LTkuNyA1LjYtLjMuMS0uNi4xLS43LS4xLS4yLS4yLS4yLS41LS4yLS44bC43LTkuMmMwLS40LjItLjYuNC0uOC4yLS4xLjYtLjEuOSAwbDguMyA0Yy40LjIuNi40LjYuNi4xLjMtLjEuNS0uMy43bTIuOS0xNy4zYy4yLjEuMy4zLjQuNiAxLjIgMy45IDEuMiA3LjMgMCAxMS4zLS4xLjMtLjIuNS0uNC42LS4zLjEtLjUtLjEtLjgtLjNsLTcuNy01LjJjLS4zLS4yLS41LS41LS41LS44IDAtLjMuMi0uNS41LS44TDQ1IDI0Yy4zLS4yLjYtLjMuOC0uMiIgc3R5bGU9ImZpbGw6I2ZmZiIvPjwvc3ZnPg0K',
		55
	);

	add_submenu_page(
		'rotaract',
		__( 'Settings for Appointments' ),
		__( 'Appointments', 'rotaract' ),
		'administrator',
		'rotaract_appointments',
		'appointments_settings_html'
	);
}
add_action( 'admin_menu', 'rotaract_appointments_settings_menu' );

function appointments_settings_init() {
	// Register our settings.
	register_setting( 'rotaract_appointments', 'rotaract_appointment_options' );

	add_settings_section(
		'rotaract_appointment_settings',
		__( 'Rotaract Events', 'rotaract' ),
		'rotaract_appointment_section_cb',
		'rotaract_appointments'
	);

	add_settings_field(
		'rotaract_appointment_owners',
		__( 'Owners', 'rotaract' ),
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
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function rotaract_appointment_section_cb( $args ) {
	?>
	<p id="<?php esc_attr_e( $args['id'] ); ?>"><?php esc_html_e( 'Customize your calendar events here.', 'rotaract' ); ?></p>
	<?php
}

function appointment_owners_field_cb( $args ) {
	// Get the value of the setting we've registered with register_setting().
	$options = get_option( 'rotaract_appointment_options' );
	$owners  = getAllOwner();
	?>
	<select id="<?php esc_attr_e( $args['label_for'] ) ?>"
		name="rotaract_appointment_options[<?php esc_attr_e( $args['label_for'] ) ?>][]"
		class="lc_select"
		multiple>
		<optgroup label="<?php esc_attr_e( 'Rotaract Deutschland', 'rotaract' ) ?>">
			<option value="Rotaract Deutschland Komitee"<?php echo in_array( 'Rotaract Deutschland Komitee', $options[ $args['label_for'] ] ) ? ' selected' : '' ?>>Rotaract Deutschland Komitee</option>
		</optgroup>
		<?php foreach ( $owners as $type => $items ) : ?>
		<optgroup label="<?php esc_attr_e( $type, 'rotaract' ) ?>">
			<?php foreach ( $items as $item ) : ?>
			<option value="<?php esc_attr_e( $item ) ?>"<?php echo in_array( $item, $options[ $args['label_for'] ] ) ? ' selected' : '' ?>><?php esc_html_e( $item ) ?></option>
			<?php endforeach; ?>
		</optgroup>
		<?php endforeach; ?>
	</select>
	<?php
}

function appointments_settings_html() {
	// Check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Add error/update messages.

	// Check if the user has submitted the settings.
	// WordPress will add the "settings-updated" $_GET parameter to the URL.
	if ( isset( $_GET['settings-updated'] ) ) {
		// Add settings saved message with the class of "updated".
		add_settings_error( 'rotaract_messages', 'rotaract_message', __( 'Settings Saved', 'rotaract' ), 'updated' );
	}

	// Show error/update messages.
	settings_errors( 'rotaract_messages' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( get_admin_page_title() ); ?></h1>
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
	<script type="text/javascript">
		new lc_select('select.lc_select', {
			enable_search: true,
			labels: [
				'<?= __( 'Search', 'rotaract' ) ?>',
				'<?= __( 'Add', 'rotaract' ) ?>',
				'<?= __( 'Select', 'rotaract' ) ?>',
				'<?= __( 'Nothing found.', 'rotaract' ) ?>'
			]
		});
	</script>
	<?php
}

function rotaract_html() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( get_admin_page_title() ); ?></h1>
		<p>Wir freuen uns, dass du hier bist!</p>
		<p><i>Dein Ressort IT Entwicklung</i></p>
	</div>
	<?php
}
