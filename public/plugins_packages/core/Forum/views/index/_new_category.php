<? if (ForumPerm::has('add_category', $seminar_id)) : ?>
<a name="create"></a>
<form action="<?= PluginEngine::getLink('coreforum/index/add_category') ?>" method="post" id="tutorAddCategory" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Neue Kategorie erstellen') ?></legend>

        <label>
            <?= _('Name der Kategorie') ?>
            <input type="text" size="50" placeholder="<?= _('Titel für neue Kategorie') ?>" name="category" required>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::create(_('Kategorie erstellen')) ?>
    </footer>
</form>
<br>
<? endif ?>
