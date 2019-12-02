<? if (count($domains) == 0) : ?>
    <?= MessageBox::info(_('Es sind keine Nutzerdomänen vorhanden.')) ?>
<? else : ?>
    <form method="post">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <colgroup>
                <col style="width: 40%">
                <col style="width: 20%">
                <col style="width: 15%">
                <col style="width: 15%">
                <col style="width: 10%">
            </colgroup>
            <caption>
                <?= _('Liste der Nutzerdomänen') ?>
            </caption>
            <thead>
                <tr>
                    <th><?= _('Name') ?></th>
                    <th><?= _('ID') ?></th>
                    <th><?= _('Nutzer/-innen') ?></th>
                    <th><?= _('Veranstaltungen') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
            <? foreach ($domains as $domain): ?>
                <tr>
                    <td><?= htmlReady($domain->name) ?></td>
                    <td><?= htmlReady($domain->id) ?></td>
                    <td><?= count($domain->users) ?></td>
                    <td><?= count($domain->courses) ?></td>
                    <td class="actions">
                        <a href="<?= $controller->link_for("admin/domain/edit/{$domain->id}") ?>" data-dialog="size=auto">
                            <?= Icon::create('edit')->asImg(tooltip2(_('bearbeiten'))) ?>
                        </a>
                    <? if (count($domain->users) === 0): ?>
                        <?= Icon::create('trash')->asInput(tooltip2(_('löschen')) + [
                            'class'        => 'text-top',
                            'formaction'   => $controller->url_for("admin/domain/delete/{$domain->id}"),
                            'data-confirm' => _('Wollen Sie die Nutzerdomäne wirklich löschen?')
                        ]) ?>
                    <? else: ?>
                        <?= Icon::create('trash', Icon::ROLE_INACTIVE)->asImg(['title' => _('Domänen, denen noch Personen zugewiesen sind, können nicht gelöscht werden.')]) ?>
                    <? endif; ?>
                    </td>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
    </form>
<? endif ?>
