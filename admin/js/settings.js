/**
 * Custom JS intended to be included in the plugin's setting page.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin/js
 */

/* globals instantsearch */
/* globals instantMeiliSearch */
/* globals meilisearchCredentials */

addEventListeners();
let search;

function initSearch() {
	if ( ! search || ! search.started ) {
		const { searchClient } = instantMeiliSearch( meilisearchCredentials.url, meilisearchCredentials.key );
		search = instantsearch(
			{
				indexName: 'Club',
				searchClient
			}
		);
		search.addWidgets(
			[
			instantsearch.widgets.configure(
				{
					attributesToRetrieve: [ 'name', 'slug' ],
					hitsPerPage: 10,
					length: 10,
					limit: 10
				}
			),
			instantsearch.widgets.searchBox(
				{
					container: '#searchbox',
					showReset: false,
					cssClasses: {
						submit: 'button button-primary'
					}
				}
			),
			instantsearch.widgets.hits(
				{
					container: '#hits-clubs',
					templates: {
						item: ( hit ) => `<button type="button" class="button list-btn" onclick="addOwner('${hit.name}', '${hit.slug}', 'clubs')">Rotaract Club ${hit.name}</button>`
					}
				}
			),
			instantsearch.widgets
				.index( { indexName: 'District' } )
				.addWidgets(
					[
					instantsearch.widgets.configure(
						{
							attributesToRetrieve: [ 'name', 'slug' ],
							hitsPerPage: 10,
							length: 10,
							limit: 10
						}
					),
					instantsearch.widgets.hits(
						{
							container: '#hits-districts',
							templates: {
								item: ( hit ) => `<button type="button" class="button list-btn" onclick="addOwner('${hit.name}', '${hit.slug}', 'districts')">${hit.name}</button>`
							}
						}
					)
					]
				),
			instantsearch.widgets
				.index( { indexName: 'Ressort' } )
				.addWidgets(
					[
					instantsearch.widgets.configure(
						{
							attributesToRetrieve: [ 'name', 'slug' ],
							hitsPerPage: 10,
							length: 10,
							limit: 10
						}
					),
					instantsearch.widgets.hits(
						{
							container: '#hits-ressorts',
							templates: {
								item: ( hit ) => `<button type="button" class="button list-btn" onclick="addOwner('${hit.name}', '${hit.slug}', 'ressorts')">${hit.name}</button>`
							}
						}
					)
					]
				),
			instantsearch.widgets
				.index( { indexName: 'Mdio' } )
				.addWidgets(
					[
					instantsearch.widgets.configure(
						{
							attributesToRetrieve: [ 'name', 'slug' ],
							hitsPerPage: 10,
							length: 10,
							limit: 10
						}
					),
					instantsearch.widgets.hits(
						{
							container: '#hits-mdios',
							templates: {
								item: ( hit ) => `<button type="button" class="button list-btn" onclick="addOwner('${hit.name}', '${hit.slug}', 'mdios')">${hit.name}</button>`
							}
						}
					)
					]
				)
			]
		);
		search.start();
	}
}


/**
 * Registers click events to add or delete appointment owner.
 *
 * @see delLine
 * @see addOwner
 */
function addEventListeners() {
	// Add Delete Listeners.
	const delBtns = document.querySelectorAll( 'button.delete-line' );
	delBtns.forEach(
		function (delBtn) {
			delBtn.addEventListener( 'click', delLine );
		}
	);
	// Add Change Color Listeners.
	const colorSelects = document.querySelectorAll( 'select.owner-color, select.feed-color' );
	colorSelects.forEach(
		function( colorSelect ) {
			colorSelect.addEventListener( 'change', changeColor );
		}
	);

	document.querySelector( 'button.add-owner' )?.addEventListener(
		'click',
		function () {
			document.querySelector( '.modal-bg' ).classList.toggle( 'show', true );
			initSearch();
		}
	);
	document.querySelector( 'button.add-ics' )?.addEventListener( 'click', addFeed ); // phpcs:ignore
}

/**
 * Adds new owner whose events to display.
 */
function addOwner( name, slug, type ) {
	const owners = document.querySelectorAll( '.owner-group .owner-name' );
	let newIndex = 0;
	owners.forEach(
		function (owner) {
			let i    = parseInt( owner.getAttribute( 'name' ).split( /[\[\]]/ )[1] );
			newIndex = Math.max( newIndex, i );
		}
	);
	newIndex += 1;

	const newOwner    = document.querySelector( '.owner-group.prototype' ).cloneNode( true );
	const nameInput   = newOwner.querySelector( 'input.owner-name' );
	const slugInput   = newOwner.querySelector( 'input.owner-slug' );
	const typeInput   = newOwner.querySelector( 'input.owner-type' );
	const colorSelect = newOwner.querySelector( 'select.owner-color' );

	nameInput.setAttribute( 'name', nameInput.getAttribute( 'name' ).replace( '-1', newIndex ) );
	nameInput.setAttribute( 'value', name );
	slugInput.setAttribute( 'name', slugInput.getAttribute( 'name' ).replace( '-1', newIndex ) );
	slugInput.setAttribute( 'value', slug );
	typeInput.setAttribute( 'name', typeInput.getAttribute( 'name' ).replace( '-1', newIndex ) );
	typeInput.setAttribute( 'value', type );
	colorSelect.setAttribute( 'name', colorSelect.getAttribute( 'name' ).replace( '-1', newIndex ) );

	newOwner.classList.remove( 'prototype' );
	document.getElementById( 'rotaract-appointment-owner' ).append( newOwner );

	document.querySelector( '.modal-bg' ).classList.remove( 'show' );
	addEventListeners();
}

/**
 * Adds new ics calendar feeds.
 */
function addFeed(event = null) {
	if (event) {
		event.preventDefault();
	}

	const owners = document.querySelectorAll( '.ics-group input.feed-name' );
	let newIndex = 0;
	owners.forEach(
		function (feed) {
			let i    = parseInt( feed.getAttribute( 'name' ).split( /[\[\]]/ )[1] );
			newIndex = Math.max( newIndex, i );
		}
	);
	newIndex += 1;

	let newFeed        = document.querySelector( '.ics-group' ).cloneNode( true );
	let newInputName   = newFeed.querySelector( 'input.feed-name' );
	let newInputUrl    = newFeed.querySelector( 'input.feed-url' );
	let newSelectColor = newFeed.querySelector( 'select.feed-color' );

	newInputName.setAttribute( 'name', newInputName.getAttribute( 'name' ).replace( /\d+/, newIndex ) );
	newInputUrl.setAttribute( 'name', newInputUrl.getAttribute( 'name' ).replace( /\d+/, newIndex ) );
	newSelectColor.setAttribute( 'name', newSelectColor.getAttribute( 'name' ).replace( /\d+/, newIndex ) );

	newInputName.value   = null;
	newInputUrl.value    = null;
	newSelectColor.value = null;

	newFeed.querySelectorAll( 'option' ).forEach(
		function (option) {
			option.removeAttribute( 'selected' );
		}
	);

	document.getElementById( 'rotaract-appointment-ics' ).append( newFeed );

	addEventListeners();
}

/**
 * Removes owner selection.
 */
function delLine(event) {
	event.preventDefault();
	if (document.querySelectorAll( '.appointment-line' ).length < 2) {
		addOwner();
	}
	event.target.closest( '.appointment-line' ).remove();
}

/**
 * Select on_change callback updating the color.
 *
 * @param event
 */
function changeColor( event ) {
	const style           = event.target.closest( '.appointment-line' ).style;
	style.backgroundColor = event.target.value + '25';
	style.borderColor     = event.target.value;
}
