<dl>
    <dt><?= _('Termine') ?>:</dt>
    <dd><?= htmlReady($request->getDateString()) ?></dd>
    <dt><?= _('Rüstzeit')?>:</dt>
    <dd>
        <? $preparation_time_minutes = intval($request->preparation_time / 60) ?>
        <?= sprintf(
            ngettext(
                '%d Minute',
                '%d Minuten',
                $preparation_time_minutes
            ),
            $preparation_time_minutes
        ) ?>
    </dd>
    <? if ($request instanceof RoomRequest): ?>
        <dt><?= _('Gewünschter Raum')?>:</dt>
    <? else: ?>
        <dt><?= _('Gewünschte Ressource')?>:</dt>
    <? endif ?>
    <dd>
        <? if ($request->resource): ?>
            <?= htmlReady($request->resource->name) ?>
        <? else: ?>
            <? if ($request instanceof RoomRequest): ?>
                <?= _('Es wurde kein spezifischer Raum gewünscht.') ?>
            <? else: ?>
                <?= _('Es wurde keine spezifische Ressource gewünscht.') ?>
            <? endif ?>
        <? endif ?>
    </dd>
    <? if ($request->isNew()): ?>
        <dt>
            <? if (!($request->properties || $request->resource_id)): ?>
                <?= _('Die Anfrage ist unvollständig, und kann so nicht dauerhaft gespeichert werden!') ?>
            <? else: ?>
                <?= _('Die Anfrage ist neu.') ?>
            <? endif ?>
        </dt>
    <? else: ?>
        <dt><?= _('Erstellt von') ?>:</dt>
        <dd>
            <?= htmlReady(
                $request->user
                ? $request->user->getFullName()
                : _('unbekannt')
            ) ?>
        </dd>
        <dt><?= _('Erstellt am') ?>:</dt>
        <dd><?= date('d.m.Y H:i', $request->mkdate) ?></dd>
        <dt><?= _('Letzte Änderung am') ?>:</dt>
        <dd><?= date('d.m.Y H:i', $request->chdate) ?></dd>
        <dt><?= _('Letzte Änderung von') ?>:</dt>
        <dd>
            <?= htmlReady(
                $request->last_modifier
                ? $request->last_modifier->getFullName()
                : _('unbekannt')
            ) ?>
        </dd>
    <? endif ?>
    <? if ($request instanceof RoomRequest): ?>
        <? if ($request->seats): ?>
            <dt><?= _('Gewünschte Zahl an Sitzplätzen') ?>:</dt>
            <dd><?= htmlReady($request->seats) ?></dd>
        <? endif ?>
        <? if ($request->category): ?>
            <dt><?= _('Gewünschter Raumtyp') ?>:</dt>
            <dd><?= htmlReady($request->category->name) ?></dd>
        <? endif ?>
    <? endif ?>
    <? if ($request->properties): ?>
        <? $mandatory_properties = (
            $request instanceof RoomRequest
            ? ['seats', 'room_type']
            : []
            ) ?>
        <? foreach ($request->properties as $property): ?>
            <? if (!in_array($property->name, $mandatory_properties)): ?>
                <dt><?= htmlReady($property->display_name) ?></dt>
                <dd><?= htmlReady($property->__toString()) ?></dd>
            <? endif ?>
        <? endforeach ?>
    <? endif ?>
    <dt><?= _('Bearbeitung durch') ?>:</dt>
    <dd>
        <?= htmlReady(
            $request->last_modifier
            ? $request->last_modifier->getFullName()
            : _('unbekannt')
        ) ?>
    </dd>
    <dt><?= _('Bearbeitungsstatus') ?></dt>
    <dd><?= htmlReady($request->getStatusText()) ?></dd>
    <? if ($request->comment) : ?>
        <dt><?= _('Nachricht an die Administration') ?></dt>
        <dd><?= htmlReady($request->comment) ?></dd>
    <? endif ?>
    <? if ($request->reply_comment) : ?>
        <dt><?= _('Nachricht der Adminstration') ?>:</dt>
        <dd><?= htmlReady($request->reply_comment) ?></dd>
    <? endif ?>
</dl>
