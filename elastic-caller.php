<?php

function readAppointments( $appointmentOwner ) {
	$appointmentOwner = '"' . implode( '","', $appointmentOwner ) . '"';
	$searchParam      = '{
		"size": "1000",
		"query" : {
			"bool" : {
				"filter" : [
					{ "match": { "publish_on_homepage": true }},
					{ "terms": { "owner_select_names.keyword": [' . $appointment_owner . '] }},
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

	$header = array(
		'Content-type: application/json',
	);

	$url  = 'hosting.rotaract.de:9200/events/_search';
	$curl = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $searchParam );
	$res = curl_exec( $curl );
	curl_close( $curl );
	return json_decode( $res );
}

function getAllClubs() {
	$clubs       = array();
	$search_param = '{
		"_source": ["select_name", "district_name"],
		"size": "1000",
		"query" : {
			"bool" : {
				"must" : {
					"match_all" : {}
				},
				"filter" : [
					{"terms": { "status": ["active", "founding", "preparing"]}}
				]
			}
		}
	}';

	$header      = array(
		'content-type: application/json',
	);

	$url         = 'hosting.rotaract.de:9200/clubs/_search';
	$curl        = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $searchParam );
	$res = json_decode( curl_exec( $curl ) );
	curl_close( $curl );
	foreach ( $res->hits->hits as $club ) {
		$clubs[] = $club->_source->select_name;
	}

	return $clubs;
}

function getAllRessorts() {
	$ressorts    = array();
	$search_param = '{
		"_source": ["select_name", "district_name", "homepage_url"],
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

	$header      = array(
		'content-type: application/json',
	);

	$url         = 'hosting.rotaract.de:9200/ressorts/_search';
	$curl        = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $searchParam );
	$res = json_decode( curl_exec( $curl ) );
	curl_close( $curl );
	foreach ( $res->hits->hits as $ressort ) {
		$ressorts[] = $ressort->_source->select_name;
	}

	return $ressorts;
}

function getAllDistricts() {
	$districts   = array();
	$search_param = '{
		"_source": ["select_name", "district_name", "homepage_url"],
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

	$header      = array(
		'content-type: application/json',
	);

	$url         = 'hosting.rotaract.de:9200/districts/_search';
	$curl        = curl_init();
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, $searchParam );
	$res = json_decode( curl_exec( $curl ) );
	curl_close( $curl );
	foreach ( $res->hits->hits as $district ) {
		$districts[] = $district->_source->select_name;
	}

	return $districts;
}

function getAllOwner() {
	$clubs     = getAllClubs();
	$ressorts  = getAllRessorts();
	$districts = getAllDistricts();
	return array(
		'Clubs'     => $clubs,
		'Distrikte' => $districts,
		'Ressorts'  => $ressorts,
	);
}
