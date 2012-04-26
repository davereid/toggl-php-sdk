<?php

class TogglUser extends TogglRecord {
  static $element_name = 'me';

  public static function loadAll() {
    return FALSE;
  }

  public function save() {
    return FALSE;
  }

  public function delete() {
    return FALSE;
  }
}
