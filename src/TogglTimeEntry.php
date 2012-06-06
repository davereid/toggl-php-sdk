<?php

class TogglTimeEntry extends TogglRecord {
  static $element_name = 'time_entry';
  static $element_plural_name = 'time_entries';

  public static function loadDateRange(TogglConnection $connection, $start_date = NULL, $end_date = NULL, array $options = array()) {
    if (isset($start_date) != isset($end_date)) {
      throw new TogglException("Invalid parameters for loading time entries.");
    }

    if (isset($start_date) && isset($end_date)) {
      if ($end_date < $start_date) {
        throw new TogglException("Start date cannot be after the end date.");
      }
      $options['query']['start_date'] = gmdate(TogglUtility::DATE_FORMAT, $start_date);
      $options['query']['end_date'] = gmdate(TogglUtility::DATE_FORMAT, $end_date);
    }

    return parent::loadMultiple($connection, $options);
  }
}
