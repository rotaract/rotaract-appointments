<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/includes
 * @author     Ressort IT-Entwicklung - Rotaract Deutschland <it-entwicklung@rotaract.de>
 */
class Rotaract_Appointments_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rotaract-appointments',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	/**
	 * Localize name of owner type.
	 *
	 * @param  string $owner_type_name The name of the owner type of an appointment.
	 * @return string The translated name of the owner type.
	 * @since  1.0.0
	 */
	public static function localize_owner_type_name( string $owner_type_name ) {

		switch ( $owner_type_name ) {
			case 'clubs':
				return __( 'clubs', 'rotaract-appointments' );
			case 'districts':
				return __( 'districts', 'rotaract-appointments' );
			case 'ressorts':
				return __( 'ressorts', 'rotaract-appointments' );
			default:
				return $owner_type_name;
		}
	}

}
