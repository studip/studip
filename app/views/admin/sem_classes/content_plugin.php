<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
?>
<div class="plugin<?= ($plugin['enabled'] ? "" : " deactivated").(is_numeric($plugin['id']) ? "" : " core").($sticky ? " sticky" : "") ?>" id="plugin_<?= $plugin_id ?>" <?= $plugin['enabled'] ? "" : ' title="'._("Plugin ist momentan global deaktiviert.").'"' ?>>
    <h2><?= $plugin['name'] ?></h2>
    <div>
        <label><input type="checkbox" value="1" name="nonsticky"<?= !$sticky ? " checked" : "" ?>><?= _("Wählbar") ?></label>
        <br>
        <label><input type="checkbox" value="1" name="active"<?= $activated ? " checked" : "" ?>><?= _("Standard Aktiv") ?></label>
    </div>
</div>
