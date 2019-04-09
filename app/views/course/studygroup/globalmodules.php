<?php
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;


/* * * * * * * * * * * *
 * * * O U T P U T * * *
 * * * * * * * * * * * */

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>
<? if (!$configured): ?>
    <?= MessageBox::error(_('Keine Veranstaltungsart für Studiengruppen gefunden'),
        [sprintf(_('Die Standardkonfiguration für Studiengruppen in der Datei <b>%s</b> fehlt oder ist unvollständig.'),
                'config.inc.php')]) ?>
<? endif ?>
<? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
    <?= MessageBox::info( _("Die Studiengruppen sind derzeit <b>nicht</b> aktiviert.")
            . '<br>'. _("Zum Aktivieren füllen Sie bitte das Formular aus und klicken Sie auf \"Speichern\".")); ?>
<? else: ?>
    <? if ($can_deactivate) : ?>
        <?= MessageBox::info( _("Die Studiengruppen sind aktiviert.")) ?>
        <form action="<?= $controller->url_for('course/studygroup/deactivate') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::create(_("Deaktivieren"), 'deaktivieren') ?>
        </form>
    <? else: ?>
        <?= MessageBox::info(_("Sie können die Studiengruppen nicht deaktivieren, solange noch welche in Stud.IP vorhanden sind!")) ?>
    <? endif; ?>
    <br>
<? endif;?>
<form class="default" action="<?= $controller->url_for('course/studygroup/savemodules') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <!-- Title -->
    <fieldset>
        <legend><?= _("Einrichtungszuordnung") ?></legend>
        <label>
            <?= _("Alle Studiengruppen werden folgender Einrichtung zugeordnet:") ?><br>
            <select name="institute" class="nested-select">
            <? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
                <option value="" class="is-placeholder">
                    <?= _('-- Bitte auswählen --') ?>
                </option>
            <? endif ?>
            <? foreach ($institutes as $fak_id => $faculty) : ?>
                <option value="<?= $fak_id ?>" class="nested-item-header"
                    <?= ($fak_id == $default_inst) ? 'selected="selected"' : ''?>>
                    <?= htmlReady(my_substr($faculty['name'], 0, 60)) ?>
                </option>
                <? foreach ($faculty['childs'] as $inst_id => $inst_name) : ?>
                <option value="<?= $inst_id ?>" class="nested-item"
                    <?= ($inst_id == $default_inst) ? 'selected="selected"' : ''?>>
                    <?= htmlReady(my_substr($inst_name, 0, 60)) ?>
                </option>
                <? endforeach; ?>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <!-- Title -->
    <fieldset>
        <legend><?= _("Nutzungsbedingugen") ?></legend>
        <label>
            <?= _("Geben Sie hier Nutzungsbedingungen für die Studiengruppen ein. ".
                    "Diese müssen akzeptiert werden, bevor eine Studiengruppe angelegt werden kann.") ?>
            <textarea name="terms" style="width: 90%" rows="10" style='align:middle;'><?= htmlReady($terms) ?></textarea>
        </label>
    </fieldset>
    <footer>
        <?= Button::createAccept(_("Speichern"), 'speichern') ?>
    </footer>
</form>
