/**
 * Custom JS intended to be included in the in public view.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/public/js
 */

/* globals appointmentsData */
/* exported calendarInit */
/* exported toggleOwner */

/**
 * Configuration of FullCalendar's options.
 */
const rotaractCalendarOptions  = ( short, viewList, initView ) => ({
	locale: appointmentsData.locale,
	initialView: initView,
	customButtons: {
		ownerButton: {
			text: appointmentsData.calendarBtn
		}
	},
	headerToolbar: {
		start: short ? '' : 'prev,next',
		center: short ? '' : 'title',
		end: short ? '' : viewList.join( ',' )
	},
	footerToolbar: {
		start: short ? 'prev,today,next' : 'today',
		center: '',
		end: 'ownerButton'
	},
	height: 'auto',
	views: {
		list: {
			eventDidMount( info ) {
				let elem       = document.createElement( 'div' );
				elem.innerHTML = createEventContent( info.event );
				elem.classList.add( 'event-description' );
				info.el.append( elem );
			},
			eventClick( info ) {
				if ( ! info.jsEvent.target.href) {
					info.el.classList.toggle( 'show' );
				}
			}
		},
		dayGrid: {
			eventDidMount( info ) {
				const calEl = info.el.closest( '#rotaract-appointments' );
				tippy(
					info.el,
					{
						allowHTML: true,
						appendTo: calEl,
						content: createEventContent( info.event ),
						interactive: true,
						theme: 'rotaract',
						trigger: 'click',
						onShow() {
							calEl.style.setProperty( '--fc-event-bg-color', info.event.source.internalEventSource._raw.color );
						}
					}
				);
			}
		},
		listYear: {
			type: 'list',
			duration: {
				months: 12
			}
		},
		listQuarter: {
			type: 'list',
			duration: {
				months: 3,
				buttonText: 'Quartal'
			}
		}
	}
})

let calendar;

/**
 * Initializes Tippy.js, FullCalendar.
 *
 * It generates and renders the FullCalendar, set the sources of the events and registers tippy popups.
 * It is intended to be called once after DOM as finished loading.
 *
 * @param eventSources The sources of the displayed events.
 */
function calendarInit( eventSources, short, views, initView ) {
	const calendarEl = document.getElementById( 'rotaract-appointments' );

	const viewList = views.split( ',' );
	if ( ! viewList.includes( initView )) {
		initView = viewList[0];
	}

	calendar = new FullCalendar.Calendar( calendarEl, rotaractCalendarOptions( short, viewList, initView ) );
	calendar.setOption( 'eventSources', eventSources );
	calendar.render();
	tippy(
		'button.fc-ownerButton-button',
		{
			allowHTML: true,
			content: document.getElementById( 'calendar-owners' ).innerHTML,
			interactive: true,
			theme: 'rotaract',
			trigger: 'click'
		}
	);
}

/**
 * Generates the HTML representation of an event's content aka description (incl. time and venue).
 *
 * @param {object} eventInfo The info data object of an event.
 *
 * @return The generated HTML tags.
 */
function createEventContent( eventInfo ) {
	const address = eventInfo.extendedProps.address.replace( /https?:\/\/[a-z0-9\-.]+\.[a-zZ]{2,3}(\/\S*)?/g, '<a href="$&" target="_blank" rel="noreferrer" title="' + eventInfo.title + '">$&</a>' );

	let html = '<h5 class="event-title">';
	html    += eventInfo.title;
	html    += '</h5>';
	html    += '<p class="event-info">';
	html    += eventInfo.start.toLocaleDateString( appointmentsData.locale, rotaractDateOptions( eventInfo.allDay ) );
	html    += ', ';
	html    += address;
	html    += '</p>';
	html    += eventInfo.extendedProps.description;

	return html;
}

/**
 * Returns the date and time format options.
 *
 * @param {boolean} [allDay] Whether to return the date and time format for a all day event.
 *
 * @return The format option.
 */
function rotaractDateOptions( allDay = false ) {
	let options = {
		year: 'numeric',
		month: '2-digit',
		day: '2-digit'
	};
	if ( ! allDay ) {
		options.hour   = '2-digit';
		options.minute = '2-digit';
	}
	return options;
}

/**
 * Toggles the display attribute of all events of an certain owner.
 *
 * @param el The visual HTML toggle element.
 */
function toggleOwner( el ) {
	el.classList.toggle( 'off' );
	calendar.getEvents().forEach(
		function (e) {
			if ( e.source.id === el.dataset.owner ) {
				if (e.display === 'none') {
					e.setProp( 'display', 'auto' );
				} else {
					e.setProp( 'display', 'none' );
				}
			}
		}
	);
}
