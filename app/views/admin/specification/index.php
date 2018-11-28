<form method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Verwaltung von Zusatzangaben') ?>
        </caption>
        <colgroup>
            <col width="45%">
            <col width="45%">
            <col width="10%">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Name') ?></th>
                <th><?= _('Beschreibung') ?></th>
                <th><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? if (!empty($allrules)): ?>
            <? foreach ($allrules as $index => $rule) : ?>
                <tr>
                    <td>
                        <?= htmlReady($rule['name']) ?>
                    </td>
                    <td>
                        <?= htmlReady($rule['description']) ?>
                    </td>
                    <td class="actions">
                        <a href="<?=$controller->url_for('admin/specification/edit/'.$rule['lock_id']) ?>" style="vertical-align: bottom">
                            <?= Icon::create('edit', 'clickable', ['title' => _('Regel bearbeiten')])->asImg() ?>
                        </a>
                        <?=Icon::create('trash', 'clickable', tooltip2(_('Regel löschen')))->asInput([
                            'formaction'   => $controller->url_for('admin/specification/delete/' . $rule['lock_id']),
                            'data-confirm' => sprintf(_('Wollen Sie die Regel "%s" wirklich löschen?'), $rule['name']),
                            'style'        => 'vertical-align: bottom'
                        ])?>
                    </td>
                </tr>
            <? endforeach ?>
        <? else : ?>
            <tr>
                <td colspan="3" style="text-align: center">
                    <?= _('Es wurden noch keine Zusatzangaben definiert.') ?>
                </td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
</form>
<?

$sidebar = Sidebar::Get();
$sidebar->setImage('sidebar/admin-sidebar.png');
$sidebar->setTitle(_('Zusatzangaben'));
$actions = new ActionsWidget();
$actions->addLink(_('Neue Regel anlegen'), $controller->url_for('admin/specification/edit'), Icon::create('add', 'clickable'));
$sidebar->addWidget($actions);

?>
