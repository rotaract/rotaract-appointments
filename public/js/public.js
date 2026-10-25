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

const rotaractCalendarPickers = {};

/**
 * Configuration of FullCalendar's options.
 *
 * @param index
 * @param short
 * @param days
 * @param viewList
 * @param initView
 */
const rotaractCalendarOptions = ( index, short, days, viewList, initView ) => ({
	locale: appointmentsData.locale,
	initialView: days ? 'listDaysManual' : initView,
	buttons: {
		ownerButton: {
			class: 'pos-relative',
			text: appointmentsData.calendarBtn,
			didMount({ el: refEl }) {
				const floatEl = createCalendarPicker(index);
				rotaractCalendarPickers[index] = { refEl, floatEl, open: false, cleanup: null };
			},
			click() {
				const p = rotaractCalendarPickers[index];
				if (p.open) {
					p.floatEl.remove();
					p.cleanup();
				} else {
					document.body.append(p.floatEl);
					p.cleanup = window.FloatingUIDOM.autoUpdate( p.refEl, p.floatEl, () => {
						window.FloatingUIDOM.computePosition( p.refEl, p.floatEl, {
							placement: 'bottom',
							middleware: [
								window.FloatingUIDOM.offset(4),
								window.FloatingUIDOM.flip(),
								window.FloatingUIDOM.shift({ padding: 4 })
							]
						} ).then( ( {x, y} ) => {
							Object.assign(p.floatEl.style, {
								left: `${x}px`,
								top: `${y}px`
							} );
						} );
					} );
				}
				p.open = !p.open;
			}
		}
	},
	headerToolbar: {
		start: short ? '' : 'prev,next',
		center: short ? '' : 'title',
		end: short ? '' : viewList.join( ',' )
	},
	footerToolbar: {
		start: short ? 'prev,next' : 'today',
		center: '',
		end: eventSources.length > 1 ? 'ownerButton' : ''
	},
	footerToolbarClass: 'toolbar-footer',
	height: 'auto',
	views: {
		list: {
			listItemEventInnerClass: 'appointment-inner',
			listDayHeaderInnerClass: 'day-header-inner',
			eventDidMount( info ) {
				let elem = document.createElement( 'div' );
				elem.innerHTML = createEventContent( info.event );
				elem.classList.add( 'event-description' );
				info.el.parentElement.append( elem );
			},
			eventClick( info ) {
				if ( ! info.jsEvent.target.href) {
					info.el.parentElement.classList.toggle( 'show' );
				}
			}
		},
		dayGrid: {
			eventDidMount( info ) {
				const calEl = info.el.closest( '.rotaract-appointments' );
				let dialog = document.createElement( 'dialog' );
				dialog.id = `event-${info.event._def.defId}`;
				dialog.setAttribute('closedby', 'any');
				dialog.innerHTML = createEventContent( info.event );
				calEl.append(dialog);
			},
			eventClick( info ) {
				document.getElementById(`event-${info.event._def.defId}`).showModal();
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
 * Initializes FullCalendar.
 *
 * It generates and renders the FullCalendar and sets the sources of the events.
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

	const lastCalIndex = calendar.push( new FullCalendar.Calendar( calendarEl, rotaractCalendarOptions( index, short, days, viewList, initView ) ) ) - 1;
	calendar[lastCalIndex].setOption( 'eventSources', eventSources );
	calendar[lastCalIndex].render();
}

/**
 * Generates the HTML representation of an event's content aka description (incl. time and venue).
 *
 * @param {object} eventInfo The info data object of an event.
 *
 * @return string generated HTML tags.
 */
function createEventContent( eventInfo ) {
	const location = eventInfo.extendedProps.location ? eventInfo.extendedProps.location.replace( /https?:\/\/[a-z0-9\-.]+\.[a-zZ]{2,3}(\/\S*)?/g, '<a href="$&" target="_blank" rel="noreferrer" title="' + eventInfo.title + '">$&</a>' ) : null;

	let html = '<p class="event-info">';
	html    += eventInfo.extendedProps.owner ? eventInfo.extendedProps.owner.join( ', ' ) : eventInfo.source.id;
	html    += '</p>';
	html    += '<h5 class="event-title">';
	html    += eventInfo.title;
	html    += '</h5>';
	html    += '<p class="event-info">';
	html    += eventInfo.start.toLocaleDateString( appointmentsData.locale, rotaractDateOptions( eventInfo.allDay ) );
	if (location) {
		html += '<br>' + location;
	}
	html += '</p>';
	if (eventInfo?.extendedProps?.description) {
		html += marked.parse(String(eventInfo.extendedProps.description));
	}

	return html;
}

/**
 * Generates the HTML representation of a calendar picker menu.
 *
 * @param {int} index The shortcode's index.
 *
 * @return HTML div element.
 */
function createCalendarPicker( index ) {
	let el = document.createElement( 'div' );
	el.id = `calendar-picker-${index}`;
	el.classList.add( 'calendar-picker' );

	eventSources.forEach(es => {
		el.innerHTML += `<button type="button" class="calendar-owner" onclick="toggleEventSource(${index}, this)" data-owner="${es.title}">
							 <span class="indicator" style="--bg-color: ${es.color}"></span>
							 <span>${es.title}</span>
						 </button>`;
	});

	return el;
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
