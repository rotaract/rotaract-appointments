<?php
/**
 * Interface functions to receive data from Elasticsearch API.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/includes
 */

/**
 * Interface functions to receive data from Elasticsearch API.
 *
 * @link       https://github.com/rotaract/rotaract-appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/includes
 */
class Rotaract_Elastic_Caller {

	/**
	 * The host URL auf the Elasticsearch instance containing Rotaract events.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $elastic_host    The host URL auf the Elasticsearch instance containing Rotaract events.
	 */
	private string $elastic_host;

	/**
	 * Set the Elasticsearch host URL if defined.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ROTARACT_ELASTIC_HOST' ) ) {
			$this->elastic_host = trailingslashit( ROTARACT_ELASTIC_HOST );
		}
	}

	/**
	 * Receive appointments from elestic that match the search_param.
	 *
	 * @param String $api_path absolute API path.
	 * @param String $search_param API attributes in JSON format.
	 *
	 * @return array of appointments
	 */
	private function elastic_request( string $api_path, string $search_param ): array {
		if ( ! $this->isset_elastic_host() ) {
			return array();
		}
		$url    = $this->elastic_host . $api_path;
		$header = array(
			'Content-Type' => 'application/json',
		);

		$res      = wp_remote_post(
			$url,
			array(
				'headers' => $header,
				'body'    => $search_param,
			)
		);
		$res_body = wp_remote_retrieve_body( $res );

		$result = json_decode( $res_body )->hits->hits;
		return $result ? $result : array();
	}


	/**
	 * Check if Elasticsearch host URL is set.
	 *
	 * @return boolean
	 */
	public function isset_elastic_host(): bool {
		return isset( $this->elastic_host );
	}

	/**
	 * Receive appointments from specified owners of Rotaract Germany.
	 *
	 * @param array $appointment_owner owner names filtering the receiving appointments.
	 *
	 * @return array of appointments
	 */
	public function get_appointments( array $appointment_owner ): array {
		$path         = 'events/_search';
		$search_param = array(
			'size'  => '1000',
			'query' => array(
				'bool' => array(
					'filter' => array(
						array(
							'match' => array(
								'publish_on_homepage' => true,
							),
						),
						array(
							'terms' => array(
								'owner_select_names.keyword' => $appointment_owner,
							),
						),
						array(
							'range' => array(
								'begins_at' => array(
									'gte' => 'now',
									'lte' => 'now+1y/y',
								),
							),
						),
					),
				),
			),
		);

		return $this->elastic_request( $path, wp_json_encode( $search_param ) );
	}

	/**
	 * Receive appointments for all clubs of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_clubs(): array {
		$clubs        = array();
		$path         = 'clubs/_search';
		$search_param = array(
			'_source' => array(
				'select_name',
				'district_name',
			),
			'size'    => '1000',
			'query'   => array(
				'constant_score' => array(
					'filter' => array(
						'terms' => array(
							'status' => array(
								'active',
								'founding',
								'preparing',
							),
						),
					),
				),
			),
		);

		$res = $this->elastic_request( $path, wp_json_encode( $search_param ) );

		foreach ( $res as $club ) {
			$clubs[] = $club->_source->select_name;
		}

		return $clubs;
	}

	/**
	 * Receive appointments for all departments of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_ressorts(): array {
		$ressorts     = array();
		$path         = 'ressorts/_search';
		$search_param = array(
			'_source' => array(
				'select_name',
			),
			'size'    => '1000',
		);

		$res = $this->elastic_request( $path, wp_json_encode( $search_param ) );

		foreach ( $res as $ressort ) {
			$ressorts[] = $ressort->_source->select_name;
		}

		return $ressorts;
	}

	/**
	 * Receive appointments for all districts of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_districts(): array {
		$districts    = array();
		$path         = 'districts/_search';
		$search_param = array(
			'_source' => array(
				'select_name',
			),
			'size'    => '1000',
		);

		$res = $this->elastic_request( $path, wp_json_encode( $search_param ) );

		foreach ( $res as $district ) {
			$districts[] = $district->_source->select_name;
		}

		return $districts;
	}

	/**
	 * Receive all appointments of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_owners(): array {
		$clubs     = $this->get_all_clubs();
		$ressorts  = $this->get_all_ressorts();
		$districts = $this->get_all_districts();
		return array(
			'clubs'     => $clubs,
			'districts' => $districts,
			'ressorts'  => $ressorts,
		);
	}
}
