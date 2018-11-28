<?php

/*
 * type.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


define('STUDIP_WS_TYPE_INT',    'int');
define('STUDIP_WS_TYPE_STRING', 'string');
define('STUDIP_WS_TYPE_BASE64', 'base64');
define('STUDIP_WS_TYPE_BOOL',   'bool');
define('STUDIP_WS_TYPE_FLOAT',  'float');
define('STUDIP_WS_TYPE_ARRAY',  'array');
define('STUDIP_WS_TYPE_STRUCT', 'struct');
define('STUDIP_WS_TYPE_NULL',   'null');


/**
 * <ClassDescription>
 *
 * @package     studip
 * @subpackage  ws
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: type.php 3888 2006-09-06 13:27:19Z mlunzena $
 */

class Studip_Ws_Type {


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return mixed <description>
   */
  function translate($type) {

    # complex types
    if (is_string($type) && class_exists($type))
      return array(STUDIP_WS_TYPE_STRUCT => $type);

    # array types
    if (is_array($type)) {
      if (!sizeof($type)) {
        trigger_error('Array of missing type.', E_USER_ERROR);
        return array(STUDIP_WS_TYPE_NULL => NULL);
      }

      $element_type = current($type);
      if (is_null($element_type)) {
        trigger_error(sprintf('Array of unknown type: %s',
                              var_export($element_type, TRUE)),
                      E_USER_ERROR);
        return array(STUDIP_WS_TYPE_NULL => NULL);
      }

      return array(STUDIP_WS_TYPE_ARRAY =>
                   Studip_Ws_Type::translate($element_type));
    }

    # basic types
    if (is_string($type))
      switch ($type) {

        case 'int':
        case 'integer':
                        return array(STUDIP_WS_TYPE_INT => NULL);

        case 'string':
        case 'text':
                        return array(STUDIP_WS_TYPE_STRING => NULL);

        case 'base64':
                        return array(STUDIP_WS_TYPE_BASE64 => NULL);

        case 'bool':
        case 'boolean':
                        return array(STUDIP_WS_TYPE_BOOL => NULL);

        case 'float':
        case 'double':
                        return array(STUDIP_WS_TYPE_FLOAT => NULL);

        case 'null':
                        return array(STUDIP_WS_TYPE_NULL => NULL);
      }

    # type by example
    $type_checkers = array(
      'is_bool'   => STUDIP_WS_TYPE_BOOL,
      'is_float'  => STUDIP_WS_TYPE_FLOAT,
      'is_int'    => STUDIP_WS_TYPE_INT,
      'is_string' => STUDIP_WS_TYPE_STRING,
      'is_null'   => STUDIP_WS_TYPE_NULL,
      );
    foreach ($type_checkers as $function => $replacement)
      if ($function($type))
        return array($replacement => NULL);

    trigger_error('"' . gettype($type) . '" is not a valid type.');
    return array(STUDIP_WS_TYPE_NULL => NULL);
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return string <description>
   *
   * @todo name ist falsch
   */
  function get_type($type) {

    if (is_array($type))
      return key($type);

    trigger_error(sprintf('$type has to be an array, but is: "%s"',
                          var_export($type, TRUE)),
                  E_USER_ERROR);
    return STUDIP_WS_TYPE_NULL;
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return mixed <description>
   *
   * @todo name ist falsch
   */
  function get_element_type($type) {
    if (is_array($type))
      return current($type);
    trigger_error(sprintf('\$type has to be an array, but is: "%s"',
                          var_export($type, TRUE)),
                  E_USER_ERROR);
    return STUDIP_WS_TYPE_NULL;
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function is_complex_type($type0) {
    $type = Studip_Ws_Type::get_type($type0);
    return $type === STUDIP_WS_TYPE_ARRAY || $type === STUDIP_WS_TYPE_STRUCT;
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function is_primitive_type($type) {
    return !Studip_Ws_Type::is_complex_type($type);
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function get_struct_elements($struct) {

    # check argument; has to be a class
    if (!class_exists($struct)) {
      trigger_error(sprintf('Class definition missing: "%s"', $struct),
                    E_USER_ERROR);
      return NULL;
    }

    $result = array();

    # either a struct or a duck typing struct
    # (= responds to 'get_struct_elements')
    if (is_callable(array($struct, 'get_struct_elements'))) {
      $result = call_user_func(array($struct, 'get_struct_elements'), $struct);
    }

    # just a class
    else {
      foreach (get_class_vars($struct) as $var_name => $var_value) {
        $result[] = new Studip_Ws_StructElement($var_name,
                                                 STUDIP_WS_TYPE_STRING);
      }
    }

    return $result;
  }
}
