<?
# Lifter010: TODO
?>
<thead>
    <tr>
        <th>
            <?= _('Name') ?>
        </th>
        <th>
            <?= _('ID') ?>
        </th>
        <th>
            <?= _('Nutzer/-innen') ?>
        </th>
        <th class="action">
            <?= _('Aktionen') ?>
        </th>
    </tr>
</thead>
<tbody>
    <? foreach ($domains as $domain): ?>
        <tr>
            <td>
                <? if (isset($edit_id) && $edit_id === $domain->getID()): ?>
                    <input type="hidden" name="id" value="<?= $edit_id ?>">
                    <input type="text" style="width: 80%;" name="name" value="<?= htmlReady($domain->getName()) ?>">
                <? else: ?>
                    <?= htmlReady($domain->getName()) ?>
                <? endif ?>
            </td>
            <td>
                <?= htmlReady($domain->getID()) ?>
            </td>
            <td>
                <?= count($domain->getUsers()) ?>
            </td>
            <td class="action">
                <a href="<?= $controller->url_for('admin/domain/edit?id=' . $domain->getID()) ?>">
                    <?= Icon::create('edit', 'clickable', ['title' => _('bearbeiten')])->asImg() ?>
                </a>
                <? if (count($domain->getUsers()) == 0): ?>
                    <?=
                    Icon::create('trash', 'clickable', tooltip2(_('löschen')))
                        ->asInput(
                            [
                                'formaction'   => $controller->url_for('admin/domain/delete?id=' . $domain->getID()),
                                'data-confirm' => _('Wollen Sie die Nutzerdomäne wirklich löschen?')
                            ]
                        )
                    ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>

