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
<select id="<?php echo esc_attr( $args['label_for'] ); ?>"
	name="rotaract_appointment_options[<?php echo esc_attr( $args['label_for'] ); ?>][]"
	class="lc_select"
	multiple>
	<optgroup label="<?php esc_attr_e( 'Rotaract Deutschland', 'rotaract-appointments' ); ?>">
		<option value="Rotaract Deutschland Komitee"<?php echo in_array( 'Rotaract Deutschland Komitee', $options[ $args['label_for'] ], true ) ? ' selected' : ''; ?>>Rotaract Deutschland Komitee</option>
	</optgroup>
	<?php foreach ( $owners as $name => $items ) : ?>
	<optgroup label="<?php echo esc_attr( localize_owner_type_name( $name ) ); ?>">
		<?php foreach ( $items as $item ) : ?>
		<option value="<?php echo esc_attr( $item ); ?>"<?php echo in_array( $item, $options[ $args['label_for'] ], true ) ? ' selected' : ''; ?>><?php echo esc_html( $item ); ?></option>
		<?php endforeach; ?>
	</optgroup>
	<?php endforeach; ?>
</select>
