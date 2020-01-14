<? $range_object = $request->getRangeObject();?>
<tr>
    <td data-sort-value="<?= htmlReady($request->marked) ?>">
        <a href="#" class="request-marking-icon"
           data-request_id="<?= htmlReady($request->id) ?>"
           data-marked="<?= htmlReady($request->marked) ?>"
           title="<?= _('Markierung ändern') ?>">
        </a>
    </td>
    <td>
        <? if ($range_object instanceof Course) : ?>
            <?= htmlReady($range_object->veranstaltungsnummer)?>
        <? endif ?>
    </td>
    <td>
        <? if ($range_object instanceof Course): ?>
            <a href="<?= URLHelper::getLink(
                     'dispatch.php/course/details',
                     ['sem_id' => $range_object->id]
                     ) ?>">
                <?= htmlReady($range_object->name)?>
            </a>
        <? elseif ($range_object instanceof User): ?>
            <a href="<?= URLHelper::getLink(
                     'dispatch.php/profile',
                     ['username' => $range_object->username]) ?>">
                <?= htmlReady($range_object->getFullName('no_title_rev'))?>
            </a>
        <? endif ?>
    </td>

    <td>
        <? if ($range_object instanceof Course): ?>
            <?= htmlReady(join(', ', $range_object->members->findBy('status', 'dozent')->limit(3)->getUserFullname('no_title_rev'))) ?>
        <? endif ?>
    </td>

    <td><?= $request->resource
          ? htmlReady($request->resource->name)
          : '' ?>
    </td>
    <td>
        <?= $request->getProperty('seats') ?>
    </td>
    <td>
        <? if ($request->user instanceof User): ?>
            <a href="<?=URLHelper::getLink('dispatch.php/profile', ['username' => $request->user->username]);?>">
                <?= htmlReady($request->user->getFullName('no_title_rev')) ?>
            </a>
        <? else: ?>
            <?= _('Unbekannt') ?>
        <? endif ?>
    </td>
    <? $intervals = $request->getTimeIntervals() ?>
    <td data-sort-value="<?= htmlReady($intervals[0]['begin']) ?>">
        <?= $request->getTypeString() ?>
        <? if ($request->isSimpleRequest()): ?>
            <?
            $begin = $request->getStartDate();
            $end = $request->getEndDate();
            $different_days = $begin->format('Ymd') != $end->format('Ymd');
            ?>
            <? if (($begin instanceof DateTime) && ($end instanceof DateTime)): ?>
                <br>
                    <? if ($different_days): ?>
                        (<?= sprintf(
                            _('vom %1$s bis %2$s'),
                            $begin->format('d.m.Y H:i'),
                            $end->format('d.m.Y H:i')
                         ) ?>)
                    <? else: ?>
                        (<?= sprintf(
                            _('am %1$s von %2$s bis %3$s'),
                            $begin->format('d.m.Y'),
                            $begin->format('H:i'),
                            $end->format('H:i')
                         ) ?>)
                    <? endif ?>
            <? endif ?>
        <? else: ?>
            <? $begin = $request->getStartDate() ?>
            <? if ($begin instanceof DateTime): ?>
                <br>
                    (<?= htmlReady(
                        sprintf(
                            _('ab %s'),
                            $begin->format('d.m.Y H:i')
                        )
                     ) ?>)
            <? endif ?>
            <?= tooltipIcon(join("\n", $request->getTimeIntervalStrings())) ?>
        <? endif ?>
    </td>
    <? $priority = $request->getPriority() ?>
    <td data-sort-value="<?= htmlReady($priority) ?>">
        <?= htmlReady($priority) ?>
    </td>
    <td data-sort-value="<?= htmlReady($request->chdate) ?>">
        <?= strftime('%x', $request->chdate)?>
    </td>
    <td class="actions">
        <a href="<?= $controller->link_for(
                 'resources/room_request/resolve/' . $request->id) ?>"
           data-dialog="size=big"
           title="<?= _('Anfrage selbst auflösen') ?>">
            <?= Icon::create('room-request')->asImg('20px') ?>
        </a>
    </td>
</tr>
