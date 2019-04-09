<div class="online-list">
    <div id="online_contacts">
        <table class="default">
            <caption>
                <?= _('Kontakte') ?>
            </caption>
            <colgroup>
                <col width="<?= reset(Avatar::getDimension(Avatar::SMALL)) ?>px">
                <col>
                <col>
                <col width="1%">
            </colgroup>
            <? if (count($users['buddies']) > 0): ?>
                <thead>
                <tr>
                    <th colspan="2"><?= _('Name') ?></th>
                    <th><?= _('Letztes Lebenszeichen') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
                </thead>
                <tbody>
                <? $last_group = false;
                foreach ($users['buddies'] as $buddy):
                    ?>
                    <? if ($showGroups && $last_group !== $buddy['group']): ?>
                    <tr>
                        <th colspan="4">
                            <a href="<?= $controller->link_for('contact/index/' . ($buddy['group_id'] != 'all' ? $buddy['group_id'] : '')) ?>"
                               class="link-intern" style="color: #000;">
                                <?= htmlReady($buddy['group']) ?>
                            </a>
                        </th>
                    </tr>
                    <? $last_group = $buddy['group'];
                endif;
                    ?>
                    <?= $this->render_partial('online/user-row', ['user' => $buddy]) ?>
                <? endforeach; ?>
                </tbody>
            <? else: ?>
                <? if ($contact_count === 0): ?>
                    <tbody>
                    <tr>
                        <td colspan="4">
                            <?= _('Sie haben keine Kontakte ausgewählt.') ?>
                        </td>
                    </tr>
                    </tbody>
                <? elseif (count($users['buddies']) === 0): ?>
                    <tbody>
                    <tr>
                        <td colspan="4">
                            <?= _('Es sind keine Ihrer Kontakte online.') ?>
                        </td>
                    </tr>
                    </tbody>
                <? endif; ?>
            <? endif; ?>
            <tfoot>
            <tr>
                <td colspan="4">
                    <? printf(_('Zum Adressbuch (%u Einträge) klicken Sie %shier%s.'),
                        $contact_count,
                        '<a href="' . $controller->link_for('contact') . '">', '</a>') ?>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>

    <? if (!$showOnlyBuddies): ?>
        <div id="online_buddies">
            <table class="default">
                <caption>
                    <?= _('Andere NutzerInnen') ?>
                    <? if ($users['others'] > 0): ?>
                        <small>
                            (<?= sprintf(_('+ %u unsichtbare NutzerInnen'), $users['others']) ?>)
                        </small>
                    <? endif; ?>
                </caption>
                <colgroup>
                    <col width="<?= reset(Avatar::getDimension(Avatar::SMALL)) ?>px">
                    <col>
                    <col>
                    <col width="1%">
                </colgroup>
                <? if (count($users['users']) > 0): ?>
                    <thead>
                    <tr>
                        <th colspan="2"><?= _('Name') ?></th>
                        <th><?= _('Letztes Lebenszeichen') ?></th>
                        <th class="actions"><?= _('Aktionen') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <? foreach (array_slice($users['users'], $page * $limit, $limit) as $user): ?>
                        <?= $this->render_partial('online/user-row', compact('user')) ?>
                    <? endforeach; ?>
                    </tbody>
                <? elseif ($users['others'] > 0): ?>
                    <tbody>
                    <tr>
                        <td colspan="4">
                            <?= _('Keine sichtbaren Nutzer online.') ?>
                        </td>
                    </tr>
                    </tbody>
                <? else: ?>
                    <tbody>
                    <tr>
                        <td colspan="4">
                            <?= _('Kein anderer Nutzer ist online.') ?>
                        </td>
                    </tr>
                    </tbody>
                <? endif; ?>
                <tfoot>
                <tr>
                    <td colspan="4" class="actions">
                        <?= Pagination::create(count($users['users']), $page, $limit)->asLinks() ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    <? endif; ?>
</div>
