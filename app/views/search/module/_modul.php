<tbody class="<?= $modul_id == $modul->id ? 'not-collapsed' : 'collapsed' ?>">
    <tr class="table-header header-row" id="modul_<?= htmlReady($modul->id) ?>">
        <td style="vertical-align: middle; text-align: center;">
            <a data-dialog title="<?= htmlReady($modul->getDisplayName(ModuleManagementModel::DISPLAY_CODE | ModuleManagementModel::DISPLAY_SEMESTER)) . ' (' . _('Vollständige Modulbeschreibung') . ')' ?>" href="<?= $controller->link_for('shared/modul/description/' . $modul->id) ?>">
                <?= Icon::create('log')->asImg(['title' => _('Vollständige Modulbeschreibung')]) ?>
            </a>
        </td>
    <? if (count($modul->getAssignedCoursesBySemester($selected_semester->id, $GLOBALS['user']->id))) : ?>
        <td class="toggle-indicator">
            <a class="mvv-search-modules-row-link mvv-load-in-new-row" href="<?= $controller->link_for("/details/{$modul->id}/#{$modul->id}") ?>">
                <?= htmlReady($modul->getDisplayName(ModuleManagementModel::DISPLAY_CODE)) ?>
            </a>
        </td>
    <? else : ?>
        <td class="mvv-search-modules-row">
            <?= htmlReady($modul->getDisplayName(ModuleManagementModel::DISPLAY_CODE)) ?>
        </td>
    <? endif; ?>
        <td class="dont-hide">
            <?= htmlReady($modul->getDisplaySemesterValidity()) ?>
        </td>
        <td class="dont-hide">
        <? if ($modul->responsible_institute->institute) : ?>
            <?=  htmlReady($modul->responsible_institute->institute->getDisplayName()); ?>
        <? endif; ?>
        </td>
    </tr>
<? if ($details_id == $modul->id): ?>
    <?= $this->render_partial('search/module/details') ?>
<? endif; ?>
</tbody>
