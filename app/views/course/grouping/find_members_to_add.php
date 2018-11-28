
<form class="default" action="<?= $controller->url_for('course/grouping/add_members') ?>" method="post">
    <label>
        <?= $search->render(); ?>
    </label>

    <section>
        <ul id="persons-to-add">
        </ul>
    </section>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="permission" value="<?= $permission ?>">
    <? foreach ($courses as $course) : ?>
        <input type="hidden" name="courses[]" value="<?= $course ?>" required>
    <? endforeach ?>
        <?= Studip\Button::createAccept(_('Personen hinzufügen'), 'add') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
