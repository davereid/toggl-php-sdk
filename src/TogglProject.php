<?php

class TogglProject extends TogglRecord {
  static $element_name = 'project';
  static $element_plural_name = 'projects';

  public function delete() {
    return FALSE;
  }
}
