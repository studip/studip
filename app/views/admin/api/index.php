<? if (!empty($consumers)): ?>
<form action="#" method="post" class="default">
<table class="default">
    <caption><?= _('Registrierte Applikationen') ?></caption>
    <thead>
        <tr>
            <th><?= ('Aktiv') ?></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Typ') ?></th>
            <th><?= _('Kontakt') ?></th>
            <th><?= _('Kommerziell') ?></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
<? foreach ($consumers as $consumer): ?>
        <tr>
            <td id="<?= $consumer->id ?>">
                <a href="<?= $controller->url_for('admin/api/toggle', $consumer->id, $consumer->active ? 'off' : 'on') ?>">
                    <?= Icon::create('checkbox-' . ($consumer->active ? '' : 'un') . 'checked', 'clickable')->asImg() ?>
                </a>
            </td>
            <td>
            <? if ($consumer->url): ?>
                <a href="<?= htmlReady($consumer->url) ?>" target="_blank" rel="noopener noreferrer">
                    <?= htmlReady($consumer->title) ?>
                </a>
            <? else: ?>
                <?= htmlReady($consumer->title) ?>
            <? endif; ?>
            </td>
            <td><?= $types[$consumer->type] ?: '&nbsp;' ?></td>
            <td>
                <a href="mailto:<?= htmlReady($consumer->email) ?>">
                    <?= htmlReady($consumer->contact) ?>
                </a>
            </td>

            <td><?= Icon::create('checkbox-' . ($consumer->commercial ? '' : 'un') . 'checked', 'clickable')->asImg() ?></td>
            <td class="actions">
                <a href="<?= $controller->url_for('admin/api/keys', $consumer->id) ?>"
                   data-dialog="size=auto"
                   title="<?= htmlReady(sprintf(_('Schlüssel anzeigen für Applikation "%s"'), $consumer->title)) ?>">
                    <?= Icon::create('info-circle', 'clickable')->asImg() ?>
                </a>
                <a href="<?= $controller->url_for('admin/api/edit', $consumer->id) ?>" title="<?= _('Applikation bearbeiten') ?>" data-dialog>
                    <?= Icon::create('edit', 'clickable')->asImg() ?>
                </a>
                <a href="<?= $controller->url_for('admin/api/permissions', $consumer->id) ?>" title="<?= _('Zugriffsberechtigungen verwalten') ?>">
                    <?= Icon::create('admin', 'clickable')->asImg() ?>
                </a>
                <?= Icon::create('trash')->asInput([
                    'formaction'   => $controller->url_for('admin/api/delete/', $consumer->id),
                    'title'        => sprintf(_('Applikation "%s" entfernen'), $consumer->title),
                    'data-confirm' => '',
                    'style'        => 'vertical-align: middle'
                ]) ?>
            </td>
        </tr>
<? endforeach; ?>
    </tbody>
</table>
</form>

<? else: ?>
<p>
    <?= MessageBox::info(_('Es wurde noch keine Applikation registriert.'),
        [sprintf(_('Klicken Sie <a href="%s">hier</a>, um eine Applikation zu registrieren.'), $controller->url_for('admin/api/edit'))]) ?>
</p>
<? endif; ?>
