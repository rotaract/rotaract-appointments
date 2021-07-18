/**
 * Custom JS intended to be included in the plugin's setting page.
 *
 * @author zimbcode
 * @package Rotaract-Appointments
 */

/** Initialize LC-select field. */
new lc_select(
	'select.lc_select',
	{
		enable_search: true,
		labels: lcData.labels
	}
);
