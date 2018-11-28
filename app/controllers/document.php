<?php
/**
* document.php - compatibility for old download links
*
* This controller contains actions related to single files.
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of
* the License, or (at your option) any later version.
*
* @author      André Noack <noack@data-quest.de>
* @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
* @category    Stud.IP
*/
class DocumentController extends StudipController
{
    function download_action($file_ref_id, $disposition = 'attachment')
    {
        $file_ref = FileRef::find($file_ref_id);
        if ($file_ref) {
            $this->redirect($file_ref->getDownloadURL($disposition === 'inline' ? 'normal' : 'force'));
        } else {
            throw new Trails_Exception(404);
        }
    }
}
