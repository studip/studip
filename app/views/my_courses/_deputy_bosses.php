<?
# Lifter010: TODO
?>
<? SkipLinks::addIndex(_("Personen, deren Standardvertretung ich bin"), 'my_deputy_bosses') ?>
<table class="default" id="my_deputy_bosses">
    <caption>
        <?= _("Personen, deren Standardvertretung ich bin") ?>
    </caption>
    <colgroup>
        <col width="30px">
        <col>
    </colgroup>
    <thead>
    <tr>
        <th></th>
        <th><?= _("Name") ?></th>
        <th><?= _('Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($my_bosses as $boss) : ?>
        <tr>
            <td>
                <?= Avatar::getAvatar($boss['user_id'])->getImageTag(Avatar::SMALL, ['title' => $boss['fullname']]) ?>
            </td>
            <td>
                <?= htmlReady($boss['fullname'])?>
            </td>
            <td>
                <? if ($boss['edit_about'] && $deputies_edit_about_enabled) : ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $boss['username']]) ?>">
                        <?= Icon::create('person', 'clickable', ['title' => _('Personenangaben bearbeiten')])->asImg() ?>
                    </a>
                <? endif ?>
                <a href="<?= URLHelper::getLink('dispatch.php/messages/write',
                    ['filter' => 'send_sms_to_all',
                          'rec_uname' => $boss['username']])?>" data-dialog>
                    <?= Icon::create('mail', 'clickable', ['title' => sprintf(_('Nachricht an %s senden'), htmlReady($boss['fullname']))])->asImg() ?>
                </a>
                <a href="<?= $controller->url_for('my_courses/delete_boss',
                        $boss['user_id'])?>" data-confirm="<?=sprintf(
                        _('Wollen Sie sich wirklich als Standardvertretung von %s austragen?'),
                        htmlReady($boss['fullname'])) ?>">
                    <?= Icon::create('trash', 'clickable', ['title' => sprintf(_('Mich als Standardvertretung von %s austragen'),htmlReady($boss['fullname']))])->asImg() ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<br/>
