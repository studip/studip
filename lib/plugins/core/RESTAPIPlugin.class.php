<?php
/*
 * REST-API Plugins add maps to the REST-API router.
 *
 * Copyright (c) 2014 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface RESTAPIPlugin
{
    /**
     * Returns one or more instances of RESTAPI\RouteMap to register
     * to the Router.
     *
     * @return RouteMap|Array   either a single instance of class
     *                          RouteMap or an array of them
     */
    public function getRouteMaps();
}
