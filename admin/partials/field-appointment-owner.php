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
<div class="owner-group
<?php
if ( $is_new ) :
	?>
	new-owner<?php endif; ?>">
	<select name="rotaract_appointment_owners[<?php echo esc_attr( $index ); ?>][name]" class="lc-select owner-name" data-placeholder="<?php esc_attr_e( 'Owner', 'rotaract-appointments' ); ?>">
		<optgroup label="<?php esc_attr_e( 'Rotaract Deutschland', 'rotaract-appointments' ); ?>">
			<option value="Rotaract Deutschland Komitee"
				<?php
				if ( ! $is_new && 'Rotaract Deutschland Komitee' === $owner_name ) :
					?>
					selected<?php endif; ?>>Rotaract Deutschland Komitee</option>
		</optgroup>
		<?php foreach ( $owners as $type_name => $items ) : ?>
		<optgroup label="<?php echo esc_attr( Rotaract_Appointments_I18n::localize_owner_type_name( $type_name ) ); ?>">
			<?php foreach ( $items as $item ) : ?>
			<option value="<?php echo esc_attr( $item ); ?>"
				<?php
				if ( ! $is_new && $item === $owner_name ) :
					?>
				selected<?php endif; ?>><?php echo esc_html( $item ); ?></option>
			<?php endforeach; ?>
		</optgroup>
		<?php endforeach; ?>
	</select>
	<select name="rotaract_appointment_owners[<?php echo esc_attr( $index ); ?>][color]" class="lc-select owner-color" data-placeholder="<?php esc_attr_e( 'Color', 'rotaract-appointments' ); ?>">
		<?php foreach ( $color_palette as $hex => $color ) : ?>
		<option value="<?php echo esc_attr( $hex ); ?>"
			<?php
			if ( ! $is_new && $hex === $owner_color ) :
				?>
			selected<?php endif; ?>><?php echo esc_attr( $color ); ?></option>
		<?php endforeach; ?>
	</select>
	<button class="delete-owner button" title="<?php esc_attr_e( 'Delete', 'rotaract-appointments' ); ?>">
		<span class="dashicons dashicons-no"></span>
	</button>
</div>
