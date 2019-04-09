<form action="<?= $controller->url_for('admin/semester/delete/bulk') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

<table class="default" id="semesters">
    <caption><?= _('Semester') ?></caption>
    <colgroup>
        <col width="20px">
        <col>
        <col width="10%">
        <col width="15%">
        <col width="15%">
        <col width="20%">
        <col width="48px">
    </colgroup>
    <thead>
        <tr>
            <th>
                <input type="checkbox"
                       data-proxyfor="#semesters tbody :checkbox"
                       data-activates="#semesters tfoot button">
            </th>
            <th><?= _('Name') ?></th>
            <th><?= _('Kürzel') ?></th>
            <th><?= _('Zeitraum') ?></th>
            <th><?= _('Veranstaltungszeitraum') ?></th>
            <th><?= _('Veranstaltungen') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<? if (empty($semesters)): ?>
        <tr>
            <td colspan="7" style="text-align: center;">
            <? if ($filter): ?>
                <?= _('In der gewählten Ansicht gibt es keine Einträge.') ?>
            <? else: ?>
                <?= _('Es wurden noch keine Semester angelegt.') ?><br>
                <?= Studip\LinkButton::create(_('Neues Semester anlegen'),
                                              $controller->url_for('admin/semester/edit'),
                                              ['data-dialog' => 'size=auto']) ?>
            <? endif; ?>
            </td>
        </tr>
<? else: ?>
    <? foreach ($semesters as $semester): ?>
        <tr <? if ($semester->current) echo 'style="font-weight: bold;"'; ?>>
            <td>
                <input type="checkbox" name="ids[]" value="<?= $semester->id ?>"
                       <? if ($semester->absolute_seminars_count) echo 'disabled'; ?>>
            </td>
            <td title="<?= htmlReady($semester->description) ?>">
                <?= htmlReady($semester->name) ?>
                <? if (!$semester->visible): ?>
                <?= '(' . _('gesperrt') . ')'; ?>
                <? endif; ?>
            </td>
            <td>
                <?= htmlReady($semester->semester_token ?: '- ' . _('keins') . ' -') ?>
            </td>
            <td>
                <?= strftime('%x', $semester->beginn) ?>
                -
                <?= strftime('%x', $semester->ende) ?>
            </td>
            <td>
                <?= strftime('%x', $semester->vorles_beginn) ?>
                -
                <?= strftime('%x', $semester->vorles_ende) ?>
            </td>
            <td>
                <?= $semester->absolute_seminars_count ?>
                <?= sprintf(_('(+%u implizit)'),
                            $semester->continuous_seminars_count + $semester->duration_seminars_count) ?>
            </td>
            <td class="actions" nowrap>

            <?
                $actionMenu = ActionMenu::get();

                $actionMenu->addLink(
                    $controller->url_for("admin/semester/edit/{$semester->id}"),
                    _('Semesterangaben bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                );

                 if ($semester->visible) {
                    $actionMenu->addLink(
                        $controller->url_for("admin/semester/lock/{$semester->id}"),
                        _('Semester sperren'),
                        Icon::create('lock-unlocked'),
                        ['data-dialog' => 'size=auto']
                    );
                } else {
                    $actionMenu->addButton(
                        'unlock',
                        _('Semester entsperren'),
                        Icon::create('lock-locked', Icon::ROLE_CLICKABLE, [
                            'title'        => _('Semester entsperren'),
                            'formaction'   => $controller->url_for("admin/semester/unlock/{$semester->id}"),
                            'data-confirm' => _('Soll das Semester wirklich entsperrt werden? Anmelderegeln und Sperrebenen werden nicht verändert.'),
                            'style'        => 'vertical-align: text-bottom'
                        ])
                    );
                }

                if ($semester->absolute_seminars_count) {
                    $actionMenu->addLink(
                        $controller->url_for("admin/semester"),
                        _('Semester hat Veranstaltungen und kann daher nicht gelöscht werden.'),
                        Icon::create('trash', Icon::ROLE_INACTIVE)
                    );
                } else {
                    $actionMenu->addButton(
                        'delete',
                        _('Semester löschen'),
                        Icon::create('trash', Icon::ROLE_CLICKABLE, [
                            'title'        => _('Semester löschen'),
                            'formaction'   => $controller->url_for("admin/semester/delete/{$semester->id}"),
                            'data-confirm' => _('Soll das Semester wirklich gelöscht werden?'),
                            'style'        => 'vertical-align: text-bottom'
                        ])
                    );
                }

                echo $actionMenu;
            ?>

            </td>
        </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7">
                <?= Studip\Button::create(_('Markierte Einträge löschen'), 'delete', [
                        'data-confirm' => _('Sollen die Semester wirklich gelöscht werden?')
                ]) ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>
