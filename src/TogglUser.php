<?php

class TogglUser extends TogglRecord {
  static $element_name = 'me';

  public static function loadMultiple(TogglConnection $connection, array $options = array()) {
    return FALSE;
  }

  public function save(array $options = array()) {
    return FALSE;
  }

  public function delete(array $options = array()) {
    return FALSE;
  }
}
