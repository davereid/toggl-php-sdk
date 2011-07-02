<?php

class TogglException extends Exception {}

/**
 * Toggl API Class
 *
 * @see https://www.toggl.com/public/api
 */
class Toggl {
  /**
   * The Toggl API version, used in HTTP requests.
   */
  const API_VERSION = 'v6';

  private $base_url = 'https://www.toggl.com';
  private $user_agent = 'Toggle PHP SDK';

  private $token;

  /**
   * Construct the API object.
   */
  public function __construct($token) {
    $this->token = $token;
  }

  /**
   * Construct the request URI.
   */
  protected function getURL($name) {
    return $this->base_url . '/api/' . self::VERSION . $name . '.json';
  }

  /**
   * Build the request headers.
   *
   * @return array
   */
  protected function getHeaders() {
    return array(
      'Authorization' => 'Basic ' . base64_encode($this->token . ':api_token'),
      'User-Agent' => $this->user_agent,
      'Content-Type' => 'application/json',
    );
  }

  protected function getRequest($url, array $options = array()) {
    if (!empty($options['data'])) {
      $url .= '?' . http_build_query($options['data'], '&');
      $options['data'] = NULL;
    }
    return $this->sendRequest($url, $options);
  }

  protected function sendRequest($url, array $options = array()) {
    $options += array(
      'headers' => array(),
      'method' => 'GET',
      'data' => NULL,
    );

    foreach (array_merge($this->getHeaders(), $headers) as $header => $value) {
      $headers[$header] = $header . ': ' . $value;
    }
    
    // Set the CURL variables.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUSET, $options['method']);

    // Include post data.
    if (isset($options['data'])) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['data']));
    }

    // Perform the API request.
    $result = curl_exec($ch);

    $response = new stdClass();
    $response->data = json_decode($result);
    $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $resposne->success = $response->code == 200;
    return $response;
  }

  /**
   * Get time entries for the current user.
   *
   * @param int $start_date
   * @param int $end_date
   */
  public function timeEntriesLoadRecent($start_date = NULL, $end_date = NULL) {
    if (isset($start_date) != isset($end_date)) {
      throw new ToggleAPIException("Invalid parameters for getTimeEntries.");
    }

    $data = array();
    if (isset($start_date) && isset($end_date)) {
      $data['start_date'] = gmdate(DATE_ISO8601, $start_date);
      $data['end_date'] = gmdate(DATE_ISO8601, $end_date);
    }

    // @todo Convert this into an array of timeEntry classes.
    return $this->getRequest($this->getURL('tasks'), $data);
  }

  /**
   * Save a time entry for the current user.
   *
   * @param $timeEntry
   */
  public function timeEntrySave($timeEntry) {
    $options['method'] = !empty($timeEntry->id) ? 'PUT' : 'POST';
    $url = 'time_entries' . !empty($timeEntry->id) ? '/' . $timeEntry->id : '';
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
    $url = 'clients' . !empty($client->id) ? '/' . $client->id : '';
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
    $url = 'projects' . !empty($project->id) ? '/' . $project->id : '';
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
    return $this->request($this->getURL('me'));
  }
}

class TogglTimeEntry {
  private $parent;

  function __construct(TogglAPI $parent) {
    $this->parent = $parent;
  }

  public function save() {
    $this->parent->timeEntrySave($this);
  }

  public function delete() {
    $this->parent->timeEntryDelete($this);
  }
}
