<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin/partials
 */

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form method="post" action="options.php">
		<?php
		// Output security fields for the registered setting "rotaract_appointments".
		settings_fields( 'rotaract_appointments' );
		// Output setting sections and their fields.
		// Sections are registered for "rotaract", each field is registered to a specific section.
		do_settings_sections( 'rotaract' );
		// Output save settings button.
		submit_button();
		?>
	</form>
</div>
