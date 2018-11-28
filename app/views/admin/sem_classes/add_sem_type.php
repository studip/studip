<form action="?" method="post" class="default" id="add_sem_class_window" data-dialog>
    <fieldset>
        <legend>
            <?= _('Veranstaltungskategorie anlegen') ?>
        </legend>

        <label>
            <span class="required">
                <?= _("Name") ?>
            </span>
            <input type="text" name="add_name" id="add_name" required>
        </label>

        <label>
            <?= _("Attribute kopieren von Veranstaltungskategorie") ?>
            <select name="add_like" id="add_like">
                <option value=""><?= _("keine") ?></option>
                <? foreach ($GLOBALS['SEM_CLASS'] as $id => $sem_class) : ?>
                <option value="<?= $id ?>"><?= htmlReady($sem_class['name']) ?></option>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::create(_("Erstellen")) ?>
    </footer>
</form>
