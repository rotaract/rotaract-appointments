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
<div class="owner-group appointment-line<?php
if ( $is_prototype ) :
	?> prototype<?php
endif; ?>" style="background-color: <?php echo esc_attr( $owner_color ?: array_key_first( $color_palette ) ); ?>25; border-color: <?php echo esc_attr( $owner_color ?: array_key_first( $color_palette ) ); ?>;">
	<input type="text"
		   name="rotaract_appointment_owners[<?php echo esc_attr( $index ); ?>][name]"
		   class="owner-name"
		   value="<?php echo esc_attr( $owner_name ); ?>" readonly>
	<input type="hidden"
		   name="rotaract_appointment_owners[<?php echo esc_attr( $index ); ?>][abbreviation]"
		   class="owner-abbreviation"
		   value="<?php echo esc_attr( $owner_abbreviation ); ?>">
	<input type="hidden"
		   name="rotaract_appointment_owners[<?php echo esc_attr( $index ); ?>][type]"
		   class="owner-type"
		   value="<?php echo esc_attr( $owner_type ); ?>">
	<select name="rotaract_appointment_owners[<?php echo esc_attr( $index ); ?>][color]"
			class="owner-color">
		<?php foreach ( $color_palette as $hex => $color ) : ?>
		<option value="<?php echo esc_attr( $hex ); ?>"<?php
			if ( $hex === $owner_color ) :
				?> selected<?php endif; ?>>
			<?php echo esc_attr( $color ); ?>
		</option>
		<?php endforeach; ?>
	</select>
	<button class="delete-line" title="<?php esc_attr_e( 'Delete', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-no"></span>
	</button>
</div>
