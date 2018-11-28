<? use Studip\Button, Studip\LinkButton; ?>

<? if (empty($consumers)): ?>
<?= MessageBox::info(_('Sie haben noch keinen Apps Zugriff auf Ihren Account gewährt.')) ?>
<? else: ?>
<table class="oauth-apps default">
    <caption><?= _('Applikationen') ?></caption>
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th>&nbsp;</th>
    </thead>
    <tbody>
    <? foreach ($consumers as $consumer): ?>
        <tr>
            <td>
                <h3>
                <? if ($consumer->url): ?>
                    <a href="<?= htmlReady($consumer->url) ?>" target="_blank" rel="noopener noreferrer">
                        <?= htmlReady($consumer->title) ?>
                    </a>
                <? else: ?>
                    <?= htmlReady($consumer->title) ?>
                <? endif; ?>
                <? if ($type = $types[$consumer->type]): ?>
                    <small>(<?= htmlReady($type) ?>)</small>
                <? endif; ?>
                </h3>
            <? if ($consumer->description): ?>
                <p><?= htmlReady($consumer->description) ?></p>
            <? endif; ?>
            </td>
            <td class="actions">
                <?= LinkButton::createCancel(
                    _('App entfernen'),
                    $controller->url_for('api/authorizations/revoke', $consumer->id),
                    ['data-confirm' => _('Wollen Sie der App wirklich den Zugriff auf Ihre Daten untersagen?')]
                ) ?>
            </td>
        </tr>
<? endforeach; ?>
    </tbody>
</table>
<? endif; ?>
