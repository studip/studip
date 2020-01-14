<?php

/**
 * ResourcePropertyDefinitionException.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * This exception is thrown when a resource property definition is requested
 * but not defined for the resource property or if it is defined
 * but cannot be stored.
 */
class ResourcePropertyDefinitionException extends InvalidArgumentException
{

}
