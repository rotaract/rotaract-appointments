/**
 * Custom JS intended to be included in the plugin's setting page.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/admin/js
 */

/* globals lcData */

lcSelectInit();
addEventListeners();

/**
 * Initializes LC-select targeting the select field.
 */
function lcSelectInit() {
	lc_select(
		'select.lc-select',
		{
			enable_search: true,
			wrap_width: 'inherit',
			pre_placeh_opt: true,
			labels: lcData.labels,
			on_change: changeColor
		}
	);
}

/**
 * Removes LC-select resulting in plain HTML select field.
 */
function lcSelectDestroy() {
	const destroyEvent     = new Event( 'lc-select-destroy' );
	const lcSelectElements = document.querySelectorAll( 'select.lc-select' );
	lcSelectElements.forEach(
		function (lcSelect) {
			lcSelect.dispatchEvent( destroyEvent );
		}
	);
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

	document.querySelector( 'button.add-owner' ) ? .addEventListener( 'click', addOwner );
	document.querySelector( 'button.add-ics' ) ? .addEventListener( 'click', addFeed );
}

/**
 * Adds new owner whose events to display.
 */
function addOwner(event = null) {
	if (event) {
		event.preventDefault();
	}

	const owners = document.querySelectorAll( '.owner-group select.owner-name' );
	let newIndex = 0;
	owners.forEach(
		function (owner) {
			let i    = parseInt( owner.getAttribute( 'name' ).split( /\[|\]/ )[1] );
			newIndex = Math.max( newIndex, i );
		}
	);
	newIndex += 1;

	lcSelectDestroy();

	let newOwner       = document.querySelector( '.owner-group' ).cloneNode( true );
	let newSelectName  = newOwner.querySelector( 'select.owner-name' );
	let newSelectColor = newOwner.querySelector( 'select.owner-color' );

	newOwner.style.backgroundColor = null;
	newOwner.style.borderColor     = null;

	newSelectName.setAttribute( 'name', newSelectName.getAttribute( 'name' ).replace( /\d+/, newIndex ) );
	newSelectColor.setAttribute( 'name', newSelectColor.getAttribute( 'name' ).replace( /\d+/, newIndex ) );

	newSelectName.value  = null;
	newSelectColor.value = null;

	newOwner.querySelectorAll( 'option' ).forEach(
		function (option) {
			option.removeAttribute( 'selected' );
		}
	);

	document.getElementById( 'rotaract-appointment-owner' ).append( newOwner );

	lcSelectInit();
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
			let i    = parseInt( feed.getAttribute( 'name' ).split( /\[|\]/ )[1] );
			newIndex = Math.max( newIndex, i );
		}
	);
	newIndex += 1;

	lcSelectDestroy();

	let newFeed        = document.querySelector( '.ics-group' ).cloneNode( true );
	let newInputName   = newFeed.querySelector( 'input.feed-name' );
	let newInputUrl    = newFeed.querySelector( 'input.feed-url' );
	let newSelectColor = newFeed.querySelector( 'select.feed-color' );

	newFeed.style.backgroundColor = null;
	newFeed.style.borderColor     = null;

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

	lcSelectInit();
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
 * LC-select's on_change callback updating the color.
 *
 * @param newValue
 * @param targetField
 */
function changeColor(newValue, targetField) {
	const style           = targetField.closest( '.appointment-line' ).style;
	style.backgroundColor = newValue + '25';
	style.borderColor     = newValue;
}
