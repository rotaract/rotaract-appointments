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
<div id="rotaract-appointment-ics">
	<?php
	if ( count( $ics_feeds ) ) {
		foreach ( $ics_feeds as $index => $ics_feed ) {
			$this->print_ics_line( false, $index, $ics_feed['name'], $ics_feed['url'], $ics_feed['color'] );
		}
	} else {
		$this->print_ics_line( true, count( $ics_feeds ) );
	}
	?>
</div>
<div class="ics-add">
	<button class="add-ics button" title="<?php esc_attr_e( 'Add', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-plus"></span>
		<?php esc_html_e( 'Add', 'rotaract-appointments' ); ?>
	</button>
</div>
