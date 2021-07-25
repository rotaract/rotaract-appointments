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
<script type="text/javascript" id="rotaract-appointments">
document.addEventListener("DOMContentLoaded", function() {
	const calendarEl = document.getElementById("rotaract-appointments");
	const calendar = new FullCalendar.Calendar(calendarEl, rotaractCalendarOptions);
	calendar.setOption('eventSources', <?php echo wp_json_encode( $event_sources ); ?>);
	calendar.render();
});
</script>
