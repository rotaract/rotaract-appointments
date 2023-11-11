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
<?php foreach ( $shortcodes as $index => $shortcode_atts ) : ?>
<div id="calendar-owners-<?php echo wp_json_encode( $index ); ?>" style="display: none">
	<?php foreach ( $owners as $owner ) : ?>
	<div class="calendar-owner" onclick="toggleEventSource(<?php echo wp_json_encode( $index ); ?>, this)" data-owner="<?php echo esc_attr( $owner['name'] ); ?>">
		<span class="fc-list-event-dot" style="border-color: <?php echo esc_attr( $owner['color'] ); ?>"></span>
		<span><?php echo esc_html( $owner['name'] ); ?></span>
	</div>
	<?php endforeach; ?>
	<?php if ( is_array( $feeds ) ) : ?>
		<?php foreach ( $feeds as $feed ) : ?>
	<div class="calendar-owner" onclick="toggleEventSource(<?php echo wp_json_encode( $index ); ?>, this)" data-owner="<?php echo esc_attr( $feed['name'] ); ?>">
		<span class="fc-list-event-dot" style="border-color: <?php echo esc_attr( $feed['color'] ); ?>"></span>
		<span><?php echo esc_html( $feed['name'] ); ?></span>
	</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<?php endforeach; ?>
<script id="rotaract-appointments">
const eventSources = <?php echo wp_json_encode( $event_sources ); ?>;
document.addEventListener("DOMContentLoaded", function() {
	<?php foreach ( $shortcodes as $index => $shortcode_atts ) : ?>
	calendarInit(<?php echo wp_json_encode( $index ); ?>, <?php echo esc_js( $shortcode_atts['short'] ); ?>, <?php echo esc_js( $shortcode_atts['days'] ); ?>, '<?php echo esc_js( $shortcode_atts['views'] ); ?>', '<?php echo esc_js( $shortcode_atts['init_view'] ); ?>');
	<?php endforeach; ?>
});
</script>
