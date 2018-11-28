<? if (!$has_enabled) : ?>
    <?= MessageBox::info(_('Es gibt keine aktiven Schritte für den Anlegeassistenten!')) ?>
<? endif ?>
<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Vorhandene Schritte im Anlegeassistenten für Veranstaltungen') ?>
            <span class="actions">
                <a href="<?= $controller->url_for('admin/coursewizardsteps/edit') ?>" data-dialog="size=auto">
                    <?= Icon::create('add')->asImg(tooltip2(_('Neuen Schritt hinzufügen'))) ?>
                </a>
            </span>
        </caption>
        <colgroup>
            <col style="width: 30%">
            <col style="width: 30%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 10%">
        </colgroup>
        <thead>
            <th><?= _('Name') ?></th>
            <th><?= _('PHP-Klasse') ?></th>
            <th><?= _('Nummer') ?></th>
            <th><?= _('aktiv?') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </thead>
        <tbody>
        <? foreach ($steps as $step) : ?>
            <tr id="wizard-step-<?= $step->id ?>">
                <td><?= htmlReady($step->name) ?></td>
                <td><?= htmlReady($step->classname) ?></td>
                <td><?= $step->number ?></td>
                <td>
                    <a href="<?= $controller->link_for("admin/coursewizardsteps/toggle_enabled/{$step->id}") ?>" data-behaviour="ajax-toggle">
                    <? if ($step->enabled): ?>
                        <?= Icon::create('checkbox-checked') ?>
                    <? else: ?>
                        <?= Icon::create('checkbox-unchecked') ?>
                    <? endif; ?>
                    </a>
                </td>
                <td class="actions">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? $actionMenu->addLink(
                        $controller->url_for("admin/coursewizardsteps/edit/{$step->id}"),
                        _('Schritt bearbeiten'),
                        Icon::create('edit'),
                        ['data-dialog' => 'size=auto']
                    ) ?>

                    <? $actionMenu->addButton(
                            'delete_step',
                            _('Schritt löschen'),
                            Icon::create('trash', Icon::ROLE_CLICKABLE, [
                                'title'        => _('Schritt löschen'),
                                'formaction'   => $controller->url_for("admin/coursewizardsteps/delete/{$step->id}"),
                                'data-confirm' => sprintf(
                                    _('Soll der Eintrag "%s" wirklich gelöscht werden?'),
                                    htmlReady($step->name)
                                ),
                            ])
                    ) ?>
                    <?= $actionMenu->render() ?>
                </td>
            </tr>
        <? endforeach; ?>

        <? if (!$steps): ?>
            <tr>
                <td colspan="5" style="text-align: center">
                    <?= _('Es sind keine Schritte für den Veranstaltungsanlegeassistenten registriert!') ?>
                </td>
            </tr>
        <? endif ?>
        </tbody>
    </table>
</form>
