<?php

/*
 * soap_dispatcher.php - Delegate for Stud.IP SOAP Server.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * <ClassDescription>
 *
 * @package     studip
 * @subpackage  ws
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: soap_dispatcher.php 3888 2006-09-06 13:27:19Z mlunzena $
 */

class Studip_Ws_SoapDispatcher extends Studip_Ws_Dispatcher
                            /* implements SoapServerDelegate */ {


  /**
   * <FieldDescription>
   *
   * @access private
   * @var array
   */
  var $types = array();
  

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function throw_exception($message/*, ...*/) {
    $args = func_get_args();
    return new soap_fault('Client', '', vsprintf(array_shift($args), $args));
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function register_operations(&$server) {

    $namespace = $server->wsdl->schemaTargetNamespace;

    # 1st pass: register operations
    foreach ($this->api_methods as $method) {

      # return value
      $this->store_type($method->returns);
      $returns = array('returns' => $this->type_to_name_wns($method->returns));

      # arguments
      $expects = array();
      foreach ($method->expects as $name => $argument) {
        $expects['param'.$name] =
          $this->type_to_name_wns($method->expects[$name]);
        $this->store_type($method->expects[$name]);
      }
      
      $server->register($method->name,
                        $expects,
                        $returns,
                        $namespace,
                        $namespace . '#' . $method->name,
                        'rpc',
                        'encoded',
                        $method->description);
    }
    

    # recursively find all types
    foreach ($this->types as $key => $type)
      $this->store_type_recursive($this->types[$key]);

    # 2nd pass: register types
    foreach ($this->types as $type) {

      $type_class = Studip_Ws_Type::get_type($type);

      if ($type_class === STUDIP_WS_TYPE_ARRAY) {
        $name = $this->type_to_name($type);
        $element_type = Studip_Ws_Type::get_element_type($type);
        $element_name = $this->type_to_name_wns($element_type);
        $server->wsdl->addComplexType($name,
                                      'complexType',
                                      'array',
                                      '',
                                      'SOAP-ENC:Array',
                                      array(),
                                      array(array(
                                        'ref' => 'SOAP-ENC:arrayType',
                                        'wsdl:arrayType' => $element_name . '[]')),
                                      $element_name);
      }
      
      else if ($type_class === STUDIP_WS_TYPE_STRUCT) {

        $elements = array();
        $name = Studip_Ws_Type::get_element_type($type);
        foreach (Studip_Ws_Type::get_struct_elements($name) as $element) {
          $elements[$element->name] = array(
            'name' => $element->name,
            'type'      => $this->type_to_name_wns($element->type),
            'minOccurs' => 0);
        }
        
        $server->wsdl->addComplexType($name,
                                      'complexType',
                                      'struct',
                                      'all',
                                      '',
                                      $elements);
      }
    }
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return string <description>
   */
  function type_to_name_wns(&$type) {
    return sprintf('%s:%s',
                   Studip_Ws_Type::is_complex_type($type) ? 'tns' : 'xsd',
                   $this->type_to_name($type));
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return string <description>
   */
  function type_to_name(&$type) {

    $type_class = Studip_Ws_Type::get_type($type);

    if ($type_class === STUDIP_WS_TYPE_ARRAY) {
      for ($name = '', $element_type = $type;
           Studip_Ws_Type::get_type($element_type) === STUDIP_WS_TYPE_ARRAY;
           $element_type = Studip_Ws_Type::get_element_type($element_type)) {
        $name .= 'Array';
      }
      return $this->type_to_name($element_type) . $name;
    }
    
    else if ($type_class === STUDIP_WS_TYPE_STRUCT) {
      return Studip_Ws_Type::get_element_type($type);
    }
    
    else {

      $mapping = array(
        STUDIP_WS_TYPE_INT    => 'int',
        STUDIP_WS_TYPE_STRING => 'string',
        STUDIP_WS_TYPE_BASE64 => 'base64',
        STUDIP_WS_TYPE_BOOL   => 'boolean',
        STUDIP_WS_TYPE_FLOAT  => 'double',
        STUDIP_WS_TYPE_NULL   => 'boolean');
      
      if (isset($mapping[$type_class]))
        return $mapping[$type_class];
    }
    
    trigger_error(sprintf('Type not known: %s', var_export($type, TRUE)),
                  E_USER_ERROR);
    return 'any';
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return boolean <description>
   */
  function store_type(&$type) {
    if (isset($this->types[$name = $this->type_to_name_wns($type)]))
      return FALSE;
    
    if (Studip_Ws_Type::is_primitive_type($type))
      return FALSE;

    $this->types[$name] =& $type;
    return TRUE;
  }


  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return void
   */
  function store_type_recursive(&$type) {


    $type_class = Studip_Ws_Type::get_type($type);

    if ($type_class === STUDIP_WS_TYPE_ARRAY) {
      $element_type =& Studip_Ws_Type::get_element_type($type);
      $this->store_type($element_type);
      $this->store_type_recursive($element_type);
    }

    else if ($type_class === STUDIP_WS_TYPE_STRUCT) {
      $struct =& Studip_Ws_Type::get_element_type($type);
      foreach (Studip_Ws_Type::get_struct_elements($struct) as $element) {
        if ($this->store_type($element->type))
          $this->store_type_recursive($element->type);
      }
    }
  }
}
