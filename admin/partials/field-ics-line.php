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
<div class="ics-group appointment-line
<?php
if ( $is_new ) :
	?>
	new-feed<?php endif; ?>" style="background-color: <?php echo esc_attr( $feed_color ); ?>25; border-color: <?php echo esc_attr( $feed_color ); ?>;">
	<input type="text" name="rotaract_appointment_ics[<?php echo esc_attr( $index ); ?>][name]" class="feed-name" value="<?php echo esc_attr( $feed_name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'rotaract-appointments' ); ?>">
	<input type="url" name="rotaract_appointment_ics[<?php echo esc_attr( $index ); ?>][url]" class="feed-url" value="<?php echo esc_attr( $feed_url ); ?>" placeholder="<?php esc_attr_e( 'URL', 'rotaract-appointments' ); ?>">
	<select name="rotaract_appointment_ics[<?php echo esc_attr( $index ); ?>][color]" class="lc-select feed-color" data-placeholder="<?php esc_attr_e( 'Color', 'rotaract-appointments' ); ?>">
		<option hidden selected value>---</option>
		<?php foreach ( $color_palette as $hex => $color ) : ?>
		<option value="<?php echo esc_attr( $hex ); ?>"
			<?php
			if ( ! $is_new && $hex === $feed_color ) :
				?>
			selected<?php endif; ?>>
			<?php echo esc_attr( $color ); ?>
		</option>
		<?php endforeach; ?>
	</select>
	<button class="delete-line" title="<?php esc_attr_e( 'Delete', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-no"></span>
	</button>
</div>
