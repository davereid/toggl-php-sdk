<?php

abstract class TogglRecord {
  static $element_name;
  static $element_plural_name;

  function __construct(TogglConnection $connection, array $values = array()) {
    $this->connection = $connection;
    $this->setData($values);
  }

  function setData(array $values) {
    foreach ($values as $key => $value) {
      $this->{$key} = $value;
    }
  }

  public static function load(TogglConnection $connection, $id) {
    if (!is_numeric($id)) {
      throw new TogglException('Invalid load ID ' . $id);
    }

    $class = get_called_class();
    $url = $class::$element_plural_name . '/' . $id;
    $response = $connection->request($connection->getURL($url));
    if (!empty($response->data['data'])) {
      return new $class($connection, $response->data['data']);
    }
    return FALSE;
  }

  public static function loadMultiple(TogglConnection $connection, array $query = array()) {
    $class = get_called_class();
    $response = $connection->request($connection->getUrl($class::$element_plural_name, $query));
    foreach ($response->data['data'] as $key => $record) {
      $response->data['data'][$key] = new $class($connection, $record);
    }
    return $response->data;
  }

  public function save() {
    $options['method'] = !empty($this->id) ? 'PUT' : 'POST';
    $options['data'][$this::$element_name] = $this;
    $url = $this::element_plural_name . (!empty($this->id) ? '/' . $this->id : '');
    $response = $this->connection->request($this->getURL($url), $options);
    $this->setData($response->data['data']);
  }

  public function delete() {
    if (!empty($this->id)) {
      $options['method'] = 'DELETE';
      $url = $this::$element_plural_name . '/' . $this->id;
      $response = $this->connection->request($this->getURL($url), $options);
    }
  }
}
