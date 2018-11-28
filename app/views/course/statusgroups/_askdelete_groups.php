<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_delete_groups') ?>" method="post">
    <header>
        <h1>
            <?= ngettext('Soll die folgende Gruppe wirklich gelöscht werden?',
                'Sollen die folgenden Gruppen wirklich gelöscht werden?', count($groups)) ?>
        </h1>
        <ul>
            <?php foreach ($groups as $g) : ?>
            <li>
                <input type="hidden" name="groups[]" value="<?= $g->id ?>">
                <?= htmlReady($g->name) ?>
            </li>
            <?php endforeach ?>
        </ul>
    </header>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Löschen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
