/**
 * Custom JS intended to be included in the in public view.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/public/js
 */

/* globals appointmentsData */
/* globals eventSources */
/* globals marked */
/* exported calendarInit */
/* exported toggleEventSource */

/**
 * Configuration of FullCalendar's options.
 */
const rotaractCalendarOptions  = ( short, days, viewList, initView ) => ({
	locale: appointmentsData.locale,
	initialView: days ? 'listDaysManual' : initView,
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
		end: eventSources.length ? 'ownerButton' : ''
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
				const calEl = info.el.closest( '.rotaract-appointments' );
				tippy(
					info.el,
					{
						allowHTML: true,
						appendTo: calEl,
						content: createEventContent( info.event ),
						interactive: true,
						maxWidth: 768,
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
		},
		listDaysManual: {
			type: 'list',
			duration: {
				days: days
			}
		}
	}
});

const calendar = [];

/**
 * Initializes Tippy.js, FullCalendar.
 *
 * It generates and renders the FullCalendar, set the sources of the events and registers tippy popups.
 * It is intended to be called once after DOM as finished loading.
 *
 * @param index
 * @param short
 * @param days
 * @param views
 * @param initView
 */
function calendarInit( index, short, days, views, initView ) {
	const calendarEl = document.getElementById( 'rotaract-appointments-' + index );

	const viewList = views.split( ',' );
	if ( ! viewList.includes( initView )) {
		initView = viewList[0];
	}

	const lastCalIndex = calendar.push( new FullCalendar.Calendar( calendarEl, rotaractCalendarOptions( short, days, viewList, initView ) ) ) - 1;
	calendar[lastCalIndex].setOption( 'eventSources', eventSources );
	calendar[lastCalIndex].render();
	tippy(
		'#rotaract-appointments-' + index + ' button.fc-ownerButton-button',
		{
			allowHTML: true,
			content: document.getElementById( 'calendar-owners-' + index ).innerHTML,
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
 * @return string generated HTML tags.
 */
function createEventContent( eventInfo ) {
	const address = eventInfo.extendedProps.address ? eventInfo.extendedProps.address.replace( /https?:\/\/[a-z0-9\-.]+\.[a-zZ]{2,3}(\/\S*)?/g, '<a href="$&" target="_blank" rel="noreferrer" title="' + eventInfo.title + '">$&</a>' ) : null;

	let html = '<p class="event-info">';
	html    += eventInfo.extendedProps.owner ? eventInfo.extendedProps.owner.join( ', ' ) : eventInfo.source.id;
	html    += '</p>';
	html    += '<h5 class="event-title">';
	html    += eventInfo.title;
	html    += '</h5>';
	html    += '<p class="event-info">';
	html    += eventInfo.start.toLocaleDateString( appointmentsData.locale, rotaractDateOptions( eventInfo.allDay ) );
	if (address) {
		html += ', ' + address;
	}
	html += '</p>';
	if (eventInfo?.extendedProps?.description) {
		html += marked.parse(String(eventInfo.extendedProps.description));
	}

	return html;
}

/**
 * Returns the date and time format options.
 *
 * @param {boolean} [allDay] Whether to return the date and time format for a all day event.
 *
 * @return {{month: string, year: string, day: string}} format option.
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
 * @param index
 * @param el The visual HTML toggle element.
 */
function toggleEventSource( index, el ) {
	el.classList.toggle( 'off' );
	const es = calendar[index].getEventSourceById( el.dataset.owner );
	if (es) {
		es.remove();
	} else {
		calendar[index].addEventSource( eventSources.find( b => el.dataset.owner === b.id ) );
	}
}
