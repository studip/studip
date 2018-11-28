<? if ($parent) : ?>
<form class="default" method="post" action="<?= $controller->url_for('course/grouping/unassign_parent') ?>">
    <fieldset>
        <legend><?= _('Veranstaltung zuordnen') ?></legend>
        <section>
            <p>
                <?= sprintf(
                    _('Diese Veranstaltung gehört zur Hauptveranstaltung %s%s%s.'),
                    sprintf(
                        '<a href="%s" title="%s">',
                        $controller->link_for('course/grouping/children', ['cid' => $parent->id]),
                        htmlReady($parent->getFullname())
                    ),
                    htmlReady($parent->getFullname()),
                    '</a>'
                ) ?>
            </p>
        </section>
    </fieldset>

    <footer>
        <?= Studip\Button::createCancel(_('Zuordnung aufheben'), 'unassign') ?>
    </footer>
</form>
<? else : ?>
<form class="default" method="post" action="<?= $controller->url_for('course/grouping/assign_parent') ?>">
    <fieldset>
        <legend><?= _('Veranstaltung zuordnen') ?></legend>

        <p>
            <?= _('Diese Veranstaltung ist noch keiner Hauptveranstaltung zugeordnet.') ?>
        </p>

        <label>
            <?= _('Veranstaltung') ?>
            <?= $search->render() ?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Zuordnen'), 'assign') ?>
    </footer>
</form>
<? endif ?>
