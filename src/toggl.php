<?php

require_once dirname(__FILE__) . '/TogglException.php';

/**
 * Toggl API Class
 *
 * @see https://www.toggl.com/public/api
 * @see https://github.com/davereid/toggl-php-sdk
 */
class Toggl {
  /**
   * The Toggl API version, used in HTTP requests.
   */
  const API_VERSION = 'v6';

  const DATE_FORMAT = 'Y-m-d\TH:i:sO';

  private $userAgent = 'Toggl PHP SDK';

  private $token;

  /**
   * Construct the API object.
   */
  public function __construct($token) {
    $this->token = $token;
  }

  public function setToken($token) {
    $this->token = $token;
  }

  public function getToken() {
    return $this->token;
  }

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
  protected function getURL($resource, array $query = array()) {
    $url = 'https://www.toggl.com/api/' . self::API_VERSION . '/' . $resource . '.json';
    if (!empty($query)) {
      $url .= '?' . http_build_query($query, NULL, '&');
    }
    return $url;
  }

  /**
   * Build the request headers.
   *
   * @return array
   */
  protected function getHeaders() {
    return array(
      //'Authorization' => 'Basic ' . base64_encode($this->token . ':api_token'),
      //'User-Agent' => 'Toggl PHP SDK (+https://github.com/davereid/toggl-php-sdk)',
    );
  }

  protected function request($url, array $options = array()) {
    $options += array(
      'headers' => array(),
      'method' => 'GET',
      'data' => NULL,
    );

    // Set the CURL variables.
    $ch = curl_init();

    // Include post data.
    if (isset($options['data'])) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['data']));
      $options['headers']['Content-Type'] = 'application/json';
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Needed since Toggl's SSL fails without this.
    curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);
    curl_setopt($ch, CURLOPT_USERPWD, $this->getToken() . ':api_token');

    // Build and format the headers.
    foreach (array_merge($this->getHeaders(), $options['headers']) as $header => $value) {
      $options['headers'][$header] = $header . ': ' . $value;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);

    // Perform the API request.
    $result = curl_exec($ch);
    if ($result == FALSE) {
      throw new TogglException(curl_error($ch));
    }

    // Build the response.
    $response = new stdClass();
    $response->data = json_decode($result);
    $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response->success = $response->code == 200;

    curl_close($ch);
    return $response;
  }

  /**
   * Get time entries for the current user.
   *
   * @param int $start_date
   * @param int $end_date
   */
  public function timeEntriesLoadRecent($start_date = NULL, $end_date = NULL, array $options = array()) {
    if (isset($start_date) != isset($end_date)) {
      throw new TogglException("Invalid parameters for getTimeEntries.");
    }

    $query = array();
    if (isset($start_date) && isset($end_date)) {
      if ($end_date < $start_date) {
        throw new TogglException("Start date cannot be after the end date.");
      }
      $query['start_date'] = gmdate(self::DATE_FORMAT, $start_date);
      $query['end_date'] = gmdate(self::DATE_FORMAT, $end_date);
    }

    // @todo Convert this into an array of timeEntry classes.
    return $this->request($this->getURL('time_entries', $query), $options);
  }

  /**
   * Save a time entry for the current user.
   *
   * @param $timeEntry
   *   A time entry object.
   */
  public function timeEntrySave($timeEntry) {
    // Add a default created with
    if (!isset($timeEntry->created_with)) {
      $timeEntry->created_with = $this->userAgent;
    }

    $options['method'] = !empty($timeEntry->id) ? 'PUT' : 'POST';
    $url = 'time_entries' . (!empty($timeEntry->id) ? '/' . $timeEntry->id : '');
    $options['data']['time_entry'] = $timeEntry;

    $response = $this->request($this->getURL($url), $options);
    $timeEntry = $response->data;
    return $response;
  }

  /**
   * Delete a time entry.
   *
   * @param $timeEntry
   *   A time entry object.
   */
  public function timeEntryDelete($timeEntry) {
    $options['method'] = 'DELETE';
    $url = 'time_entries/' . $timeEntry->id;

    $response = $this->request($this->getURL($url), $options);
    unset($timeEntry);
    return $response;
  }

  public function workspaceLoadAll() {
    return $this->request($this->getURL('workspaces'));
  }

  public function clientLoadAll() {
    return $this->request($this->getURL('clients'));
  }

  public function clientSave($client) {
    $options['method'] = !empty($client->id) ? 'PUT' : 'POST';
    $url = 'clients' . (!empty($client->id) ? '/' . $client->id : '');
    $options['data']['client'] = $client;

    $response = $this->request($this->getURL($url), $options);
    $client = $response->data;
    return $response;
  }

  public function clientDelete($client) {
    $options['method'] = 'DELETE';
    $url = 'clients/' . $client->id;

    $response = $this->request($this->getURL($url), $options);
    $client = $response->data;
    return $response;
  }

  public function projectLoadAll() {
    return $this->request($this->getURL('projects'));
  }

  public function projectSave($project) {
    $options['method'] = !empty($project->id) ? 'PUT' : 'POST';
    $url = 'projects' . (!empty($project->id) ? '/' . $project->id : '');
    $options['data']['project'] = $project;

    $response = $this->request($this->getURL($url), $options);
    $project = $response->data;
    return $response;
  }

  public function taskLoadAll() {
    return $this->request($this->getURL('tasks'));
  }

  public function tagLoadAll() {
    return $this->request($this->getURL('tags'));
  }

  public function userLoad() {
    $response = $this->request($this->getURL('me'));
    if (!empty($response->data->data)) {
      return $response->data->data;
    }
    return FALSE;
  }

  public function loadAll($resource) {
    $response = $this->request($this->getURL($resource));
    if (isset($response->data->data) && is_array($response->data->data)) {
      $resources = toggl_map_ids($response->data->data);
      return $resources;
    }
    return FALSE;
  }

  public function save($resource, $object) {
    $options['method'] = !empty($object->id) ? 'PUT' : 'POST';
    $url = $resource . '/' . (!empty($object->id) ? '/' . $object->id : '');
    $options['data']['client'] = $object;

    $response = $this->request($this->getURL($url), $options);
    $client = $response->data;
    return $response;
  }

  public function delete($resource, $object) {
    $options['method'] = 'DELETE';
    $url = $resource . '/' . $object->id;

    $response = $this->request($this->getURL($url), $options);
    $client = $response->data;
    return $response;
  }
}

function toggl_map_ids(array $items) {
  $return = array();
  foreach ($items as $key => $item) {
    if (isset($item->id)) {
      $key = $item->id;
    }
    $return[$key] = $item;
  }
  return $return;
}

function toggl_filter_array_set_variables(array $variables = NULL) {
  static $stored_variables = array();

  if (isset($variables)) {
    $stored_variables = $variables;
  }

  return $stored_variables;
}

function toggl_filter_array($item) {
  $variables = toggl_filter_array_set_variables();
  foreach ($variables as $key => $value) {
    if (!isset($item->{$key}) || $item->{$key} != $value) {
      return FALSE;
    }
  }
  return $item;
}
