<?php
/**
 * HTML-class for the Stud.IP-project.
 * Based on scripts from "http://tut.php-q.net/".
 *
 * @author  Alexander Willner <mail@AlexanderWillner.de>
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+
require_once 'HTML.class.php';

class HTMpty extends HTMLempty
{

    public function attr($name, $wert = null)
    {
        parent::addAttr($name, $wert);
    }

    public function stri($string)
    {
        parent::addString($string);
    }

}

class HTM extends HTML
{

    public function stri($string)
    {
        parent::addString($string);
    }

    public function attr($name, $wert = null)
    {
        parent::addAttr($name, $wert);
    }

    public function html($_content)
    {
        parent::addHTMLContent($_content);
    }

    public function cont($_content)
    {
        parent::addContent($_content);
    }
}
