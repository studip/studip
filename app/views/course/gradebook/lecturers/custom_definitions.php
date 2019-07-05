<form class="default" action="<?= $controller->link_for('course/gradebook/lecturers/store_grades') ?>" method="POST">
    <?= CSRFProtection::tokenTag()?>
    <div style="overflow-x:auto;">
        <table class="default gradebook-lecturer-custom-definitions">
            <caption>
                <?= _('Noten manuell erfassen') ?>
            </caption>

            <thead>
                <tr class="tablesorter-ignoreRow">
                    <th><?= _('Name') ?></th>
                    <? if (count($customDefinitions)) { ?>
                        <? foreach ($customDefinitions as $definition) { ?>
                            <th>
                                <?= htmlReady($definition->name) ?>
                            </th>
                        <? } ?>
                    <? } else { ?>
                        <th>&nbsp;</th>
                    <? } ?>
                </tr>
            </thead>

            <tbody>

                <? foreach ($students as $index => $student) { ?>
                    <tr>
                        <td class="gradebook-student-name" data-sort-value="<?= $studentName = htmlReady($student->getFullName('no_title_rev')) ?>">
                            <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $student->username]) ?>">
                                <?= $studentName ?>
                            </a>
                        </td>
                        <? if (count($customDefinitions)) { ?>
                            <? foreach ($customDefinitions as $definition) { ?>
                                <td class="gradebook-grade-input">
                                    <? $instance = $controller->getInstanceForUser($definition, $student) ?>
                                    <? $rawgrade = $instance ? $instance->rawgrade : 0 ?>
                                    <label class="undecorated">
                                        <input type="number"
                                               name="grades[<?= htmlReady($student->id) ?>][<?= htmlReady($definition->id) ?>]"
                                               value="<?= $controller->formatAsPercent($rawgrade) ?>"
                                               min="0"> %
                                    </label>

                                </td>
                            <? } ?>
                    <? } elseif ($index === 0) { ?>
                                <td rowspan="<?= count($students) ?>" class="gradebook-lecturer-blank-slate">
                                    <p><?= _('Es sind keine manuellen Leistungen definiert.') ?></p>
                                </td>
                        <? } ?>
                    </tr>
                <? } ?>

            </tbody>

            <? if (count($customDefinitions) && count($students)) { ?>
                <? $tfootColspan = 1 + count($customDefinitions) ?>
                <tfoot class="gradebook-lecturer-custom-definitions-actions">
                    <tr>
                        <td colspan="<?= $tfootColspan ?>">
                            <?= \Studip\Button::createAccept(_('Speichern')) ?>
                            <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/gradebook/lecturers')) ?>
                        </td>
                    </tr>
                </tfoot>
            <? } ?>
        </table>
    </div>
</form>

<? if (!count($students)) { ?>
    <?= \MessageBox::info(_('Es gibt noch keine Teilnehmer.')) ?>
<? } ?>
