<?php
# Lifter010: TODO
/*
 * PortalPlugin.class.php - start / portal page plugin interface
 *
 * Copyright (c) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 * Copyright (c) 2009 - Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface PortalPlugin
{
    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the start or portal page. Return NULL to
     * render nothing for this plugin.
     *
     * The template will automatically get a standard layout, which
     * can be configured via attributes set on the template:
     *
     *  title        title to display, defaults to plugin name
     *  icon_url     icon for this plugin (if any)
     *  admin_url    admin link for this plugin (if any)
     *  admin_title  title for admin link (default: Administration)
     *
     * @return object   template object to render or NULL
     */
    function getPortalTemplate();
}
