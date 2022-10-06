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
 * @subpackage Rotaract_Appointments/public/partials
 */

?>
<div id="calendar-owners" style="display: none">
	<?php foreach ( $owners as $owner ) : ?>
	<div class="calendar-owner" onclick="toggleOwner(this)" data-owner="<?php echo esc_attr( $owner['name'] ); ?>">
		<span class="fc-list-event-dot" style="border-color: <?php echo esc_attr( $owner['color'] ); ?>"></span>
		<span><?php echo esc_html( $owner['name'] ); ?></span>
	</div>
	<?php endforeach; ?>
</div>
<script type="text/javascript" id="rotaract-appointments">
document.addEventListener("DOMContentLoaded", function() {
	calendarInit(<?php echo wp_json_encode( $event_sources ); ?>, '<?php echo esc_js( $views ); ?>', '<?php echo esc_js( $init_view ); ?>');
});
</script>
