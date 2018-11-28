<?php
/*
 * Copyright (c) 2014  Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class PluginController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $this->dispatcher->current_plugin;
    }
}