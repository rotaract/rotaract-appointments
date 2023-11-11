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
	 * The version of fullcalendar.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $fullcalendar_version    The current version of fullcalendar.
	 */
	private string $fullcalendar_version = '6.0.1';

	/**
	 * The version of tippy.js
	 *
	 * @since    1.3.8
	 * @access   private
	 * @var      string    $tippy_version    The current version of tippy.js.
	 */
	private string $tippy_version = '6.3.7';

	/**
	 * The version of marked.js
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string    $marked_version    The current version of marked.js.
	 */
	private string $marked_version = '9.0.3';

	/**
	 * The shortcode Arguments.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array $shortcode_atts    Arguments for calendar shortcode.
	 */
	private array $shortcode_atts = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param    string                  $rotaract_appointments    The name of the plugin.
	 * @param    string                  $version        The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct( string $rotaract_appointments, string $version ) {

		$this->rotaract_appointments = $rotaract_appointments;
		$this->version               = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->rotaract_appointments, plugins_url( 'css/public.css', __FILE__ ), array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'ical-js', plugins_url( 'node_modules/ical.js/build/ical.min.js', __DIR__ ), array(), null, true );

		wp_enqueue_script( 'fullcalendar', plugins_url( 'node_modules/fullcalendar/index.global.min.js', __DIR__ ), array(), $this->fullcalendar_version, true );
		wp_enqueue_script( 'fullcalendar-locales', plugins_url( 'node_modules/@fullcalendar/core/locales-all.global.min.js', __DIR__ ), array( 'fullcalendar' ), $this->fullcalendar_version, true );
		wp_enqueue_script( 'fullcalendar-ical', plugins_url( 'node_modules/@fullcalendar/icalendar/index.global.min.js', __DIR__ ), array( 'fullcalendar', 'ical-js' ), $this->fullcalendar_version, true );
		wp_enqueue_script( 'marked', plugins_url( 'node_modules/marked/marked.min.js', __DIR__ ), array( 'fullcalendar' ), $this->marked_version, true );

		wp_enqueue_script( 'popper', plugins_url( 'node_modules/@popperjs/core/dist/umd/popper.min.js', __DIR__ ), array(), $this->tippy_version, true );
		wp_enqueue_script( 'tippy', plugins_url( 'node_modules/tippy.js/dist/tippy-bundle.umd.min.js', __DIR__ ), array( 'popper' ), $this->tippy_version, true );

		wp_enqueue_script( $this->rotaract_appointments, plugins_url( 'js/public.js', __FILE__ ), array( 'fullcalendar', 'fullcalendar-ical', 'marked', 'tippy' ), $this->version, true );
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
	 * Register new routes for data exchange in public frontend.
	 *
	 * @since    2.1.1
	 */
	public function register_routes() {

		$version = 1;
		$namespace = $this->rotaract_appointments . '/v' . $version;
		$base_ics = '/ics/(?P<name>.+)';
		register_rest_route( $namespace, $base_ics, array(
			'methods' => 'GET',
			'callback' => array( $this, 'proxy_ics' ),
			'args' => array(
				'name' => array(
					'required' => true,
					'validate_callback' => function( $param ) {
						// Check if the given name is present in feeds.
						return in_array( urldecode( $param ), array_column( get_option( 'rotaract_appointment_ics' ), 'name' ) );
					}
				)
			)
		) );

	}

	/**
	 * Return remote ICS feeds based on what was defined in Admin Panel.
	 *
	 * @since    2.1.1
	 */
	public function proxy_ics( $data ) {

		$feed_name      = urldecode( $data[ 'name' ] );
		$feed_index_key = array_search( $feed_name, array_column( get_option( 'rotaract_appointment_ics' ), 'name' ) );
		$feed_url       = get_option( 'rotaract_appointment_ics' )[ $feed_index_key ][ 'url' ];

		$feed_data      = wp_remote_get( $feed_url );
		$feed_data_body = wp_remote_retrieve_body( $feed_data );

		$response = new WP_HTTP_Response( $feed_data_body, 200, array(
			'Content-Type' => 'text/calendar',
		) );
		add_filter( 'rest_pre_serve_request', function () use ( $feed_data_body ) {
			echo $feed_data_body;
			return true;
		}, 0, 2 );
		return $response;

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
	 * @param array $atts user defined attributes in shortcode tag.
	 * @return String containing empty div tag with id "rotaract-appointments"
	 * @see appointments_enqueue_scripts
	 * @see init_calendar
	 */
	public function appointments_shortcode( $atts ): string {
		$this->shortcode_atts[] = shortcode_atts(
			array(
				'views' => 'listQuarter,dayGridMonth',
				'init'  => 'listQuarter',
				'style' => 'normal',
				'days'  => 'null',
			),
			$atts,
			'rotaract-appointments'
		);
		add_action( 'wp_print_footer_scripts', array( $this, 'init_calendar' ), 999 );

		return '<div id="rotaract-appointments-' . ( count( $this->shortcode_atts ) - 1 ) . '" class="rotaract-appointments rotaract-appointments-' . end( $this->shortcode_atts )['style'] . '"></div>';
	}

	/**
	 * Initializes the calendar by receiving event data, parse it, and display it.
	 */
	public function init_calendar() {
		$owners = get_option( 'rotaract_appointment_owners' );
		$feeds  = get_option( 'rotaract_appointment_ics' );

		$shortcodes = array();
		foreach ( $this->shortcode_atts as $shortcode_att ) {
			$shortcodes[] = array(
				'views'     => $shortcode_att['views'],
				'init_view' => $shortcode_att['init'],
				'short'     => 'short' === $shortcode_att['style'] ? 'true' : 'false',
				'days'      => $shortcode_att['days'],
			);
		}

		$event_sources = array();

		if ( defined( 'ROTARACT_APPOINTMENTS_AURORA_URL' ) ) {
			foreach ( $owners as $owner ) {
				$event_sources[] = array(
					'id'        => $owner['name'],
					'title'     => $owner['name'],
					'color'     => $owner['color'],
					'textColor' => '#fff',
					'url'       => ROTARACT_APPOINTMENTS_AURORA_URL . '/' . $owner['type'] . '/' . $owner['slug'] . '/events.json?mode=feed',
				);
			}
		}

		if ( is_array( $feeds ) ) {
			foreach ( $feeds as $feed ) {
				$event_sources[] = array(
					'id'        => $feed['name'],
					'title'     => $feed['name'],
					'url'       => '/wp-json/' . $this->rotaract_appointments . '/v1/ics/' . urlencode( $feed['name'] ),
					'color'     => $feed['color'],
					'textColor' => '#fff',
					'format'    => 'ics',
				);
			}
		}

		include $this->get_partial( 'shortcode.php' );
	}

}
