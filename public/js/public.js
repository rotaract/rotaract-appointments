/**
 * Custom JS intended to be included in the in public view.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/public/js
 */

const rotaractCalendarOptions  = {
	locale: appointmentsData.locale,
	initialView: 'listYear',
	customButtons: {
		ownerButton: {
			text: appointmentsData.calendarBtn
		}
	},
	headerToolbar: {
		start: 'prev,next',
		center: 'title',
		end: 'listYear,dayGridMonth'
	},
	footerToolbar: {
		start: 'today',
		center: '',
		end: 'ownerButton'
	},
	height: 'auto',
	views: {
		listYear: {
			eventDidMount( info ) {
				var elem       = document.createElement( 'div' );
				elem.innerHTML = info.event.extendedProps.description;
				elem.classList.add( 'event-description' );
				info.el.append( elem );
			},
			eventClick( info ) {
				if ( ! info.jsEvent.target.href) {
					info.el.classList.toggle( 'show' );
				}
			}
		},
		dayGridMonth: {
			eventDidMount( info ) {
				const calEl = info.el.closest( '#rotaract-appointments' );
				tippy(
					info.el,
					{
						allowHTML: true,
						appendTo: calEl,
						content: info.event.extendedProps.description,
						interactive: true,
						theme: 'rotaract',
						trigger: 'click',
						onShow() {
							calEl.style.setProperty( '--fc-event-bg-color', info.event.source.internalEventSource._raw.color );
						}
					}
				);
			}
		}
	}
}

var calendar;

function calendarInit( eventSources ) {
	const calendarEl = document.getElementById( "rotaract-appointments" );
	calendar         = new FullCalendar.Calendar( calendarEl, rotaractCalendarOptions );
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
	)
}
