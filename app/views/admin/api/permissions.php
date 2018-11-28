<form action="<?= $controller->url_for('admin/api/permissions', $consumer_id) ?>" method="post" class="default">
<table class="default">
    <thead>
        <tr>
            <th><?= _('Zugriff') ?></th>
            <th><?= _('Route') ?></th>
            <th><?= _('Methoden') ?></th>
            <th><?= _('Zugriff auf') ?></th>
            <th><?= _('Quelle') ?></th>
        </tr>
    </thead>
<? foreach ($routes as $route => $methods): ?>
    <tbody>

    <? $i = 0; ?>
    <? foreach ($methods as $method => $info): ?>
        <tr style="vertical-align: top;">
            <td>
                <input type="hidden" name="permission[<?= urlencode($route) ?>][<?= urlencode($method) ?>]" value="0">
                <input type="checkbox" name="permission[<?= urlencode($route) ?>][<?= urlencode($method) ?>]"
                <? if (!$global || $global->check($route, $method)): ?>
                    <? if ($permissions->check($route, $method)) echo 'checked'; ?>
                <? else: ?>
                    disabled
                <? endif; ?>
                    value="1">
            </td>
        <? if ($i++): ?>
            <td>&nbsp;</td>
        <? else: ?>
            <td><?= htmlReady($route) ?></td>
        <? endif; ?>
            <td><?= htmlReady($method) ?></td>
            <td><?= htmlReady($info['description']) ?></td>
            <td><?= $info['source'] ?></td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
    <tfoot>
        <tr>
            <td>
                <label>
                    <input type="checkbox" data-proxyfor="[name^=permission]:checkbox"> <?= _('Alle') ?>
                </label>
            </td>
            <td colspan="4">
                <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
            </td>
        </tr>
    </tfoot>
</table>
</form>
