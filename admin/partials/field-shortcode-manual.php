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
<h3 style="margin-top:0">Shortcode</h3>
<p><?php esc_html_e( 'You need to add this shortcode wherever you want your calendar to appear:', 'rotaract-appointments' ); ?></p>
<pre>[rotaract-appointments]</pre>

<h3><?php esc_html_e( 'Customization', 'rotaract-appointments' ); ?></h3>
<p><?php esc_html_e( 'You can customize your calendar by adding attributes to your shortcode. Those are the following:', 'rotaract-appointments' ); ?></p>

<h4>days</h4>
<p style="padding-left:1em;"><?php esc_html_e( 'If you just wanted to show the events of the next X (e.g. 50) days in a list, add the day attribute. All other params will be ignored which means you cannot have other views than this list.', 'rotaract-appointments' ); ?></p>
<pre style="padding-left:1em;"><strong>[rotaract-appointments days=50]</strong></pre>

<h4>views</h4>
<p style="padding-left:1em;"><?php esc_html_e( 'In your calendar visitors may switch between different views. You can define a list of views that they can choose from. Please set those as a comma-separated list without empty spaces.', 'rotaract-appointments' ); ?></p>
<pre style="padding-left:1em;"><strong>[rotaract-appointments views=listQuarter,dayGridMonth]</strong></pre>
<p style="padding-left:1em;"><?php esc_html_e( 'Possible values are', 'rotaract-appointments' ); ?>: <code>listDay</code> <code>listWeek</code> <code>listMonth</code> <code>listQuarter</code> <code>listYear</code> <code>dayGridDay</code> <code>dayGridWeek</code> <code>dayGridMonth</code></p>
<p style="padding-left:1em;"><?php esc_html_e( 'Default value', 'rotaract-appointments' ); ?>: <code>listQuarter,dayGridMonth</code></p>

<h4>init</h4>
<p style="padding-left:1em;"><?php esc_html_e( 'If you have defined multiple views you may also want to say which one shall be shown by default. It has to be one of those you declared in views attribute.', 'rotaract-appointments' ); ?></p>
<pre style="padding-left:1em;"><strong>[rotaract-appointments views=listQuarter,dayGridMonth init=listQuarter]</strong></pre>
<p style="padding-left:1em;"><?php esc_html_e( 'Default value', 'rotaract-appointments' ); ?>: <?php esc_html_e( 'first item in', 'rotaract-appointments' ); ?> <code>views</code></p>

<h4>style</h4>
<p style="padding-left:1em;"><?php esc_html_e( 'Lets say you want to display your events in a sidebar. You can choose a short layout that will fit into a widget. Please notice that you cannot have multiple views then but you can set which one is used.', 'rotaract-appointments' ); ?></p>
<pre style="padding-left:1em;"><strong>[rotaract-appointments style=short views=listQuarter]</strong></pre>
<p style="padding-left:1em;"><?php esc_html_e( 'Default value', 'rotaract-appointments' ); ?>: <code>normal</code></p>
