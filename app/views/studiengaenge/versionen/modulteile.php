<td colspan="2">
    <table id="modulteile_<?= $modul->id ?>" class="default">
        <colgroup>
            <col>
            <col style="width: 1%;">
        </colgroup>
        <? foreach ($modul->modulteile as $modulteil) : ?>
        <tbody id="<?= $modulteil->id ?>">
        <tr id="modulteil_<?= $modulteil->id ?>">
            <td><?= htmlReady($modulteil->getDisplayName()) ?></td>
            <td class="actions">
                <? $perm = MvvPerm::get($assignment->abschnitt) ?>
                <? if ($perm->haveFieldPerm('modulteil_abschnitte')) : ?>
                <a data-dialog="" href="<?= $controller->url_for('/modulteil_semester', $assignment->id, $modulteil->id) ?>">
                    <?= Icon::create('edit', Icon::ROLE_CLICKABLE , ['title' => _('Semesterzuordnung bearbeiten')])->asImg(); ?>
                </a>
                <? endif; ?>
            </td>
        </tr>
        </tbody>
        <? endforeach; ?>
    </table>
</td>