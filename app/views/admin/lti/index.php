<form action="" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Aktuell konfigurierte LTI-Tools') ?>
        </caption>

        <colgroup>
            <col style="width: 30%;">
            <col style="width: 40%;">
            <col style="width: 20%;">
            <col style="width: 5%;">
            <col style="width: 5%;">
        </colgroup>

        <thead>
            <th><?= _('Name der Anwendung') ?></th>
            <th><?= _('URL der Anwendung') ?></th>
            <th><?= _('Consumer-Key') ?></th>
            <th><?= _('Links') ?></th>
            <th class="actions">
                <?= _('Aktionen') ?>
            </th>
        </thead>

        <tbody>
            <? foreach ($tools as $tool): ?>
                <tr>
                    <td>
                        <a href="<?= $controller->link_for('admin/lti/edit/' . $tool->id) ?>" title="<?= _('LTI-Tool konfigurieren') ?>" data-dialog>
                            <?= htmlReady($tool->name) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= htmlReady($tool->launch_url) ?>" target="_blank" class="link-extern">
                            <?= htmlReady($tool->launch_url) ?>
                        </a>
                    </td>
                    <td>
                        <?= htmlReady($tool->consumer_key) ?>
                    </td>
                    <td>
                        <?= count($tool->links) ?>
                    </td>
                    <td class="actions">
                        <a href="<?= $controller->link_for('admin/lti/edit/' . $tool->id) ?>" title="<?= _('LTI-Tool konfigurieren') ?>" data-dialog>
                            <?= Icon::create('edit') ?>
                        </a>
                        <?= Icon::create('trash')->asInput([
                            'formaction' => $controller->url_for('admin/lti/delete/' . $tool->id),
                            'title' => _('LTI-Tool löschen'),
                            'data-confirm' => sprintf(_('Wollen Sie wirklich das LTI-Tool "%s" löschen?'), htmlReady($tool->name))
                        ]) ?>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
</form>
