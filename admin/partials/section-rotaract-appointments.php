<?php
/**
 * Provide a admin-facing view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link        https://github.com/rotaract/rotaract-appointments
 * @since       1.0.0
 *
 * @package     Rotaract_Appointments
 * @subpackage  Rotaract_Appointments/admin/partials
 */

?>
<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Customize your calendar events here.', 'rotaract-appointments' ); ?></p>
