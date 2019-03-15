<? use Studip\Button; ?>

<h2><?= _('Ich studiere folgende Fächer und Abschlüsse:') ?></h2>

<form action="<?= $controller->url_for('settings/studies/store_sg') ?>" method="post" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default" id="select_fach_abschluss">
        <colgroup>
            <col>
            <col>
            <col>
            <col width="100px">
            <col width="100px">
        </colgroup>
        <thead class="hidden-tiny-down">
            <tr>
                <th><?= _('Fach') ?></th>
                <th><?= _('Abschluss') ?></th>
                <th id="version_label"><?= _('Versionen') ?></th>
                <th id="fachsemester_label"><?= _('Fachsemester') ?></th>
                <th style="text-align:center;" id="austragen_label">
                    <? if ($allow_change['sg']): ?>
                        <?= _('austragen') ?>
                    <? else: ?>
                        &nbsp;
                    <? endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? if (count($user->studycourses) === 0 && $allow_change['sg']): ?>
            <tr>
                <td colspan="5" style="background: inherit;">
                    <strong><?= _('Sie haben sich noch keinem Studiengang zugeordnet.') ?></strong><br>
                    <br>
                    <?= _('Tragen Sie bitte hier die Angaben aus Ihrem Studierendenausweis ein!') ?>
                </td>
            </tr>
            <? endif; ?>


            <? foreach ($user->studycourses as $usc): ?>
            <tr>
                <td data-label="<?= _('Fach') ?>"><?= htmlReady($usc->studycourse->name) ?></td>
                <td data-label="<?= _('Abschluss') ?>"><?= htmlReady($usc->degree->name) ?></td>
                <? if ($allow_change['sg']): ?>
                    <td data-label="<?= _('Version') ?>">
                            <? $versionen = StgteilVersion::findByFachAbschluss($usc->fach_id, $usc->abschluss_id); ?>
                        <? $versionen = array_filter($versionen, function ($ver) {
                            return $ver->hasPublicStatus('genehmigt');
                        }); ?>
                        <? if (count($versionen)) : ?>
                                <select name="change_version[<?= $usc->fach_id ?>][<?= $usc->abschluss_id ?>]"
                                    aria-labelledby="version_label">
                                <option value=""><?= _('-- Bitte Version auswählen --') ?></option>
                                <? foreach ($versionen as $version) : ?>
                                        <option<?= $version->getId() == $usc->version_id ? ' selected' : '' ?>
                                            value="<?= htmlReady($version->getId()) ?>">
                                        <?= htmlReady($version->getDisplayName()) ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                        <? else : ?>
                            <?= tooltipIcon(_('Keine Version in der gewählten Fach-Abschluss-Kombination verfügbar.'), true) ?>
                        <? endif; ?>
                    </td>
                <? else : ?>
                    <? $version = StgteilVersion::find($usc->version_id); ?>
                    <td>
                        <? if ($version && $version->hasPublicStatus('genehmigt')) : ?>
                            <?= htmlReady($version->getDisplayName()); ?>
                        <? endif; ?>
                    </td>
                <? endif; ?>
                <? if ($allow_change['sg']): ?>
                    <td data-label="<?= _('Fachsemester') ?>">
                        <select name="change_fachsem[<?= $usc->fach_id?>][<?= $usc->abschluss_id ?>]"
                                aria-labelledby="fachsemester_label">
                            <? for ($i = 1; $i <= 50; $i += 1): ?>
                                <option <? if ($i == $usc->semester) echo 'selected'; ?>><?= $i ?></option>
                            <? endfor; ?>
                        </select>
                    </td>
                    <td data-label="<?= _('austragen:') ?>">
                        <input type="checkbox" aria-labelledby="austragen_label"
                               name="fach_abschluss_delete[<?= $usc->fach_id ?>]"
                               value="<?= $usc->abschluss_id ?>">
                    </td>
                <? else: ?>
                    <td data-label="<?= _('Fachsemester:') ?>"><?= htmlReady($usc->semester) ?></td>
                    <td data-label="<?= _('austragen:') ?>" style="text-align: right;">
                        <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']) ?>
                    </td>
                <? endif; ?>
            </tr>
        <? endforeach; ?>

        <? if (count($user->studycourses) !== 0 && $allow_change['sg']): ?>
        <tr>
            <td colspan="<?= $modulemanagement_enabled ? '5' : '4' ?>" style="padding: 0px; text-align: right;">
                <footer>
                    <?= Button::create(_('Übernehmen'), 'store_in', ['title' => _('Änderungen übernehmen')]) ?>
                </footer>
            </td>
        </tr>
        <? endif ?>
        </tbody>
    </table>
</form>


<? if ($allow_change['sg']): ?>
<form action="<?= $controller->url_for('settings/studies/store_sg') ?>" method="post" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Fach / Abschluss hinzufügen') ?>
        </legend>


        <div>
            <?= _('Wählen Sie die Fächer, Abschlüsse und Fachsemester in der folgenden Liste aus:') ?>
        </div>
        <div class="hgroup">
            <label>
                <select name="new_studiengang" id="new_studiengang"
                        aria-label="<?= _('-- Bitte Fach auswählen --') ?>">
                    <option selected value="none"><?= _('-- Bitte Fach auswählen --') ?></option>
                    <? foreach ($faecher as $fach) : ?>
                        <?= sprintf('<option value="%s">%s</option>', $fach->id, htmlReady(my_substr($fach->name, 0, 50))); ?>
                    <? endforeach ?>
                </select>
            </label>

            <label>
                <select name="new_abschluss" id="new_abschluss"
                        aria-label="<?= _('-- Bitte Abschluss auswählen --') ?>">
                    <option selected value="none"><?= _('-- Bitte Abschluss auswählen --') ?></option>
                    <? foreach ($abschluesse as $abschluss) : ?>
                        <?= sprintf('<option value="%s">%s</option>' . "\n", $abschluss->id, htmlReady(my_substr($abschluss->name, 0, 50))); ?>
                    <? endforeach ?>
                </select>
            </label>

            <label>
                <select name="fachsem" aria-label="<?= _("Bitte Fachsemester wählen") ?>" class="size-s">
                    <? for ($i = 1; $i <= 50; $i += 1): ?>
                        <option><?= $i ?></option>
                    <? endfor; ?>
                </select>
            </label>
        </div>
    </fieldset>
    <footer>
        <?= Button::create(_('Übernehmen'), 'store_sg', ['title' => _('Änderungen übernehmen')]) ?>
    </footer>
</form>
<? else: ?>
    <?= _('Die Informationen zu Ihrem Studiengang werden vom System verwaltet, '
          . 'und können daher von Ihnen nicht geändert werden.') ?>
<? endif; ?>
