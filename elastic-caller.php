<?php
/**
 * Interface functions to receive data from Elasticsearch API.
 *
 * @package Rotaract-Appointments
 */

/**
 * Receive appointments from elestic that match the search_param.
 *
 * @param String $api_path absolute API path.
 * @param String $search_param API attributes in JSON format.
 *
 * @return array of appointments
 */
function elastic_request( $api_path, $search_param ) {
	if ( ! defined( 'ROTARACT_ELASTIC_HOST' ) ) {
		return array();
	}
	$url    = ROTARACT_ELASTIC_HOST . $api_path;
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
	return json_decode( $res_body )->hits->hits;
}

/**
 * Receive appointments from specified owners of Rotaract Germany.
 *
 * @param array $appointment_owner owner names filtering the receiving appointments.
 *
 * @return array of appointments
 */
function read_appointments( $appointment_owner ) {
	$appointment_owner = '"' . implode( '","', $appointment_owner ) . '"';
	$search_param      = '{
		"size": "1000",
		"query" : {
			"bool" : {
				"filter" : [
					{ "match": { "publish_on_homepage": true } },
					{ "terms": { "owner_select_names.keyword": [' . $appointment_owner . '] } },
					{ "range": {
						"begins_at": {
							"gte": "now",
							"lte": "now+1y/y"
							}
						}
					}
				]
			}
		}
	}';

	return elastic_request( '/events/_search', $search_param );
}

/**
 * Receive appointments for all clubs of Rotaract Germany.
 *
 * @return array of appointments
 */
function get_all_clubs() {
	$clubs        = array();
	$search_param = '{
		"_source": [ "select_name", "district_name" ],
		"size": "1000",
		"query" : {
			"bool" : {
				"must" : {
					"match_all" : {}
				},
				"filter" : [
					{ "terms": { "status": ["active", "founding", "preparing"] } }
				]
			}
		}
	}';

	$res = elastic_request( '/clubs/_search', $search_param );

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
function get_all_ressorts() {
	$ressorts     = array();
	$search_param = '{
		"_source": [ "select_name", "district_name", "homepage_url" ],
		"size": "1000",
		"query" : {
			"bool" : {
				"must" : {
					"match_all" : {}
				},
				"filter" : [
				]
			}
		}
	}';

	$res = elastic_request( '/ressorts/_search', $search_param );

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
function get_all_districts() {
	$districts    = array();
	$search_param = '{
		"_source": [ "select_name", "district_name", "homepage_url" ],
		"size": "1000",
		"query" : {
			"bool" : {
				"must" : {
					"match_all" : {}
				},
				"filter" : [
				]
			}
		}
	}';

	$res = elastic_request( '/districts/_search', $search_param );

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
function get_all_owner() {
	$clubs     = get_all_clubs();
	$ressorts  = get_all_ressorts();
	$districts = get_all_districts();
	return array(
		'Clubs'     => $clubs,
		'Distrikte' => $districts,
		'Ressorts'  => $ressorts,
	);
}
