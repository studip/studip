<?php
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>
<h1><?= _("Studiengruppe anlegen") ?></h1>

<form action="<?= $controller->url_for('course/studygroup/create') ?>" method=post>
<?= CSRFProtection::tokenTag() ?>

<table class="blank" width="85%" cellspacing="5" cellpadding="0" border="0">
<tr>
  <td style='text-align:right; font-size:150%;'><label for="groupname"><?= _("Name:") ?></label></td>
  <td style='font-size:150%;'><input type='text' name='groupname' id='groupname' size='25' value="<?= htmlReady($this->flash['request']['groupname'])?>" style='font-size:100%'></td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'><label for="groupdescription"><?= _("Beschreibung:") ?></label></td>
  <td><textarea name='groupdescription' id='groupdescription' rows=5 cols=50 placeholder="<?= _("Hier aussagekräftige Beschreibung eingeben.") ?>"><?= htmlReady($this->flash['request']['groupdescription']) ?></textarea></td>
</tr>

<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
    <?= $this->render_partial("course/studygroup/_choose_founders", array('founders' => $flash['founders'], 'results_choose_founders' => $flash['results_choose_founders'])) ?>
<? endif; ?>
<tr>
  <td style='text-align:right; vertical-align:top;'><?= _("Inhaltselemente:") ?></td>
  <td>
    <? foreach($available_modules as $key => $name) : ?>
        <? if ($key === "documents_folder_permissions") : ?>
            <?
            // load metadata of module
            $adminModules = new AdminModules();
            $description = $adminModules->registered_modules[$key]['metadata']['description'];
            ?>
            <label>
                <input name="groupplugin[<?= $key ?>]" type="checkbox"<?= ($this->flash['request']['groupplugin'][$key]) ? 'checked="checked"' : '' ?>>
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
                    <input name="groupplugin[<?= $module ?>]" type="checkbox"<?= ($this->flash['request']['groupplugin'][$key]) ? 'checked="checked"' : '' ?>>
                    <?= htmlReady($name) ?>
                    <?= isset($info['description']) ? tooltipIcon(kill_format($info['description'])) : "" ?>
                </label><br>
            <? endif ?>
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
                <input name="groupplugin[<?= $key ?>]" type="checkbox" <?= ($this->flash['request']['groupplugin'][$key]) ? 'checked="checked"' : '' ?>> <?= htmlReady($name) ?>
                <?= isset($info['description']) ? tooltipIcon(kill_format($info['description'])) : "" ?>
            </label><br>
        <? endif ?>
    <? endforeach; ?>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
</tr>

<tr>
  <td style='text-align:right;'><label for="groupaccess"><?= _("Zugang:") ?></label></td>
  <td>
      <select name="groupaccess" id="groupaccess">
         <option <?= ($groupaccess == 'all') ? 'selected="selected"':'' ?> value="all"><?= _("Offen für alle") ?></option>
         <option <?= ($groupaccess == 'invite') ? 'selected="selected"':'' ?> value="invite"><?= _("Auf Anfrage") ?></option>
         <? if (Config::get()->STUDYGROUPS_INVISIBLE_ALLOWED): ?>
             <option <?= ($groupaccess == 'invisible') ? 'selected="selected"':'' ?> value="invisible"><?= _('Unsichtbar') ?></option>
         <? endif; ?>
      </select>
  </td>
</tr>

<tr>
  <td style='text-align:right;'></td>
  <td>&nbsp;</td>
</tr>

<tr>
  <td style='text-align:right; vertical-align:top;'><p><?= _("Nutzungsbedingungen:") ?></p></td>
  <td>
    <? if ($GLOBALS['perm']->have_perm('admin')) : ?>
    <p>
      <b><?= _("Ich habe die eingetragenen GründerInnen darüber informiert, dass in Ihrem Namen eine Studiengruppe angelegt wird und versichere, dass Sie mit folgenden Nutzungsbedingungen einverstandenen sind:") ?></b>
    </p>
    <? endif; ?>
    <p>
      <em><?= formatReady( $terms ) ?></em>
    </p>
    <p>
        <label>
            <input type=checkbox name="grouptermsofuse_ok" data-activates=".button.accept">
            <?= _("Einverstanden") ?>
        </label>
    </p>
  </td>
</tr>


<tr>
  <td></td>
  <td>
    <?= Button::createAccept(_('Speichern'), array('title' => _("Studiengruppe anlegen"))); ?>
    <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL("dispatch.php/studygroup/browse")); ?>
  </td>
</tr>

</table>
</form>
