<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name: Rotaract Appointments
 * Plugin URI:  https://github.com/rotaract/rotaract-appointments
 * Description: Plugin for managing "mein Rotaract" appointments.
 * Version: 1.0.0
 * Author: Ressort IT-Entwicklung - Rotaract Deutschland
 * Author URI: https://rotaract.de/ueber-rotaract/rdk/
 * Text Domain: rotaract-appointments
 * Domain Path: /languages
 *
 * @link https://github.com/rotaract/rotaract-appointments
 * @since 1.0.0
 * @package Rotaract_Appointments
 * @category Core
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ROTARACT_APPOINTMENTS_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rotaract-appointments.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rotaract_appointments() {

	$plugin = new Rotaract_Appointments();
	$plugin->run();

}
run_rotaract_appointments();
