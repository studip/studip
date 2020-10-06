<fieldset>
    <legend>
        <?= _('Suche nach Veranstaltungen')?>
    </legend>
    <label>
        <?= _('Semester') ?>
        <?= Semester::getSemesterSelector(
            ['name' => 'sem_select', 'id' => 'sem_select', 'class' => 'user_form'],
            $sem_select, 'semester_id',
            true
        )?>
    </label>
    <label>
        <?= _('Veranstaltung') ?>
        <input type="text" name="sem_search" value="<?= htmlReady($sem_search) ?>" id="sem_search" class="user_form" required>
    </label>
</fieldset>
<footer>
    <?= Studip\Button::create(_('Suchen'),'suchen')?>
</footer>
