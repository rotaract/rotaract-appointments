/**
 * Custom JS intended to be included in the plugin's setting page.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin/js
 */

addEventListeners();

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
			delBtn.removeEventListener( 'click', delLine );
			delBtn.addEventListener( 'click', delLine );
		}
	);
	// Add Change Color Listeners.
	const colorSelects = document.querySelectorAll( 'select.owner-color, select.feed-color' );
	colorSelects.forEach(
		function( colorSelect ) {
			colorSelect.removeEventListener( 'change', changeColor );
			colorSelect.addEventListener( 'change', changeColor );
		}
	);

	document.querySelector( 'button.add-owner' )?.removeEventListener( 'click', addOrg ); // phpcs:ignore
	document.querySelector( 'button.add-owner' )?.addEventListener( 'click', addOrg ); // phpcs:ignore
	document.querySelector( 'button.add-ics' )?.removeEventListener( 'click', addFeed ); // phpcs:ignore
	document.querySelector( 'button.add-ics' )?.addEventListener( 'click', addFeed ); // phpcs:ignore
}

/**
 * Adds new owner org whose events to display.
 */
function addOrg() {
	const elem = document.getElementById('rotaract_appointment_owners');
	if ( ! elem ) return;
	const data = JSON.parse( elem.value || '' );
	if ( ! data ) return;
	addOwner(data[2], data[1], data[0]);
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
