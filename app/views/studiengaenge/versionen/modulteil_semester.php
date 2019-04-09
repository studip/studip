<? use Studip\Button, Studip\LinkButton; ?>
<? $perm_abschnitt = MvvPerm::getFieldPermModulteil_abschnitte($abschnitt_modul->abschnitt); ?>
<h3>
    <?= _('Zuordnung der Modulteile zu Fachsemestern') ?>
</h3>
<form class="default" data-dialog="" action="<?= $controller->url_for('/modulteil_semester', $abschnitt_modul->id, $modulteil->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Belegung in folgenden Fachsemestern') ?></legend>
        <? for ($i = 1; $i <= $GLOBALS['MVV_MODULTEIL_FACHSEMESTER']; $i++) : ?>
        <? $fachsemester = $abschnitt_modul->getFachsemester($modulteil->id, $i) ?>
        <? $perm = $fachsemester ? MvvPerm::get($fachsemester) : null ?>
        <div class="mvv-fachsemester" style="flex:1;">
            <label>
                <? if ($fachsemester) : ?>
                    <? if ($perm->haveFieldPerm('fachsemester')): ?>
                        <input type="checkbox" name="fachsemester[<?= $i ?>]" value="1"<?= ($fachsemester ? ' checked' : '') ?> style="vertical-align: middle;">
                    <? else : ?>
                        <input type="hidden" name="fachsemester[<?= $i ?>]" value="1">
                    <? endif; ?>
                     <? printf(_('%s. Fachsemester'), $i) ?>
                <? else : ?>
                    <? if ($perm_abschnitt > MvvPerm::PERM_WRITE): ?>
                        <input type="checkbox" name="fachsemester[<?= $i ?>]" value="1"<?= ($fachsemester ? ' checked' : '') ?> style="vertical-align: middle;">
                        <? printf(_('%s. Fachsemester'), $i) ?>
                    <? endif; ?>
                <? endif; ?>
            </label>
            <label style="<?= (($fachsemester || !Request::isXhr()) ? 'display: inline;' : 'display: none;') ?>">
                <?= _('Status:') ?>
                <? if ($fachsemester && !$perm->haveFieldPerm('differenzierung')): ?>
                    <?= $GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'][$fachsemester->differenzierung]['name'] ?>
                    <input type="hidden" name="status[<?= $i ?>]" value="<?= $fachsemester->differenzierung ?>">
                <? else: ?>
                        <select name="status[<?= $i ?>]">
                            <option value=""><?= _('-- bitte wählen --') ?></option>
                            <? foreach ($GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'] as $status_key => $status) : ?>
                            <? if ($status['visible']) : ?>
                            <option value="<?= $status_key ?>"<?= ($fachsemester && $fachsemester->differenzierung == $status_key ? ' selected' : '') ?>><?= $status['name'] ?></option>
                            <? endif; ?>
                            <? endforeach; ?>
                        </select>


                <? endif; ?>
            </label>
        </div>
        <? endfor; ?>
    </fieldset>
    <div data-dialog-button >
        <? if ($perm_abschnitt >= MvvPerm::PERM_WRITE) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('/modulteile', $abschnitt_modul->abschnitt->id), ['title' => _('zurück zur Übersicht')]) ?>
    </div>
</form>
<script>
    jQuery('.mvv-fachsemester input').on('change', function() {
        jQuery(this).closest('label').next('label').fadeToggle();
    });
</script>