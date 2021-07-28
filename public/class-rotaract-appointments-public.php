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
require plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';
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
	private string $fullcalendar_version = '5.9.0';

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

		wp_enqueue_style( 'fullcalendar', plugins_url( 'node_modules/fullcalendar/main.min.css', __DIR__ ), array(), $this->fullcalendar_version, 'all' );
		wp_enqueue_style( $this->rotaract_appointments, plugins_url( 'css/public.css', __FILE__ ), array( 'fullcalendar' ), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'fullcalendar', plugins_url( 'node_modules/fullcalendar/main.min.js', __DIR__ ), array(), $this->fullcalendar_version, true );
		wp_enqueue_script( 'fullcalendar-locales', plugins_url( 'node_modules/fullcalendar/locales-all.min.js', __DIR__ ), array( 'fullcalendar' ), $this->fullcalendar_version, true );

		wp_enqueue_script( 'popper', plugins_url( 'node_modules/@popperjs/core/dist/umd/popper.min.js', __DIR__ ), array(), $this->version, true );
		wp_enqueue_script( 'tippy', plugins_url( 'node_modules/tippy.js/dist/tippy-bundle.umd.min.js', __DIR__ ), array( 'popper' ), $this->version, true );

		wp_enqueue_script( $this->rotaract_appointments, plugins_url( 'js/public.js', __FILE__ ), array( 'fullcalendar', 'fullcalendar-locales', 'tippy' ), $this->version, true );
		wp_localize_script(
			$this->rotaract_appointments,
			'appointmentsData',
			array(
				'locale'      => explode( '_', get_locale(), 2 )[0],
				'calendarBtn' => __( 'Calendars', 'rotaract-appointments' ),
			)
		);

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
		add_action( 'wp_print_footer_scripts', array( $this, 'init_calendar' ), 999 );

		return '<div id="rotaract-appointments"></div>';
	}

	/**
	 * Initializes the calendar by receiving event data, parse it, and display it.
	 */
	public function init_calendar() {
		$owners       = get_option( 'rotaract_appointment_owners' );
		$owner_names  = array_map(
			function ( $o ) {
				return $o['name'];
			},
			$owners
		);
		$appointments = $this->elastic_caller->get_appointments( $owner_names );

		$event_sources = array();

		foreach ( $owners as $owner ) {
			$owner_appointments = array_filter(
				$appointments,
				function ( $a ) use ( $owner ) {
					return in_array( $owner['name'], $a->_source->owner_select_names, true );
				}
			);

			$event_sources[] = array(
				'id'        => $owner['name'],
				'title'     => $owner['name'],
				'color'     => $owner['color'],
				'textColor' => '#fff',
				'events'    => array_values( array_map( array( $this, 'create_event' ), $owner_appointments ) ),
			);
		}

		include $this->get_partial( 'shortcode.php' );
	}

	/**
	 * Creates fullcalendar events from search results.
	 *
	 * @param object $appointment Appointment object from search results.
	 *
	 * @return array in form of a fullcalendar event.
	 */
	private function create_event( object $appointment ): array {
		return array(
			'title'       => $appointment->_source->title,
			'start'       => wp_date( 'Y-m-d\TH:i', strtotime( $appointment->_source->begins_at ) ),
			'end'         => wp_date( 'Y-m-d\TH:i', strtotime( $appointment->_source->ends_at ) ),
			'allDay'      => $appointment->_source->all_day,
			'address'     => $appointment->_source->address,
			'description' => $this->parser->text( $appointment->_source->description ),
			'owner'       => $appointment->_source->owner_select_names,
		);
	}

}
