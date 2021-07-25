/**
 * Custom JS intended to be included in the in public view.
 *
 * @author Rotaract Germany
 * @package Rotaract_Appointments
 * @subpackage Rotaract_Appointments/public/js
 */

const rotaractCalendarOptions = {
	locale: appointmentsData.locale,
	initialView: "listYear",
	eventDidMount: function(info) {
		var elem       = document.createElement( "div" );
		elem.innerHTML = info.event.extendedProps.description;
		elem.classList.add( "event-description" );
		info.el.append( elem );
	},
	headerToolbar: {
		start: "prev,next today",
		center: "title",
		end: "listYear,dayGridMonth"
	},
	height: "auto",
	views: {
		listYear: {
			eventClick( info ) {
				if ( ! info.jsEvent.target.href) {
					info.el.classList.toggle( "show" );
				}
			}
		},
		dayGridMonth: {
			eventClick( info ) {
				if ( ! info.jsEvent.target.href) {
					info.el.classList.toggle( "show" );
				}
				let descEl = info.el.querySelector( ".event-description" );
				if (descEl) {
					descEl.style.left = "50%%";
					let newLeft       = descEl.getBoundingClientRect().left - screen.width / 10;
					if (newLeft < 0 || newLeft + descEl.offsetWidth > screen.width * 0.8) {
						descEl.style.left = "calc(50%% - " + newLeft + "px)";
					}
				}
			}
		}
	}
}
