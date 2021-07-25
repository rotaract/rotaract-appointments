/**
 * Custom JS intended to be included in the in public view.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/public/js
 */

const rotaractCalendarOptions  = {
	locale: appointmentsData.locale,
	initialView: "listYear",
	headerToolbar: {
		start: "prev,next today",
		center: "title",
		end: "listYear,dayGridMonth"
	},
	height: "auto",
	views: {
		listYear: {
			eventDidMount( info ) {
				var elem       = document.createElement( "div" );
				elem.innerHTML = info.event.extendedProps.description;
				elem.classList.add( "event-description" );
				info.el.append( elem );
			},
			eventClick( info ) {
				if ( ! info.jsEvent.target.href) {
					info.el.classList.toggle( "show" );
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
