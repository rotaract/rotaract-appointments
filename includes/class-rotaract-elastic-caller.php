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

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

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
	 * The elasticsearch API client instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Client $client    The elasticsearch API client instance.
	 */
	private Client $client;

	/**
	 * Set the Elasticsearch host URL if defined.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		if ( defined( 'ROTARACT_APPOINTMENTS_CLOUD_ID' ) &&
			defined( 'ROTARACT_APPOINTMENTS_API_ID' ) &&
			defined( 'ROTARACT_APPOINTMENTS_API_KEY' ) ) {
			$this->client = ClientBuilder::create()
				->setElasticCloudId( ROTARACT_APPOINTMENTS_CLOUD_ID )
				->setApiKey( ROTARACT_APPOINTMENTS_API_ID, ROTARACT_APPOINTMENTS_API_KEY )
				->build();
		}
	}

	/**
	 * Check if Elasticsearch host URL is set.
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	public function isset_client(): bool {
		return isset( $this->client );
	}

	/**
	 * Receive appointments from elestic that match the search_param.
	 *
	 * @param array $params Search query.
	 * @return array of appointments
	 */
	private function elastic_request( array $params ): array {
		if ( ! $this->isset_client() ) {
			return array();
		}
		return $this->client->search( $params )['hits']['hits'];
	}

	/**
	 * Receive appointments from specified owners of Rotaract Germany.
	 *
	 * @param array $appointment_owner owner names filtering the receiving appointments.
	 *
	 * @return array of appointments
	 */
	public function get_appointments( array $appointment_owner ): array {
		$params = array(
			'index' => 'events',
			'body'  => array(
				'size'  => '100',
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
										'gte' => 'now-1y/y',
										'lte' => 'now+2y/y',
									),
								),
							),
						),
					),
				),
			),
		);

		return $this->elastic_request( $params );
	}

	/**
	 * Receive appointments for all clubs of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_clubs(): array {
		$params = array(
			'index' => 'clubs',
			'body'  => array(
				'_source' => array(
					'select_name',
					'district_name',
				),
				'size'    => '250',
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
			),
		);

		$res = $this->elastic_request( $params );

		// Unwrap array of club objects.
		return array_map( fn( array $club ): string => $club['_source']['select_name'], $res );
	}

	/**
	 * Receive appointments for all departments of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_ressorts(): array {
		$params = array(
			'index' => 'ressorts',
			'body'  => array(
				'_source' => array(
					'select_name',
				),
				'size'    => '20',
			),
		);

		$res = $this->elastic_request( $params );

		// Unwrap array of ressort objects.
		return array_map( fn( array $ressort ): string => $ressort['_source']['select_name'], $res );
	}

	/**
	 * Receive appointments for all districts of Rotaract Germany.
	 *
	 * @return array of appointments
	 */
	public function get_all_districts(): array {
		$params = array(
			'index' => 'districts',
			'body'  => array(
				'_source' => array(
					'select_name',
				),
				'size'    => '20',
			),
		);

		$res = $this->elastic_request( $params );

		// Unwrap array of ressort objects.
		return array_map( fn( array $district ): string => $district['_source']['select_name'], $res );
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
