<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      3.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin/partials
 */

?>
<div class="error notice">
	<p>
		<strong><?php esc_html_e( 'Rotaract Events', 'rotaract-appointments' ); ?>:</strong>
		<?php esc_html_e( 'Please set Meilisearch Host in your WordPress configuration!', 'rotaract-appointments' ); ?>
	</p>
</div>
