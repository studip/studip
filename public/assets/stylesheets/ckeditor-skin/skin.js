/**
 * skin.js - Register Stud.IP skin in CKEditor.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */

// register the skin
CKEDITOR.skin.name = 'studip';  

// register browser-specific skin files
CKEDITOR.skin.ua_editor = 'ie,iequirks,ie7,ie8,gecko';
CKEDITOR.skin.ua_dialog = 'ie,iequirks,ie7,ie8,opera';
