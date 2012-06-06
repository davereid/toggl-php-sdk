<?php

class TogglUtility {

  const DATE_FORMAT = 'Y-m-d\TH:i:sO';

  /**
   * Filter an array of objects or nested arrays by a variable depth value.
   *
   * @see toggl_get_nested_value()
   */
  public static function filterItemsByNestedValue(array $items, array $parents, $value) {
    $return = array();
    foreach ($items as $key => $item) {
      $key_exists = FALSE;
      $found_value = TogglUtility::getNestedValue($item, $parents, $key_exists);
      if ($key_exists) {
        if (is_array($value) && in_array($found_value, $value)) {
          $return[$key] = $item;
        }
        elseif ($found_value == $value) {
          $return[$key] = $item;
        }
      }
    }
    return $return;
  }

  /**
   * Retrieves a value from an object or nested array with variable depth.
   *
   * This is a copy of drupal_array_get_nested_value() but with added support
   * for objects.
   *
   * @param mixed $item
   *   The array or object from which to get the value.
   * @param array $parents
   *   An array of parent keys of the value, starting with the outermost key.
   * @param bool $key_exists
   *   (optional) If given, an already defined variable that is altered by
   *   reference if all the keys in $parents were found.
   *
   * @return mixed
   *   The requested nested value. Possibly NULL if the value is NULL or not all
   *   nested parent keys exist. $key_exists is altered by reference and is a
   *   Boolean that indicates whether all nested parent keys exist (TRUE) or not
   *   (FALSE). This allows to distinguish between the two possibilities when
   *   NULL is returned.
   */
  public static function getNestedValue($item, array $parents, &$key_exists = NULL) {
    $ref = $item;
    foreach ($parents as $parent) {
      if (is_array($ref) && array_key_exists($parent, $ref)) {
        $ref = &$ref[$parent];
      }
      elseif (is_object($ref) && property_exists($ref, $parent)) {
        $ref = $ref->$parent;
      }
      elseif (is_object($ref) && method_exists($ref, '__get') && $ref->__get($parent) !== NULL) {
        // Support objects that override the __get magic method.
        // This also doesn't support if $ref->$parent exists but is set to NULL.
        $ref = $ref->$parent;
      }
      else {
        $key_exists = FALSE;
        return NULL;
      }
    }
    $key_exists = TRUE;
    return $ref;
  }

  /**
   * Convert a list of Toggl API objects into a keyed array by ID and name.
   *
   * @param array $items
   *   An array of objects from a Toggl API request.
   * @param string $key
   *   The property from each object to use as the array key. Default is 'id'.
   * @param string $value
   *   The proprety from each object ot use as the array value. Default is 'name'.
   *
   * @return array
   *   An array with the specified key/value pairs.
   */
  public static function mapItemsToKeyedArray(array $items, $key = 'id', $value = 'name') {
    $return = array();
    foreach ($items as $item) {
      $return[$item->$key] = $item->$value;
    }
    return $return;
  }
}
