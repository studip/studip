<form class="default" action="<?= $controller->url_for('admin/ilias_interface/save/'.$ilias_index) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="ilias_content_settings" size="50" maxlength="255" value="1">
    <label>
        <span class="required"><?= _('Wurzelkategorie für Stud.IP-Daten') ?></span>
        <? if ($ilias_config['root_category']) : ?>
            <div><?=htmlReady($ilias_config['root_category_name']).' (ID '.htmlReady($ilias_config['root_category']).')'?></div>
        <? else : ?>
            <input type="text" name="ilias_root_category_name" size="50" maxlength="255" value="<?= htmlReady($ilias_config['root_category_name']) ?>" required>
        <? endif ?>
    </label>
    <? if ($ilias_config['user_data_category']) : ?>
    <label>
        <span class="required"><?= _('Kategorie mit User-Daten') ?></span>
        <div><?=htmlReady(_('User_daten')).' (ID '.htmlReady($ilias_config['user_data_category']).')'?></div>
    </label>
    <? endif ?>
    <label>
        <input type="checkbox" name="ilias_category_create_on_add_module" value="1" <?= $ilias_config['category_create_on_add_module'] ? 'checked' : '' ?>>
        <span><?= _('Persönliche ILIAS-Kategorie erst erzeugen, wenn Lernobjekte angelegt werden') ?></span>
    </label>
    <label>
        <input type="checkbox" name="ilias_category_to_desktop" value="1" <?= $ilias_config['category_to_desktop'] ? 'checked' : '' ?>>
        <span><?= _('Persönliche ILIAS-Kategorie auf den Schreibtisch legen') ?></span>
    </label>
    <label>
        <span class="required"><?= _('Prefix für automatisch angelegte Usernamen') ?></span>
        <? if ($ilias_config['is_active']) : ?>
            <div><?=$ilias_config['user_prefix'] ? htmlReady($ilias_config['user_prefix']) : _('Kein Präfix')?></div>
        <? else : ?>
            <input type="text" name="ilias_user_prefix" size="50" maxlength="255" value="<?= htmlReady($ilias_config['user_prefix']) ?>">
        <? endif ?>
    </label>
    <label>
    <span class="required"><?= _('Struktur für angelegte Kurse') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_cat_semester" value="none" required <?=$ilias_config['cat_semester'] == "none" ? ' checked' : ''?>>
        <span><?= _('Keine Semester-Kategorien') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_cat_semester" value="outer" required <?=$ilias_config['cat_semester'] == "outer" ? ' checked' : ''?>>
        <span><?= _('Semester als Kategorie oberhalb der Einrichtung') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_cat_semester" value="inner" required <?=$ilias_config['cat_semester'] == "inner" ? ' checked' : ''?>>
        <span><?= _('Semester als Kategorie innerhalb der Einrichtung') ?></span>
    </label>
    <label>
    <span class="required"><?= _('Kurstitel') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_course_semester" value="old" required <?=$ilias_config['course_semester'] == "old" ? ' checked' : ''?>>
        <span><?= _('Stud.IP-Veranstaltung "Veranstaltungsname"') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_course_semester" value="old_bracket" required <?=$ilias_config['course_semester'] == "old_bracket" ? ' checked' : ''?>>
        <span><?= _('Stud.IP-Veranstaltung "Veranstaltungsname" (Semester)') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_course_semester" value="none" required <?=$ilias_config['course_semester'] == "none" ? ' checked' : ''?>>
        <span><?= _('Veranstaltungsname') ?></span>
    </label>
    <label>
        <input type="radio" name="ilias_course_semester" value="bracket" required <?=$ilias_config['course_semester'] == "bracket" ? ' checked' : ''?>>
        <span><?= _('Veranstaltungsname (Semester)') ?></span>
    </label>
    <label>
        <input type="checkbox" name="ilias_course_veranstaltungsnummer" value="1" <?= $ilias_config['course_veranstaltungsnummer'] ? 'checked' : '' ?>>
        <span><?= _('Stud.IP-Veranstaltungsnummer im ILIAS-Kurstitel anzeigen') ?></span>
    </label>
    <label>
    <span>  <?= _('Module') ?></span>
    </label>
    <label>
        <? foreach ($modules_available as $module_index => $module_name) : ?>
        <label>
            <input type="checkbox" name="ilias_modules_<?=$module_index?>" value="1" <?=$ilias_config['modules'][$module_index] ? ' checked':''?>>
            <?=htmlReady($module_name)?>
        </label>
        <? endforeach ?>
    </label>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>