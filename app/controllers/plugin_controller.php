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

    /**
     * Creates the body element id for this controller a given action.
     *
     * @param string $unconsumed_path Unconsumed path to extract action from
     * @return string
     */
    protected function getBodyElementIdForControllerAndAction($unconsumed_path)
    {
        $body_id = implode('-', [
            'plugin',
            strtosnakecase(get_class($this->plugin)),
            parent::getBodyElementIdForControllerAndAction($unconsumed_path),
        ]);

        return $body_id;
    }
}
