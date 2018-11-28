<table class="default">
    <caption><?= _('Inhalts-Nutzungsbedingungen') ?></caption>
    <thead>
        <tr>
            <th></th>
            <th><?= _('ID') ?></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Download-Bedingung') ?></th>
            <th><?= _('Standard')?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <? if (count($entries) === 0): ?>
        <tr>
            <td colspan="6">
                <?= MessageBox::info(_('Es sind keine Nutzungsbedingungen für Inhalte definiert!')) ?>
                <?= Studip\LinkButton::create(
                    _('Neue Nutzungsbedingungen definieren'),
                    $controller->url_for('admin/content_terms_of_use/add'),
                    ['data-dialog' => 'reload-on-close;size=auto']
                ) ?>
            </td>
        </tr>
    <? endif; ?>
    <? foreach ($entries as $entry): ?>
        <tr>
            <td><?= Icon::create($entry->icon, Icon::ROLE_INFO) ?></td>
            <td><?= htmlReady($entry->id) ?></td>
            <td><?= htmlReady($entry->name) ?></td>
            <td>
                <?= htmlReady(ContentTermsOfUse::describeCondition($entry->download_condition)) ?>
            </td>
            <td><?= $entry->is_default ? _('Ja') : _('Nein') ?></td>
            <td class="actions">
                <a href="<?= $controller->url_for('admin/content_terms_of_use/edit', ['entry_id' => $entry->id]) ?>" data-dialog>
                    <?= Icon::create('edit', Icon::ROLE_CLICKABLE) ?>
                </a>
                <a href="<?= $controller->url_for('admin/content_terms_of_use/delete', ['entry_id' => $entry->id]) ?>" data-dialog="size=auto">
                    <?= Icon::create('trash', Icon::ROLE_CLICKABLE) ?>
                </a>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
