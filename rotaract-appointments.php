<?php

/**
 * Plugin Name: Rotaract Appointments
 * Plugin URI: https:...rotaract.de/rotaract-appointments
 * Description: Plugin for managing appointments.
 * Version: 1.0
 * Author: Ressort IT-Entwicklung
 * Author: URI: https://rotaract.de
 *
 */
include 'elastic-caller.php';
include 'Parsedown.php';


function appointmentsEnqueueScripts() {

    wp_register_style('rotaract-appointments', plugins_url( 'rotaract-appointments.css', __FILE__));
    wp_enqueue_style('rotaract-appointments');

    wp_enqueue_script('rotaract-appointments', plugins_url( 'rotaract-appointments.js', __FILE__));

    $scriptData = array(
        'appointment-option1' => get_option('appointment-option1'),
        'appointment-option2' => get_option('appointment-option2')
    );

    wp_localize_script('rotaract-appointments', 'scriptData', $scriptData);
}

function appointmentsShortcode($atts) {

    $owner = explode(';', get_option('appointment-option2'));
    $appointments = readAppointments($owner)->hits->hits;

    $output = '';

    $parser = new Parsedown();
    foreach ($appointments as $appointment) {

        $output .= '<div class="appointment"
			id="appointment-' . $appointment->_source->id . '">';

        $output .= '<div class="appointment-info" onclick="toggleAppointmentDescription(' . $appointment->_source->id . ')">';
        $output .= '<div class="appointment-date">';
        $output .= date('d.m.Y', strtotime($appointment->_source->begins_at));
        $output .= '</div>';
	    $output .= '<div class="appointment-details appointment-info-details">';
        $output .= 'Start: <b>' . date('d.m.Y H:i', strtotime($appointment->_source->begins_at)) . '</b><br>';
        $output .= 'Ende: <b>' . date('d.m.Y H:i', strtotime($appointment->_source->ends_at)). '</b><br>';
        $output .= 'Ort: ' . $appointment->_source->address;
        $output .= '</div>';
        $output .= '</div>';


        $output .= '<div class="appointment-content">';

        $output .= '<div class="appointment-header" onclick="toggleAppointmentDescription(' . $appointment->_source->id . ')">';
        $output .= '<div class="appointment-title">';
        $output .= $appointment->_source->title;
        $output .= '</div>';
        $output .= '<div class="appointment-owner">';
        $output .= implode(' | ', $appointment->_source->owner_select_names);
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="appointment-details">';
        $output .= '<div class="appointment-description">';
	    $output .= $parser->text($appointment->_source->description);
        $output .= '<p class="closer"><button class="btn btn-light" onclick="toggleAppointmentDescription(' . $appointment->_source->id . ')">Schließen</button></p>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '</div>';

        $output .= '</div>';
    }

    appointmentsEnqueueScripts();
    return $output;
}
add_shortcode('rotaract-appointments', 'appointmentsShortcode');



add_action("admin_menu", "rotaractAppointmentsSettings");
function rotaractAppointmentsSettings(){
    add_menu_page(
        'Settings for  Appointments',
        'Termine',
        'administrator',
        __FILE__,
        'appointmentsSettings',
        'dashicons-location-alt'
    );
    add_action('admin_init', 'registerAppointmentsSettings');
}

function registerAppointmentsSettings() {
    //register our settings
    register_setting( 'rotaract-appointments-settings-group', 'appointment-option1' );
    register_setting( 'rotaract-appointments-settings-group', 'appointment-option2' );
    register_setting( 'rotaract-appointments-settings-group', 'appointment-list-id[owner]' );

}

function appointmentsSettings() {
    $owner = getAllOwner();
    $ressorts = getAllRessorts();
    $clubs = getAllClubs();
    $districts = getAllDistricts();
    ?>
    <div class="wrap">
        <script type="text/css">
            .selection {
                background-color: green;
            }
        </script>
        <script type="application/javascript">
            function toggleOwnerSelection(owner){
                console.log('hello ' + owner);
                var ownerField = document.getElementById('owners');
                var ownerText = ownerField.value;
                var selectedOwner = document.getElementById(owner);
                selectedOwner.classList.toggle('selection');
                if (ownerText == '') {
                    ownerText += owner;
                } else {
                    ownerText += ';' + owner;
                }
                ownerField.value = ownerText;

            }
        </script>
        <h1>Rotaract Appointments</h1>

        <form method="post" action="options.php">

            <?php settings_fields( 'rotaract-appointments-settings-group' ); ?>
            <?php do_settings_sections( 'rotaract-appointments-settings-group' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Veranstalter</th>
                    <td><input style="width: 1200px;" type="text" id="owners" name="appointment-option2" value="<?php echo esc_attr( get_option('appointment-option2') ); ?>" /></td>
                </tr>
            </table>
            <div>

		<div class="owner-group" >
			<span onclick="toggleOwnerSelection('Rotaract Deutschland Komitee')" id="Rotaract Deutschland Komitee"
			      class="owner"
			      style="color: black; border: 1px solid black;white-space: nowrap;display: inline-block; padding:5px; margin: 5px;">Rotaract Deutschland Komitee</span>
		</div>
                <div class="owner-group" >
                    <div><h3>Clubs</h3></div>
                    <div>
                        <?php
                        $i=0;
                        foreach ($clubs as $districtName => $club) { ?>
                            <h4><?= $districtName?></h4>
                            <div>
                            <?php
                            foreach ($club as $clubname) {
                                ?>
                                <span onclick="toggleOwnerSelection('<?= $clubname ?>')" id="<?= $clubname ?>"
                                      class="owner"
                                      style="color: black; border: 1px solid black;white-space: nowrap;display: inline-block; padding:5px; margin: 5px;"><?= $clubname ?></span>
                                <?php
                                $i++;
                            } ?>
                            </div>

                                <?php
                        } ?>
                    </div><br>
                </div>
                <div class="owner-group" style="overflow: hidden;">
                    <div><h3>Ressorts</h3></div>
                    <div>
                        <?php
                        $i=0;
                        foreach ($ressorts as $ressort) { ?>
                            <span onclick="toggleOwnerSelection('<?= $ressort?>')" id="<?= $ressort?>" class="owner" style="color: black; border: 1px solid black; padding:5px; margin: 5px;"><?= $ressort?></span>
                            <?php
                            $i++;
                        } ?>
                    </div><br>
                </div>
                <div class="owner-group" style="overflow: hidden;">
                    <div><h3>Distrikte</h3></div>
                    <div>
                        <?php
                        $i=0;
                        foreach ($districts as $district) { ?>
                            <span onclick="toggleOwnerSelection('<?= $district?>')" id="<?= $district?>" class="owner" style="color: black; border: 1px solid black;padding:5px; margin: 5px;"><?= $district?></span>
                            <?php
                            $i++;
                        } ?>
                    </div><br>
                </div>
                <?php

                foreach ($owner as $name => $ownerNames) { ?>

                <?php } ?>
            </div>

            <?php submit_button(); ?>

        </form>
    </div>

    <?php
}
