<?php
/*
 * DetailspagePlugin.class.php
 *
 * Copyright (c) 2019 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */
interface DetailspagePlugin
{
    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the details page. Return NULL to
     * render nothing for this plugin or this course.
     *
     * The template will automatically get a standard layout, which
     * can be configured via attributes set on the template:
     *
     *  title        title to display, defaults to plugin name
     *
     * @return object   template object to render or NULL
     */
    function getDetailspageTemplate($course);
}
