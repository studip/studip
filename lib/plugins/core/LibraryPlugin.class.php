<?php


/*
 * LibraryPlugin.class.php - A plugin class for library plugins.
 *
 * Copyright (c) 2020  Moritz Strohm
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


interface LibraryPlugin
{
    /**
     * Generates the URL that leads to the plugin action to create a request.
     * The URL may vary depending on the ID of the library file that shall be
     * requested. Therefore, the library file ID is passed to this method.
     *
     * @param $library_file_id The library file ID to which a request URL shall
     *     be generated.
     *
     * @returns string The URL for the request action of the plugin.
     */
    public function getRequestURL(string $library_file_id) : string;


    /**
     * Generates the title for the plugin action to create a request.
     * That title may vary depending on the library file that shall be requested.
     * Therefore, the file is passed to this method.
     *
     * @param LibraryFile $file The file to which the request URL title shall be
     *     generated.
     *
     * @returns string The title for the request URL action of the plugin.
     */
    public function getRequestTitle() : string;


    /**
     * Generates the icon for the plugin action to create a request.
     * That icon may vary depending on the library file that shall be requested.
     * Therefore, the file is passed to this method.
     *
     * @param LibraryFile $file The file to which the request URL icon shall be
     *     generated.
     *
     * @returns Icon The icon for the request URL action of the plugin.
     */
    public function getRequestIcon() : Icon;
}
