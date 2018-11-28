<form action="<?= URLHelper::getLink("dispatch.php/questionnaire/bulkdelete", $range_type ? ['range_type' => $range_type, 'range_id' => Context::getId()]: []) ?>" method="post">
    <table class="default" id="questionnaire_overview">
        <thead>
            <tr>
                <th width="20"><input type="checkbox" data-proxyfor="#questionnaire_overview > tbody input[type=checkbox]"></th>
                <th><?= _('Fragebogen') ?></th>
                <th><?= _('Startet') ?></th>
                <th><?= _('Endet') ?></th>
                <th><?= _('Eingebunden') ?></th>
                <th><?= _('Teilnehmende') ?></th>
                <th class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <? if (count($questionnaires)) : ?>
            <? foreach ($questionnaires as $questionnaire) : ?>
                <?= $this->render_partial('questionnaire/_overview_questionnaire.php', compact('questionnaire')) ?>
            <? endforeach ?>
            <? else : ?>
                <tr class="noquestionnaires">
                    <td colspan="7" style="text-align: center">
                        <?= _('Sie haben noch keine Fragebögen erstellt.') ?>
                    </td>
                </tr>
            <? endif ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="100">
                    <?= \Studip\LinkButton::create(_("Löschen"), "delete", ['onclick' => "STUDIP.Dialog.confirm('"._("Wirklich löschen?")."', function () { jQuery('#questionnaire_overview').closest('form').submit(); }); return false;"]) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
<?php
$actions = new ActionsWidget();
$actions->addLink(
    _('Fragebogen erstellen'),
    $controller->url_for('questionnaire/edit', $range_type ? ['range_type' => $range_type, 'range_id' => Context::getId()]: []),
    Icon::create('add', 'clickable'),
    ['data-dialog' => '']
);
Sidebar::Get()->addWidget($actions);
