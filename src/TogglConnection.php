<?php

/**
 * @file
 * Defines TogglConnection.
 */

class TogglConnection {
  /**
   * The Toggl API version, used in HTTP requests.
   */
  const API_VERSION = 'v6';

  const DATE_FORMAT = 'Y-m-d\TH:i:sO';

  private $userAgent = 'Toggl PHP SDK';

  private $token;

  private $options = array();

  /**
   * Construct the API object.
   */
  public function __construct($token, array $options = array()) {
    $this->token = $token;
    $this->options = $options;
  }

  public function setToken($token) {
    $this->token = $token;
  }

  public function getToken() {
    return $this->token;
  }

  public function getOptions() {
    return $this->options;
  }

  public function setOptions(array $options) {
    $this->options = $options;
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
  public function getURL($resource, array $query = array()) {
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

  public function request($resource, array $options = array()) {
    $options += $this->getOptions() + array(
      'headers' => array(),
      'method' => 'GET',
      'query' => array(),
      'data' => NULL,
    );

    $url = $this->getURL($resource, $options['query']);

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
    $response->data = json_decode($result, TRUE);
    $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $response->success = $response->code == 200;

    curl_close($ch);
    return $response;
  }

  public function getUser() {
    $response = $this->request('me');
    if (!empty($response->data['data'])) {
      return new TogglUser($this, $response->data['data']);
    }
    return FALSE;
  }
}
