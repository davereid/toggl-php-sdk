<?php

/**
 * @file
 * Defines TogglConnection.
 */

class TogglReportConnection extends TogglConnection {
  /**
   * The Toggl API version, used in HTTP requests.
   */
  public $report_api_version = 'v2';

  /**
   * Construct the request URI.
   *
   * @param string $resource
   *   The resource name/path.
   * @param array $query
   *   An array of query string parameters to add to the URL.
   *
   * @return
   *   A fully-quantified Toggl API URL.
   */
  public function getURL($resource, array $query = array()) {
    $query['user_agent'] = $this->userAgent;
    $url = 'https://toggl.com/reports/api/' . $this->report_api_version . '/' . $resource;
    if (!empty($query)) {
      $url .= '?' . http_build_query($query, NULL, '&');
    }
    return $url;
  }

}
