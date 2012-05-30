<?php

class TogglWorkspace extends TogglRecord {
  static $element_name = 'workspace';
  static $element_plural_name = 'workspaces';

  public static function load(TogglConnection $connection, $id, array $options = array()) {
    return FALSE;
  }

  public function save(array $options = array()) {
    return FALSE;
  }

  public function delete(array $options = array()) {
    return FALSE;
  }
}
