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
		$this->print_appointment_owners_line( false, $index, $selected_owner['name'], $selected_owner['abbreviation'], $selected_owner['type'], $selected_owner['color'] );
	}
	?>
</div>
<div class="owner-add">
	<button type="button" class="add-owner button" title="<?php esc_attr_e( 'Add', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-plus"></span>
		<?php esc_html_e( 'Add', 'rotaract-appointments' ); ?>
	</button>
</div>
<div class="modal-bg">
	<div class="modal">
		<div id="searchbox"></div>
		<div id="hits"></div>
	</div>
</div>
<script type="text/javascript" id="rotaract-appointments">
const meilisearchCredentials = {
	url: '<?= ROTARACT_APPOINTMENTS_SEARCH_URL ?>',
	key: '<?= ROTARACT_APPOINTMENTS_SEARCH_KEY ?>'
}
</script>
