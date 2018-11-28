<?php

/*
 * Copyright (C) 2008 - André Klaßen <aklassen@uos.de>
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/classes/Avatar.class.php';

class Step120Userpic extends Migration {

  function description() {
    return 'modify existing user pictures according to Step00120';
  }

  function up() {
    foreach (glob($GLOBALS['DYNAMIC_CONTENT_PATH'].'/user/*.jpg') as $value) {
      if (preg_match('/\/([0-9a-f]+).jpg$/', $value, $matches)) {
        try {
          Avatar::getAvatar($matches[1])->createFrom($value);
        } catch (Exception $e) {
          $this->announce('Exception while converting avatar "%s"', $value);
          $this->write($e->getMessage()."\n");
        }
        @unlink($value);
      }
    }
  }

  function down() {
  }
}
