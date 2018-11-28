<form class="default" action="<?= $controller->url_for('course/statusgroups/join') ?>" method="post">
    <section>
        <label for="target_group">
            <?= _('Welcher Gruppe möchten Sie beitreten?') ?>
            <select name="target_group">
                <?php foreach ($joinables as $g) : ?>
                    <option value="<?= $g->id ?>"><?= htmlReady($g->name) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Beitreten'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
