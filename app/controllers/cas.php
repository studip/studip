<?php
/**
 * cas.php - CAS single sign on controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'app/controllers/studip_controller.php';
require_once 'lib/classes/cas/CAS_PGTStorage_Cache.php';

class CasController extends StudipController
{
    /**
     * proxy action of this controller: receive and store PGT data
     */
    public function proxy_action()
    {
        $pgt = Request::get('pgtId');
        $pgt_iou = Request::get('pgtIou');
        $cas_config = $GLOBALS['STUDIP_AUTH_CONFIG_CAS'];
        $cas = new CAS_Client(CAS_VERSION_2_0, true, $cas_config['host'], $cas_config['port'], $cas_config['uri'], false);
        $pgt_storage = new CAS_PGTStorage_Cache($cas);
        $pgt_storage->write($pgt, $pgt_iou);
        $this->render_nothing();
    }
}
