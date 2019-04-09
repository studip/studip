<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<fieldset>
    <legend>
        <?= _('Suche nach Veranstaltungen')?>
    </legend>
    <label>
        <?= _('Semester:') ?>
        <?=SemesterData::GetSemesterSelector(['name' => 'sem_select', 'id' => 'sem_select', 'class' => 'user_form'], $sem_select, 'key', true)?>
    </label>
    <label>
        <?= _('Veranstaltung:') ?>
        <input type="text" name="sem_search" value="<?= htmlReady($sem_search) ?>" id="sem_search" class="user_form" required>
    </label>
</fieldset>
<footer>
    <?= Button::create(_('Suchen'),'suchen')?>
</footer>
