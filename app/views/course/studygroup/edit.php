<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>

<form action="<?= $controller->url_for('course/studygroup/update/', ['cid' => $sem_id]) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _("Studiengruppe bearbeiten") ?>
        </legend>

        <input type='submit' class="invisible" name="<?=_("Änderungen übernehmen")?>" aria-hidden="true">
        <label>
            <span class="required"><?= _('Name') ?></span>
            <input type='text' name='groupname' size='25' value='<?= htmlReady($sem->getName()) ?>'>
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name='groupdescription' rows=5 cols=50><?= htmlReady($sem->description) ?></textarea>
        </label>

        <? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id)) : ?>
            <?= $this->render_partial("course/studygroup/_replace_founder", ['tutors' => $tutors]) ?>
        <? endif; ?>

        <section>
            <?= _('Inhaltselemente') ?>
            <? foreach($available_modules as $key => $name) : ?>
                <? if ($key === "documents_folder_permissions") : ?>
                    <?
                    // load metadata of module
                    $adminModules = new AdminModules();
                    $description = $adminModules->registered_modules[$key]['metadata']['description'];
                    ?>
                    <label>
                        <input name="groupplugin[<?= $key ?>]" type="checkbox" <?= ($modules->getStatus($key, $sem_id, 'sem')) ? 'checked="checked"' : '' ?>>
                        <?= htmlReady($name) ?>
                        <?= isset($description) ? tooltipIcon(kill_format($description)) : "" ?>
                    </label><br>
                <? else : ?>
                    <? $module = $sem_class->getSlotModule($key) ?>
                    <? if ($module && $sem_class->isModuleAllowed($module) && !$sem_class->isSlotMandatory($key)) : ?>
                        <?
                        // load metadata of module
                        $studip_module = $sem_class->getModule($key);
                        $info = $studip_module->getMetadata();
                        ?>
                        <label>
                            <input name="groupplugin[<?= $module ?>]" type="checkbox" <?= ($modules->getStatus($key, $sem_id, 'sem')) ? 'checked="checked"' : '' ?>>
                            <?= htmlReady($name) ?>
                            <? $studip_module = $sem_class->getModule($module);
                            if (is_a($studip_module, "StandardPlugin")) : ?>
                                (<?= htmlReady($studip_module->getPluginName()) ?>)
                            <? endif ?>
                            <?= isset($info['description']) ? tooltipIcon(kill_format($info['description'])) : "" ?>
                        </label>
                    <? endif;?>
                <? endif ?>
            <? endforeach; ?>

            <? foreach($available_plugins as $key => $name) : ?>
                <? if ($sem_class->isModuleAllowed($key) && !$sem_class->isModuleMandatory($key) && !$sem_class->isSlotModule($key)) : ?>
                    <?
                    // load metadata of plugin
                    $plugin = $sem_class->getModule($key);
                    $info = $plugin->getMetadata();
                    ?>
                    <label>
                        <input name="groupplugin[<?= $key ?>]" type="checkbox" <?= ($enabled_plugins[$key]) ? 'checked="checked"' : '' ?>>
                        <?= htmlReady($name) ?>
                        <?= isset($info['description']) ? tooltipIcon(kill_format($info['description'])) : "" ?>
                    </label>
                <? endif ?>
            <? endforeach; ?>
        </section>

        <label>
            <?= _('Zugang') ?>
            <select name="groupaccess">
                <option <?= ($sem->admission_prelim == 0) ? 'selected="selected"':'' ?> value="all"><?= _('Offen für alle') ?></option>
                <option <?= ($sem->admission_prelim == 1) ? 'selected="selected"':'' ?> value="invite"><?= _('Auf Anfrage') ?></option>
                <? if(Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED || $sem->visible == 0): ?>
                    <option <?= ($sem->visible == 0) ? 'selected="selected"':'' ?> value="invisible" <?= Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED ? '' : 'disabled="true"' ?>><?= _('Unsichtbar') ?></option>
                <? endif; ?>
            </select>
        </label>

    </fieldset>

    <footer>
        <?= Button::createAccept(_('Übernehmen'), ['title' => _("Änderungen übernehmen")]); ?>
        <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('seminar_main.php')); ?>
    </footer>
</form>
