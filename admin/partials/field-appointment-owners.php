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
<div id="rotaract-appointment-owner">
	<?php
	if ( count( $selected_owners ) ) {
		foreach ( $selected_owners as $index => $selected_owner ) {
			$this->print_appointment_owners_line( false, $index, $selected_owner['name'], $selected_owner['color'] );
		}
	} else {
		$this->print_appointment_owners_line( true, count( $selected_owners ) );
	}
	?>
</div>
<div class="owner-add">
	<button class="add-owner button" title="<?php esc_attr_e( 'Delete', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-plus"></span>
		<?php esc_html_e( 'Add', 'rotaract-appointments' ); ?>
	</button>
</div>
