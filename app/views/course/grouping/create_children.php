<form class="default" action="<?= $controller->url_for('course/wizard/step') ?>" method="post" data-dialog>
    <fieldset>
        <legend>
            <?= _('Anlegen von mehreren Unterveranstaltungen') ?>
        </legend>

        <label>
            <span class="required">
                <?= _('Anzahl anzulegender Veranstaltungen') ?>
            </span>
            <input type="number" name="batchcreate[number]" value="5" min="1" required>
        </label>

        <section>
            <span class="required">
                <?= _('Nummerierung/Kennzeichnung anhängen an') ?>
            </span>
            <label>
                <input type="radio" name="batchcreate[add_number_to]" value="name" required>
                <?= _('Name der Veranstaltung') ?>
            </label>
            <label>
                <input type="radio" name="batchcreate[add_number_to]" value="number" required>
                <?= _('Veranstaltungsnummer') ?>
            </label>
        </section>

        <section>
            <span class="required">
                <?= _('Nummerierung/Kennzeichnung durch') ?>
            </span>
            <label>
                <input type="radio" name="batchcreate[numbering]" value="number" required>
                <?= _('Zahlen (1, 2, 3, ...)') ?>
            </label>
            <label>
                <input type="radio" name="batchcreate[numbering]" value="letters" required>
                <?= _('Buchstaben (A, B, C, ...)') ?>
            </label>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <input type="hidden" name="batchcreate[parent]" value="<?= $course->id ?>">
        <?= Studip\Button::createAccept(_('Weiter zum Anlegeassistenten'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for('course/grouping/children')
        ) ?>
    </footer>
</form>
