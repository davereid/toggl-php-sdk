<?php

abstract class TogglRecord {
  static $element_name;
  static $element_plural_name;

  protected $data = array();
  protected $connection;
  
  public $debug = false;

  public function __construct(TogglConnection $connection, array $data = array()) {
    $this->connection = $connection;
    $this->connection->debug = $this->debug;
    $this->data = $data;
  }

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __isset($name) {
    return isset($this->data[$name]);
  }

  public function __unset($name) {
    unset($this->data[$name]);
  }

  protected function setConnection(TogglConnection $connection) {
    $this->connection = $connection;
  }

  public function getConnection() {
    return $this->connection;
  }

  protected function setData(array $data) {
    $this->data = $data;
  }

  public function getData() {
    return $this->data;
  }

  public static function load(TogglConnection $connection, $id, array $options = array()) {
    if (!is_numeric($id)) {
      throw new TogglException('Invalid load ID ' . $id);
    }

    $class = get_called_class();
    $resource = $class::$element_plural_name . '/' . $id;
    $response = $connection->request($resource);
    if (!empty($response->data['data'])) {
      return new $class($connection, $response->data['data']);
    }
    return $response->success;
  }

  public static function loadMultiple(TogglConnection $connection, array $options = array()) {
    $class = get_called_class();
    $response = $connection->request($class::$element_plural_name, $options);
    $ret = array('data' => array(), 'count' => 0);
    foreach ($response->data as $key => $record) {
      $ret['data'][] = new $class($connection, $record);
    }
    $ret['count'] = count($ret['data']);
    return $ret;
  }

  public function save(array $options = array()) {
    $options['method'] = !empty($this->id) ? 'PUT' : 'POST';
    $options['data'][$this::$element_name] = $this->data;
    $resource = $this::$element_plural_name . (!empty($this->id) ? '/' . $this->id : '');
    $response = $this->connection->request($resource, $options);
    $this->data = $response->data['data'];
    return $response->success;
  }

  public function delete(array $options = array()) {
    if (!empty($this->id)) {
      $options['method'] = 'DELETE';
      $resource = $this::$element_plural_name . '/' . $this->id;
      $response = $this->connection->request($resource, $options);
      return $response->success;
    }
    $this->data = array();
    return TRUE;
  }
  
  public function getCurlDebugInfo() {
    return $this->connection->curlVerbose;
  }
  
}
