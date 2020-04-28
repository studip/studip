<option value="edit"><?= _('Bearbeiten') ?></option>
<option value="preparecancel"><?= _('Ausfallen lassen') ?></option>
<option value="undelete"><?= _('Stattfinden lassen') ?></option>
<? if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) : ?>
    <option value="request"><?= _('Anfrage auf ausgewählte Termine stellen') ?> </option>
<? endif ?>
