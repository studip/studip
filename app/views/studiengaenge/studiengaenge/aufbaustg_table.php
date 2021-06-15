<? $perm = MvvPerm::get($grund_stg) ?>
<div id="mvv-aufbaustg-table">
    <table class="default sortable-table" data-sortlist="[[0, 1]]">
        <colgroup>
            <col width="45%">
            <col width="15%">
            <col>
            <col width="5%">
        </colgroup>
        <caption>
            <?= _('Aufbaustudiengänge im Master') ?>
            <span class="actions">
            <? if (!$perm->haveFieldPerm('aufbaustg_assignments', MvvPerm::PERM_CREATE) || $grund_stg->isNew()) : ?>
                <?= Icon::create('add', Icon::ROLE_INACTIVE) ?>
            <? else : ?>
                <a href="<?= $controller->url_for('studiengaenge/studiengaenge/aufbaustg_select', $grund_stg->id) ?>" data-dialog="size=auto">
                <?= Icon::create('add', Icon::ROLE_CLICKABLE) ?>
                </a>
            <? endif; ?>
            </span>
        </caption>
        <thead>
            <tr class="sortable">
                <th data-sort="text"><?= _('Aufbaustudiengang'); ?></th>
                <th data-sort="htmldata"><?= _('Typ'); ?></th>
                <th data-sort="false"><?= _('Bemerkung'); ?></th>
                <th data-sort="false"></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($grund_stg->aufbaustg_assignments as $aufbau_stg) : ?>
            <tr>
                <td><?= htmlReady($aufbau_stg->getDisplayName()) ?></td>
                <td data-sort-value="<?= htmlReady($GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'][$aufbau_stg->typ]['name']) ?>">
                    <select name="aufbaustg_typ[<?= $aufbau_stg->id ?>]">
                    <? foreach ($GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'] as $typ_key => $typ_value) : ?>
                        <? if ($GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'][$typ_key]['visible']) : ?>
                            <option value="<?= htmlReady($typ_key) ?>"<?= $typ_key == $aufbau_stg->typ ? ' selected' : '' ?>>
                                <?= htmlReady($typ_value['name']) ?>
                            </option>
                        <? endif; ?>
                    <? endforeach; ?>
                    </select>
                </td>
                <td>
                    <div style="display: inline-block; overflow: hidden; vertical-align: bottom; white-space: nowrap; text-overflow: ellipsis; max-width: 250px;">
                        <?= formatReady($aufbau_stg->kommentar) ?>
                    </div>
                <? if (trim($aufbau_stg->kommentar)) : ?>
                    <a data-dialog="size=auto" href="<?= $controller->link_for('studiengaenge/studiengaenge/aufbaustg_info', $aufbau_stg->id) ?>">
                        <?= Icon::create('info')->asImg(12, tooltip2(_('Bemerkung anzeigen'))) ?>
                    </a>
                <? endif; ?>
                </td>
                <td class="actions">
                <? if ($perm->haveFieldPerm('aufbaustg_assignments', MvvPerm::PERM_WRITE)) : ?>
                    <a data-dialog="" href="<?= $controller->link_for('studiengaenge/studiengaenge/aufbaustg_edit', $aufbau_stg->id) ?>">
                        <?= Icon::create('edit') ?>
                    </a>
                <? endif; ?>
                <? if ($perm->haveFieldPerm('aufbaustg_assignments', MvvPerm::PERM_CREATE)) : ?>
                    <a data-dialog href="<?= $controller->url_for('studiengaenge/studiengaenge/aufbaustg_delete', $aufbau_stg->id) ?>">
                        <?= Icon::create('trash')->asInput([
                            'formaction' => $controller->url_for('studiengaenge/studiengaenge/aufbaustg_delete', $aufbau_stg->id),
                            'title' => _('Zuordnung als Aufbaustudiengang löschen'),
                            'data-confirm' => sprintf(_('Wollen Sie wirklich die Zuordnung von "%s" als "%s" löschen?'), htmlReady($aufbau_stg->getDisplayName()), htmlReady($aufbau_stg->typ))
                        ]) ?>
                    </a>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        <? if (count($grund_stg->aufbaustg_assignments) === 0): ?>
            <tr>
                <td colspan="4">
                <? if ($grund_stg->isNew()) : ?>
                    <?= _('Der neue Studiengang muss erst gespeichert werden, um Aufbaustudiengänge zuordnen zu können.') ?>
                <? else : ?>
                    <?= _('Es wurden noch keine Aufbaustudiengänge angelegt.') ?>
                <? endif; ?>
                </td>
            </tr>
        <? endif; ?>
        </tbody>
    </table>
</div>
