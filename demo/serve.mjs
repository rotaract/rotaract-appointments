#!/usr/bin/env node
/**
 * Local demo of the public calendar without WordPress.
 *
 * Serves a page that loads the plugin's public assets in the same order as
 * Rotaract_Appointments_Public enqueues them, fed with dummy JSON and ICS feeds.
 *
 * Usage:
 *   npm install && build/copy-js.sh build/copylist-public
 *   node demo/serve.mjs [port]
 *
 * @package Rotaract_Appointments
 */

import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root   = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
const public_ = path.join( root, 'public' );
const port   = Number( process.argv[2] || process.env.PORT || 8080 );

if ( ! fs.existsSync( path.join( public_, 'js/vendor/fullcalendar.js' ) ) ) {
	console.error( 'Vendor files missing. Run: npm install && build/copy-js.sh build/copylist-public' );
	process.exit( 1 );
}

// Asset order as enqueued by the plugin, so the demo breaks whenever WordPress would.
function assets() {
	const php = fs.readFileSync( path.join( public_, 'class-rotaract-appointments-public.php' ), 'utf8' );
	return [ ...php.matchAll( /wp_enqueue_(script|style)\(\s*[^,]+,\s*plugins_url\(\s*'([^']+)'/g ) ]
		.map( ( [ , kind, src ] ) => ( { kind, src: '/public/' + src } ) );
}

// Dummy data relative to today.
const day = ( offset, hour = 19, minute = 0 ) => {
	const d = new Date();
	d.setDate( d.getDate() + offset );
	d.setHours( hour, minute, 0, 0 );
	return d;
};
const ymd = d => d.toISOString().slice( 0, 10 );

const owners = [
	{ name: 'RAC Musterstadt', color: '#d41367' },
	{ name: 'RAC Beispielhausen', color: '#0050a2' },
	{ name: 'Distrikt 1800', color: '#019fcb' },
];
const feeds = [
	{ name: 'Stammtisch-Kalender (ICS)', color: '#f7a81b' },
];

const events = {
	'RAC Musterstadt': [
		{ title: 'Clubmeeting', start: day( 2 ), end: day( 2, 21 ), location: 'Rathaus, Raum 3', description: '**Agenda**\n\n1. Begrüßung\n2. Berichte der Ressorts\n3. Verschiedenes' },
		{ title: 'Online-Meeting', start: day( 9, 20 ), end: day( 9, 21 ), location: 'https://meet.example.org/rac-musterstadt', description: 'Link siehe Ort.' },
		{ title: 'Charterfeier', start: day( 30, 18 ), end: day( 30, 23, 30 ), location: 'Schlosshotel Musterstadt', description: 'Festlicher Abend mit [Anmeldung](https://example.org/anmeldung).\n\n> Dresscode: festlich' },
		{ title: 'Clubmeeting', start: day( 16 ), end: day( 16, 21 ), location: 'Rathaus, Raum 3' },
	],
	'RAC Beispielhausen': [
		{ title: 'Social Event: Bowling', start: day( 5, 19, 30 ), location: 'Bowlingcenter Beispielhausen' },
		{ title: 'Hands-on: Tafel-Aktion', start: day( 12, 10 ), end: day( 12, 14 ), description: 'Wir helfen bei der Lebensmittelausgabe. Bitte *pünktlich* sein.' },
		{ title: 'Gemeinsames Meeting mit RAC Musterstadt', start: day( 23 ), end: day( 23, 22 ), owner: [ 'RAC Beispielhausen', 'RAC Musterstadt' ] },
	],
	'Distrikt 1800': [
		{ title: 'Distriktkonferenz', start: ymd( day( 20 ) ), end: ymd( day( 23 ) ), allDay: true, location: 'Tagungszentrum Nord', description: 'Drei Tage Workshops, Mitgliederversammlung und Party.\n\n- Fr: Anreise & Get-together\n- Sa: Konferenz\n- So: Brunch' },
		{ title: 'Präsidenten-Treffen', start: ymd( day( 45 ) ), allDay: true },
	],
};
for ( const [ owner, list ] of Object.entries( events ) ) {
	for ( const e of list ) {
		const { title, start, end, allDay, location, description } = e;
		Object.assign( e, { title, start, end, allDay, extendedProps: { owner: e.owner || [ owner ], location, description } } );
	}
}

const icsDate = d => d.toISOString().replace( /[-:]/g, '' ).replace( /\.\d{3}/, '' );
const ics = [
	'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//rotaract-appointments//demo//DE',
	'BEGIN:VEVENT', 'UID:stammtisch@demo', `DTSTAMP:${ icsDate( day( 0 ) ) }`,
	`DTSTART:${ icsDate( day( 3, 20 ) ) }`, `DTEND:${ icsDate( day( 3, 23 ) ) }`, 'RRULE:FREQ=WEEKLY;COUNT=8',
	'SUMMARY:Stammtisch', 'LOCATION:Kneipe am Markt', 'DESCRIPTION:Wöchentlicher Stammtisch – *jeder* ist willkommen!', 'END:VEVENT',
	'BEGIN:VEVENT', 'UID:xmas@demo', `DTSTAMP:${ icsDate( day( 0 ) ) }`,
	`DTSTART:${ icsDate( day( 60, 18 ) ) }`, 'SUMMARY:Weihnachtsfeier', 'END:VEVENT',
	'END:VCALENDAR', '',
].join( '\r\n' );

const eventSources = [
	...owners.map( o => ( { id: o.name, title: o.name, color: o.color, textColor: '#fff', url: '/feed/' + encodeURIComponent( o.name ) + '.json' } ) ),
	...feeds.map( f => ( { id: f.name, title: f.name, color: f.color, textColor: '#fff', url: '/feed/ics', format: 'ics' } ) ),
];

// Shortcode variants, mirroring [rotaract-appointments views=… init=… style=… days=…].
const shortcodes = [
	{ label: 'Default: [rotaract-appointments]', views: 'listQuarter,dayGridMonth', init: 'listQuarter', short: false, days: null },
	{ label: 'Month: [rotaract-appointments init="dayGridMonth"]', views: 'listQuarter,dayGridMonth', init: 'dayGridMonth', short: false, days: null },
	{ label: 'Short: [rotaract-appointments style="short" days="30"]', views: 'listQuarter,dayGridMonth', init: 'listQuarter', short: true, days: 30 },
];

const esc = s => String( s ).replace( /[&<>"]/g, c => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' } )[ c ] );

function page( locale ) {
	const list = assets();
	const owners_html = eventSources.map( s => `<div class="calendar-owner" onclick="toggleEventSource(INDEX, this)" data-owner="${ esc( s.id ) }"><span class="calendar-owner-dot" style="border-color: ${ s.color }"></span> <span>${ esc( s.id ) }</span></div>` ).join( '' );
	return `<!doctype html>
<html lang="${ locale }"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rotaract Appointments – Demo</title>
<style>
body { font-family: system-ui, sans-serif; margin: 0 auto; max-width: 960px; padding: 16px; color: #333; }
h2 { border-bottom: 2px solid #d41367; font-size: 1.1rem; margin: 3rem 0 1rem; padding-bottom: .25rem; }
nav a { margin-right: 1em; }
</style>
${ list.filter( a => a.kind === 'style' ).map( a => `<link rel="stylesheet" href="${ a.src }">` ).join( '\n' ) }
</head><body>
<h1>Rotaract Appointments – Demo</h1>
<nav>Locale: <a href="?locale=de">de</a><a href="?locale=en">en</a><a href="?locale=fr">fr</a></nav>
${ shortcodes.map( ( s, i ) => `<h2>${ esc( s.label ) }</h2>
<div id="rotaract-appointments-${ i }" class="rotaract-appointments rotaract-appointments-${ s.short ? 'short' : 'normal' }"></div>
<div id="calendar-owners-${ i }" style="display: none">${ owners_html.replaceAll( 'INDEX', i ) }</div>` ).join( '\n' ) }
<script>var appointmentsData = ${ JSON.stringify( { locale, calendarBtn: locale === 'de' ? 'Kalender' : 'Calendars' } ) };</script>
${ list.filter( a => a.kind === 'script' ).map( a => `<script src="${ a.src }"></script>` ).join( '\n' ) }
<script>
const eventSources = ${ JSON.stringify( eventSources ) };
document.addEventListener( 'DOMContentLoaded', function () {
${ shortcodes.map( ( s, i ) => `	calendarInit( ${ i }, ${ s.short }, ${ s.days }, '${ s.views }', '${ s.init }' );` ).join( '\n' ) }
} );
</script>
</body></html>`;
}

const types = { '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png' };

http.createServer( ( req, res ) => {
	const url = new URL( req.url, 'http://localhost' );
	const send = ( type, body ) => {
		res.writeHead( 200, { 'content-type': type + '; charset=utf-8' } );
		res.end( body );
	};

	if ( url.pathname === '/' ) {
		return send( 'text/html', page( url.searchParams.get( 'locale' ) || 'de' ) );
	}
	if ( url.pathname === '/feed/ics' ) {
		return send( 'text/calendar', ics );
	}
	const feed = url.pathname.match( /^\/feed\/(.+)\.json$/ );
	if ( feed ) {
		return send( 'application/json', JSON.stringify( events[ decodeURIComponent( feed[1] ) ] || [] ) );
	}
	const file = path.join( root, path.normalize( url.pathname ) );
	if ( file.startsWith( public_ + path.sep ) && fs.existsSync( file ) && fs.statSync( file ).isFile() ) {
		return send( types[ path.extname( file ) ] || 'application/octet-stream', fs.readFileSync( file ) );
	}
	res.writeHead( 404 );
	res.end();
} ).listen( port, () => console.log( `Demo running at http://localhost:${ port }/` ) );
