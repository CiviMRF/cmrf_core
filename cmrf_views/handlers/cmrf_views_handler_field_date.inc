<?php

/**
 * @file
 * Definition of views_handler_field_date.
 */

/**
 * A handler to provide proper displays for dates.
 *
 * @ingroup views_field_handlers
 */
class cmrf_views_handler_field_date extends views_handler_field_date {
  
	/**
	 * Convert a date time field from civicrm to unix timestamp
	 */
	function get_value($values, $field = NULL) {
		$value = parent::get_value($values, $field);
		if ($value) {
			$dateTime = new DateTime($value);
			return $dateTime->format('U');
		}
  }
}