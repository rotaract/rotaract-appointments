<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/rotaract/appointments
 * @since      1.0.0
 *
 * @package    Rotaract_Appointments
 * @subpackage Rotaract_Appointments/public/partials
 */

?>
<script>
document.addEventListener("DOMContentLoaded", function() {
	const calendarEl = document.getElementById("rotaract-appointments");
	const calendar = new FullCalendar.Calendar(calendarEl, {
		locale: "de",
		initialView: "listYear",
		eventDidMount: function(info) {
			var elem = document.createElement("div");
			elem.innerHTML = info.event.extendedProps.description;
			elem.classList.add("event-description");
			info.el.append(elem);
		},
		eventClick: function(info) {
			if (!info.jsEvent.target.href) {
				info.el.classList.toggle("show");
				if (info.view.type !== "listYear") {
					let descEl = info.el.querySelector(".event-description");
					if (descEl) {
						descEl.style.left = "50%%";
						let newLeft = descEl.getBoundingClientRect().left - screen.width / 10;
						if (newLeft < 0 || newLeft + descEl.offsetWidth > screen.width * 0.8) {
							descEl.style.left = "calc(50%% - " + newLeft + "px)";
						}
					}
				}
			}
		},
		headerToolbar: {
			start: "prev,next today",
			center: "title",
			end: "listYear,dayGridMonth"
		},
		height: "auto",
		events: <?php echo wp_json_encode( $events ); ?>
	});
	calendar.render();
});
</script>
