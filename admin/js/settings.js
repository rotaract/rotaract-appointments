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

/** Initialize LC-select field. */
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

function lcSelectDestroy() {
	const destroyEvent     = new Event( 'lc-select-destroy' );
	const lcSelectElements = document.querySelectorAll( 'select.lc-select' );
	lcSelectElements.forEach(
		function (lcSelect) {
			lcSelect.dispatchEvent( destroyEvent );
		}
	);
}

function addEventListeners() {
	// Add Delete Listeners.
	const delBtns = document.querySelectorAll( 'button.delete-owner' );
	delBtns.forEach(
		function( delBtn ) {
			delBtn.addEventListener( 'click', delOwner );
		}
	);

	document.querySelector( 'button.add-owner' ).addEventListener( 'click', addOwner );
}

function addOwner( event = null ) {
	if ( event ) {
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

	newOwner.querySelectorAll( 'option' ).forEach(
		function (option) {
			option.removeAttribute( 'selected' );
		}
	);

	document.getElementById( 'rotaract-appointment-owner' ).append( newOwner );

	lcSelectInit();
	addEventListeners();
}

function delOwner( event ) {
	event.preventDefault();
	if ( document.querySelectorAll( '.owner-group' ).length < 2 ) {
		addOwner();
	}
	event.target.closest( '.owner-group' ).remove();
}

function changeColor( newValue, targetField) {
	const style           = targetField.closest( '.owner-group' ).style;
	style.backgroundColor = newValue + '25';
	style.borderColor     = newValue;
}
