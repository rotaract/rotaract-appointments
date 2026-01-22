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
	$this->print_appointment_owners_line( true );
	foreach ( $selected_owners as $index => $selected_owner ) {
		$this->print_appointment_owners_line( false, $index, $selected_owner['name'], $selected_owner['slug'], $selected_owner['type'], $selected_owner['color'] );
	}
	?>
</div>
<div class="owner-add">
	<select id="rotaract_appointment_owners">
		<option value="">- <?php esc_html_e( 'Add', 'rotaract-appointments' ); ?> -</option>
		<?php
		foreach ($orgs as $type => $typed_orgs) : ?>
		<optgroup label="<?php esc_attr_e( $type, 'rotaract-appointments' ); ?>">
			<?php
			foreach ($typed_orgs as $org) : ?>
			<option value='["<?= esc_attr( $type ) ?>","<?= esc_attr( $org->slug )?>","<?= esc_attr( $org->name ); ?>"]'><?= esc_attr( $org->name); ?></option>
			<?php endforeach; ?>
		</optgroup>
		<?php endforeach; ?>
	</select>
	<button type="button" class="add-owner button" title="<?php esc_attr_e( 'Add', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-plus"></span>
		<?php esc_html_e( 'Add', 'rotaract-appointments' ); ?>
	</button>
</div>
