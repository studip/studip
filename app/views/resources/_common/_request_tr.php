<? $range_object = $request->getRangeObject(); ?>
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
            <?= htmlReady($range_object->veranstaltungsnummer) ?>
        <? endif ?>
    </td>
    <td>
        <a href="<?= $controller->link_for('resources/room_request/resolve/' . $request->id) ?>" data-dialog="size=big"
           title="<?= _('Anfrage auflösen') ?>">
            <? if ($range_object instanceof Course): ?>
                <?= htmlReady($range_object->name) ?>
            <? elseif ($range_object instanceof User): ?>
                <?= htmlReady($range_object->getFullName('no_title_rev')) ?>
            <? endif ?>
        </a>
    </td>

    <td>
        <? if ($range_object instanceof Course): ?>
            <?= htmlReady(
                join(', ', $range_object->members->findBy('status', 'dozent')
                    ->limit(3)->getUserFullname('no_title_rev')
                )
            ) ?>
        <? endif ?>
    </td>
    <td>
        <?= $request->resource ? htmlReady($request->resource->name) : '' ?>
    </td>
    <td>
        <?= $request->getProperty('seats') ?>
    </td>
    <td>
        <? if ($request->user instanceof User): ?>
            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $request->user->username]); ?>">
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
            $begin          = $request->getStartDate();
            $end            = $request->getEndDate();
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
            <? if (count($intervals) > 1 && $intervals[0]['begin'] > 0): ?>
                <br>
                (<?= htmlReady(
                    sprintf(
                        _('ab %s'),
                        date('d.m.Y H:i', $intervals[0]['begin'])
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
        <?= strftime('%x', $request->chdate) ?>
    </td>
    <td class="actions">
        <? $action_menu = ActionMenu::get()
            ->addLink(
                $controller->link_for('resources/room_request/resolve/' . $request->id),
                _('Anfrage auflösen'),
                Icon::create('room-request'),
                [
                    'data-dialog' => 'size=big'
                ])
            ->addLink(
                $controller->link_for('resources/room_request/decline/' . $request->id, ['single-request' => 1]),
                _('Anfrage ablehnen'),
                Icon::create('decline'),
                [
                    'data-dialog' => 'size=big'
                ])
            ->addLink(
                $controller->link_for('resources/room_request/decline/' . $request->id, ['delete' => 1]),
                _('Anfrage löschen'),
                Icon::create('trash'),
                [
                    'data-dialog' => 'size=big'
                ])
        ?>
        <?
        $edit_url            = '';
        $edit_url_attributes = [];
        if ($GLOBALS['perm']->have_studip_perm('tutor', $request->getRangeId())) {
            $edit_url            = $controller->link_for(
                'course/room_requests/request_summary/' . $request->id,
                ['cid' => $request->getRangeId()]
            );
            $edit_url_attributes = ['target' => '_blank'];
        } elseif ($request->isSimpleRequest() && !$request->isReadOnlyForUser($current_user)) {
            $edit_url            = $controller->link_for('resources/room_request/edit/' . $request->id);
            $edit_url_attributes = ['data-dialog' => 'size=auto'];
        }
        if ($edit_url && $edit_urL_attributes) {
            $action_menu->addLink(
                $edit_url,
                _('Anfrage bearbeiten'),
                Icon::create('edit'),
                $edit_url_attributes
            );
        }
        if ($range_object instanceof Course) {
            $action_menu->addLink(
                URLHelper::getLink('dispatch.php/course/details', ['cid' => $range_object->id]),
                _('Veranstaltungsdetails'),
                Icon::create('seminar'),
                ['data-dialog' => 'size=auto']
            );
        }

        if ($range_object instanceof User) {
            $action_menu->addLink(
                URLHelper::getLink('dispatch.php/profile', ['username' => $range_object->username]),
                _('Profil anzeigen'),
                Icon::create('person'),
                ['target' => '_blank']
            );
        }
        ?>
        <?= $action_menu->render() ?>
    </td>
</tr>