<?php

namespace Peridot\ObjectPath;

/**
 * ObjectPath is a utility for parsing object and array strings into
 * ObjectPathValues.
 *
 * @package Peridot\ObjectPath
 */
class ObjectPath {
  /**
   * The subject to match path expressions against.
   *
   * @var array|object
   */
  protected $subject;

  /**
   * @param array|object $subject
   */
  public function __construct(&$subject) {
    $this->subject = &$subject;
  }

  /**
   * Returns the target value if the property described by $path
   * can be located in the subject.
   *
   * A path expression uses object and array syntax.
   *
   * @code
   *
   * $person = new stdClass();
   * $person->name = new stdClass();
   * $person->name->first = 'brian';
   * $person->name->last = 'scaturro';
   * $person->hobbies = ['programming', 'reading', 'board games'];
   *
   * $path = new ObjectPath($person);
   * $first = $path->{'name->first'};
   * $reading = $path->{'hobbies[0]'};
   *
   * @endcode
   *
   * @param string $path
   * @return mixed Returns null when target is not found.
   */
  public function &__get($path) {
    $path = $this->normalizeKey($path);

    $property = array_pop($path);

    $subject = &$this->deepReference($this->subject, $path);

    if ( is_object($subject) ) {
      return $subject->$property;
    }
    else if ( is_array($subject) || $subject instanceof \ArrayAccess ) {
      return $subject[$property];
    }

    $null = null;
    return $null;
  }

  /**
   * For backward compatibility.
   *
   * @see $this->__get($path)
   * @return ObjectPathValue
   */
  public function get($path) {
    $path = $this->normalizeKey($path);

    $property = array_pop($path);

    $value = &$this->deepReference($this->subject, $path);

    if ( is_object($value) ) {
      $value = &$value->$property;

      // nested single property objects should be digged out.
      while ( is_object($value) ) {
        $vars = get_object_vars($value);

        if ( count($vars) != 1 ) {
          break;
        }

        $vars = array_keys($vars);

        $value = &$value->{$vars[0]};
      }

      unset($vars);
    }
    else if ( is_array($value) || $value instanceof \ArrayAccess ) {
      $value = &$value[$property];
    }
    else {
      return null;
    }

    if ( $value ) {
        return new ObjectPathValue($property, $value);
    }
    else {
        return null;
    }
  }

  /**
   * Assigns the target with specified value if the property described by $path
   * can be located in the subject.
   *
   * A path expression uses object and array syntax.
   *
   * @code
   *
   * $person = new stdClass();
   * $person->name = new stdClass();
   * $person->name->first = 'brian';
   * $person->name->last = 'scaturro';
   * $person->hobbies = ['programming', 'reading', 'board games'];
   *
   * $path = new ObjectPath($person);
   * $first = $path->{'name->first'} = 'John';
   * $reading = $path->{'hobbies[0]'} = 'writing';
   *
   * $person->name == 'John'; // TRUE
   * $person->hobbies[0] == 'writing'; // TRUE
   *
   * @endcode
   *
   * @param string $path
   * @return mixed Returns null when target is not found.
   */
  public function __set($path, $value) {
    $path = $this->normalizeKey($path);

    $subject = &$this->deepReference($this->subject, $path);

    $subject = $value;
  }

  /**
   * Unsets target if the property described by $path can be located in the
   * subject.
   *
   * A path expression uses object and array syntax.
   *
   * @code
   *
   * $person = new stdClass();
   * $person->name = new stdClass();
   * $person->name->first = 'brian';
   * $person->name->last = 'scaturro';
   * $person->hobbies = ['programming', 'reading', 'board games'];
   *
   * $path = new ObjectPath($person);
   * unset($path->{'name->first'});
   *
   * empty($person->name->first); // TRUE
   *
   * @endcode
   *
   * @param string $path
   * @return mixed Returns null when target is not found.
   */
  public function __unset($path) {
    $path = $this->normalizeKey($path);

    $property = array_pop($path);

    $subject = &$this->deepReference($this->subject, $path);

    if ( is_object($subject ) ) {
      unset($subject->$property);
    }
    else if ( is_array($subject) || $subject instanceof \ArrayAccess ) {
      unset($subject[$property]);
    }
  }

  /**
   * Returns the underlying subject.
   */
  public function &getValue() {
    return $this->subject;
  }

  /**
   * Return a key that can be used on the current subject.
   *
   * @param $key
   * @param $matches
   * @return mixed
   */
  protected function normalizeKey($key) {
    // Replaces array notions into delimiters
    $key = str_replace('->', '::', $key);

    // Replaces object notions into delimiters
    $key = preg_replace('/\[\'?(\w+?|\d+?)\'?\]/', '::$1', $key);

    $key = explode('::', trim($key, '::'));

    return $key;
  }

  /**
   * Return a reference to $path from subject.
   */
  protected function &deepReference(&$subject, array $path) {
    while ( $path ) {
      $subject = &$this->propertyOf($subject, array_shift($path));
    }

    return $subject;
  }

  /**
   * Returns target property from either array or object notion.
   */
  protected function &propertyOf(&$subject, $property) {
    if ( is_object($subject) ) {
      return $subject->$property;
    }
    else if ( is_array($subject) || $subject instanceof \ArrayAccess ) {
      return $subject[$property];
    }
    else {
      $value = false;
      return $value;
    }
  }
}
