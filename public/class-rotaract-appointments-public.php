<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin
 */

/**
 * Markdown parser to convert description to HTML.
 */
require 'vendor/autoload.php';
use Parsedown;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin
 * @author     Ressort IT-Entwicklung - Rotaract Deutschland <it-entwicklung@rotaract.de>
 */
class Rotaract_Appointments_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $rotaract_appointments    The ID of this plugin.
	 */
	private string $rotaract_appointments;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private string $version;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $fullcalendar_version    The current version of this plugin.
	 */
	private string $fullcalendar_version = '5.8.0';

	/**
	 * The Elasticsearch caller.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Rotaract_Elastic_Caller $elastic_caller    The object that handles search calls to the Elasticsearch instance.
	 */
	private Rotaract_Elastic_Caller $elastic_caller;

	/**
	 * The parser.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Parsedown $parser    The current version of this plugin.
	 */
	private Parsedown $parser;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param    string                  $rotaract_appointments    The name of the plugin.
	 * @param    string                  $version        The version of this plugin.
	 * @param    Rotaract_Elastic_Caller $elastic_caller Elasticsearch call handler.
	 * @since    1.0.0
	 */
	public function __construct( string $rotaract_appointments, string $version, Rotaract_Elastic_Caller $elastic_caller ) {

		$this->rotaract_appointments = $rotaract_appointments;
		$this->version               = $version;
		$this->elastic_caller        = $elastic_caller;
		$this->parser                = new Parsedown();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->rotaract_appointments, plugins_url( 'css/public.css', __FILE__ ), array(), $this->version, 'all' );
		wp_enqueue_style( 'fullcalendar', plugins_url( 'node_modules/fullcalendar/main.min.css', __DIR__ ), array(), $this->fullcalendar_version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'fullcalendar', plugins_url( 'node_modules/fullcalendar/main.min.js', __DIR__ ), array(), $this->fullcalendar_version, true );
		wp_enqueue_script( 'fullcalendar-locales', plugins_url( 'node_modules/fullcalendar/locales-all.min.js', __DIR__ ), array( 'fullcalendar' ), $this->fullcalendar_version, true );

	}

	/**
	 * Returns the full include path for a partial.
	 *
	 * @param string $filename Name of the file to be included.
	 *
	 * @return string Path for include statement.
	 */
	private function get_partial( string $filename ): string {
		return plugin_dir_path( __FILE__ ) . 'partials/' . $filename;
	}

	/**
	 * Enqueues all style and script files and init calendar.
	 *
	 * @see appointments_enqueue_scripts
	 * @see init_calendar
	 *
	 * @return String containing empty div tag with id "rotaract-appointments"
	 */
	public function appointments_shortcode(): string {
		add_action( 'wp_footer', 'init_calendar', 999 );

		return '<div id="rotaract-appointments"></div>';
	}

	/**
	 * Initializes the calendar by receiving event data, parse it, and display it.
	 */
	private function init_calendar() {
		$owner        = get_option( 'rotaract_appointment_options' )['rotaract_appointment_owners'];
		$appointments = read_appointments( $owner );

		$events = array_map( 'create_event', $appointments );

		include $this->get_partial( 'shortcode.php' );
	}

	/**
	 * Creates fullcalendar events from search results.
	 *
	 * @param array $appointment Appointment object from search results.
	 *
	 * @return array in form of a fullcalendar event.
	 */
	private function create_event( array $appointment ): array {
		return array(
			'title'       => $appointment->_source->title,
			'start'       => wp_date( 'Y-m-d\TH:i', strtotime( $appointment->_source->begins_at ) ),
			'end'         => wp_date( 'Y-m-d\TH:i', strtotime( $appointment->_source->ends_at ) ),
			'allDay'      => $appointment->_source->all_day,
			'description' => '<div class="event-title">' . $appointment->_source->title . '</div><div class="event-description-inner">' . $this->parser->text( $appointment->_source->description ) . '</div>',
			'owner'       => $appointment->_source->owner,
		);
	}

}
