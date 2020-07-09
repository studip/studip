<? if ($resources): ?>
<table class="default">
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Eigenschaften') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($resources as $resource): ?>
        <tr>
            <td>
                <a href="<?= $resource->getActionLink('show') ?>">
                    <?= htmlReady($resource->name) ?>
                </a>
            </td>
            <td><?= htmlReady($resource->description) ?></td>
            <td>
                <? if ($resource->properties): ?>
                <ul>
                    <? foreach ($resource->properties as $property): ?>
                        <? if ($property->definition): ?>
                            <li><?= htmlReady($property->getFullName()) ?></li>
                        <? endif ?>
                    <? endforeach ?>
                </ul>
                <? endif ?>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
<? else: ?>
<?= MessageBox::info(_('Es gibt keine Ressourcen in der gewählten Ressourcenkategorie!')) ?>
<? endif ?>
