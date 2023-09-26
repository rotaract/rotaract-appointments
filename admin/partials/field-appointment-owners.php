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
		<div id="searchbox">
			<button type="button" class="button button-secondary" onclick="document.querySelector( '.modal-bg' ).classList.remove( 'show' );">
				<svg class="ais-SearchBox-resetIcon" viewBox="0 0 20 20" width="10" height="10" aria-hidden="true">
					<path d="M8.114 10L.944 2.83 0 1.885 1.886 0l.943.943L10 8.113l7.17-7.17.944-.943L20 1.886l-.943.943-7.17 7.17 7.17 7.17.943.944L18.114 20l-.943-.943-7.17-7.17-7.17 7.17-.944.943L0 18.114l.943-.943L8.113 10z"></path>
				</svg>
			</button>
		</div>
		<div id="hits"></div>
	</div>
</div>
<script type="text/javascript" id="rotaract-appointments">
const meilisearchCredentials = {
	url: '<?= ROTARACT_APPOINTMENTS_SEARCH_URL ?>',
	key: '<?= ROTARACT_APPOINTMENTS_SEARCH_KEY ?>'
}
</script>
