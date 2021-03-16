<? if ($deputies && count($deputies)): ?>
    <form method="post" action="<?= $controller->link_for('settings/deputies/store') ?>" class="default">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default no-hover">
            <colgroup>
                <col>
                <? if ($edit_about_enabled): ?>
                    <col style="width: 200px">
                <? endif ?>
                <col>
            </colgroup>
            <thead>
            <tr>
                <th><?= _('Nutzer'); ?></th>
                <? if ($edit_about_enabled): ?>
                    <th><?= _('darf mein Profil bearbeiten'); ?></th>
                <? endif ?>
                <th class="actions"><?= _('Aktion'); ?></th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($deputies as $deputy): ?>
                <? $deputy_fullname = $deputy->getDeputyFullname() ?>
                <tr>
                    <td>
                        <?= Avatar::getAvatar($deputy->user_id)->getImageTag(Avatar::SMALL) ?>
                        <?= htmlReady($deputy_fullname . ' (' . $deputy->username . ', ' . _('Status') . ': ' . $deputy->perms . ')') ?>
                    </td>
                    <? if ($edit_about_enabled): ?>
                        <td style="text-align: center">
                            <div class="hgroup">
                                <label>
                                    <input type="radio" name="edit_about[<?= $deputy->user_id ?>]" value="1"
                                        <? if ($deputy->edit_about) echo 'checked'; ?>>
                                    <?= _('ja') ?>
                                </label>

                                <label>
                                    <input type="radio" name="edit_about[<?= $deputy->user_id ?>]" value="0"
                                        <? if (!$deputy->edit_about) echo 'checked'; ?>>
                                    <?= _('nein') ?>
                                </label>
                            </div>
                        </td>
                    <? endif ?>
                    <td class="actions">
                        <?= Icon::create('trash')->asInput(
                            [
                                'formaction'   => $controller->deleteURL($deputy),
                                'data-confirm' => _('Wollen Sie die Standardvertretung wirklich löschen?')
                            ]
                        ) ?>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="<?= 2 + (int)$edit_about_enabled ?>">
                    <?= Studip\Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen speichern')]) ?>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
<? else: ?>
    <?= MessageBox::info(_('Sie haben noch niemanden als Ihre Standardvertretung eingetragen. Benutzen Sie die Aktion in der Sidebar, um dies zu tun.')); ?>
<? endif; ?>