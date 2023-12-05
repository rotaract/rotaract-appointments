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
	<button type="button" class="add-owner button" title="<?php esc_attr_e( 'Add', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-plus"></span>
		<?php esc_html_e( 'Add', 'rotaract-appointments' ); ?>
	</button>
</div>
<div class="modal-bg">
	<div class="modal">
		<div id="searchbox">
			<button type="button" class="button button-secondary" onclick="document.querySelector( '.modal-bg' ).classList.remove( 'show' );">
				<span class="dashicons dashicons-no"></span>
			</button>
		</div>
		<div id="hits">
			<h3><?php esc_html_e( 'clubs', 'rotaract-appointments' ); ?></h3>
			<div id="hits-clubs"></div>
			<hr>
			<h3><?php esc_html_e( 'districts', 'rotaract-appointments' ); ?></h3>
			<div id="hits-districts"></div>
			<hr>
			<h3><?php esc_html_e( 'ressorts', 'rotaract-appointments' ); ?></h3>
			<div id="hits-ressorts"></div>
			<hr>
			<h3><?php esc_html_e( 'mdios', 'rotaract-appointments' ); ?></h3>
			<div id="hits-mdios"></div>
		</div>
	</div>
</div>
<script id="rotaract-appointments">
const meilisearchCredentials = {
	url: '<?php echo esc_html( ROTARACT_APPOINTMENTS_SEARCH_URL ); ?>',
	key: '<?php echo esc_html( ROTARACT_APPOINTMENTS_SEARCH_KEY ); ?>'
}
</script>
