<? SkipLinks::addIndex(_('Personen, deren Standardvertretung ich bin'), 'my_deputy_bosses') ?>
<table class="default" id="my_deputy_bosses">
    <caption>
        <?= _('Personen, deren Standardvertretung ich bin') ?>
    </caption>
    <colgroup>
        <col style="width: 30px">
        <col>
    </colgroup>
    <thead>
    <tr>
        <th></th>
        <th><?= _('Name') ?></th>
        <th class="actions"><?= _('Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($my_bosses as $boss) : ?>
        <? $boss_fullname = $boss->getBossFullname(); ?>
        <tr>
            <td>
                <?= Avatar::getAvatar($boss->user_id)->getImageTag(Avatar::SMALL, ['title' => $boss_fullname]) ?>
            </td>
            <td>
                <?= htmlReady($boss_fullname)?>
            </td>
            <td class="actions">
            <? if ($boss->edit_about && $deputies_edit_about_enabled) : ?>
                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $boss->boss_username]) ?>">
                    <?= Icon::create('person')->asImg(['title' => _('Personenangaben bearbeiten')]) ?>
                </a>
            <? endif ?>
                <a href="<?= URLHelper::getLink(
                    'dispatch.php/messages/write',
                    ['filter' => 'send_sms_to_all', 'rec_uname' => $boss->boss_username])?>" data-dialog>
                    <?= Icon::create('mail')->asImg(['title' => sprintf(_('Nachricht an %s senden'), htmlReady($boss_fullname))]) ?>
                </a>
                <a href="<?= $controller->link_for('my_courses/delete_boss/' . $boss->range_id) ?>"
                   data-confirm="<?= sprintf(_('Wollen Sie sich wirklich als Standardvertretung von %s austragen?'), htmlReady($boss_fullname)) ?>">
                    <?= Icon::create('trash')->asImg([
                        'title' => sprintf(_('Mich als Standardvertretung von %s austragen'), htmlReady($boss_fullname))
                    ]) ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
