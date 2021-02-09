<tr>
    <td>
        <a data-dialog href="<?= $controller->link_for($linkchunk, compact('field')) ?>">
            <?= htmlReady($field) ?>
        </a>
        <? if (!empty($description)): ?>
            <br><small><?= htmlReady($description) ?></small>
        <? endif; ?>
    </td>
    <td class="wrap-content">
        <? if ($type === 'string' || $type === 'i18n'): ?>
            <em><?= htmlReady($value) ?></em>
        <? elseif ($type === 'integer'): ?>
            <?= (int)$value ?>
        <? elseif ($type === 'boolean'): ?>
            <? if ($value): ?>
                <?= Icon::create('accept', Icon::ROLE_STATUS_GREEN, ['title' => _('TRUE')]) ?>
            <? else : ?>
                <?= Icon::create('decline', Icon::ROLE_STATUS_RED, ['title' => _('FALSE')]) ?>
            <? endif; ?>
        <? endif; ?>
    </td>
    <td><?= htmlReady($type) ?></td>
    <td class="actions">
        <a data-dialog="size=auto" href="<?= $controller->link_for($linkchunk, compact('field')) ?>">
            <?= Icon::create('edit')->asImg(['title' => _('Konfigurationsparameter bearbeiten')]) ?>
        </a>
    </td>
</tr>
