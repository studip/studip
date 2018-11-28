<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
$number_of_seminars = $sem_type->countSeminars();
$id = $sem_type['id'];
?>
<li id="sem_type_<?= htmlReady($id) ?>">
    <span class="name_container">
        <span class="id" title="<?= _("ID des Veranstaltungstyps") ?>">
            [<?= htmlReady($sem_type['id']) ?>]
        </span>
        <span class="name_html">
            <?= htmlReady($sem_type['name']) ?>
        </span>
        <span class="name_input" style="display: none;">
            <input type="text" value="<?= htmlReady($sem_type['name']) ?>">
        </span>
    </span>
    (<?= sprintf(_("%s Veranstaltungen"), $number_of_seminars ? $number_of_seminars : _("keine")) ?>)
    <a href="#" class="sem_type_edit" onClick="jQuery(this).closest('li').find('.name_container').children().toggle().find('input').focus(); return false;" title="<?= _("Veranstaltungstyp umbenennen") ?>"><?= Icon::create('edit', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
    <? if ($number_of_seminars == 0) : ?>
        <a href="#" class="sem_type_delete" onClick="return false;" title="<?= _("Veranstaltungstyp löschen") ?>"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
    <? endif ?>
</li>