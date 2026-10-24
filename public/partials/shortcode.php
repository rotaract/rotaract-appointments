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
<script id="rotaract-appointments">
const eventSources = <?php echo wp_json_encode( $event_sources ); ?>;
document.addEventListener("DOMContentLoaded", function() {
	<?php foreach ( $shortcodes as $index => $shortcode_atts ) : ?>
	calendarInit(<?php echo wp_json_encode( $index ); ?>, <?php echo esc_js( $shortcode_atts['short'] ); ?>, <?php echo esc_js( $shortcode_atts['days'] ); ?>, '<?php echo esc_js( $shortcode_atts['views'] ); ?>', '<?php echo esc_js( $shortcode_atts['init_view'] ); ?>');
	<?php endforeach; ?>
});
</script>
