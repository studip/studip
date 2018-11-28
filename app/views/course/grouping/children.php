<form class="default" method="post" action="<?= $controller->url_for('course/grouping/assign_child') ?>">
    <fieldset>
        <legend>
            <?= _('Bereits zugeordnet') ?>
        </legend>
        <section>
        <? if (count($children) > 0) : ?>
            <ul>
            <? foreach ($children as $child) : ?>
                <li>
                    <a href="<?= $controller->url_for('course/management', ['cid' => $child->id]) ?>">
                        <?= htmlReady($child->getFullname()) ?>
                    </a>
                    <a href="<?= $controller->url_for('course/grouping/unassign_child', $child->id) ?>"
                       data-confirm="<?= _('Wollen Sie die Zuordnung dieser Unterveranstaltung wirklich entfernen?')?>">
                        <?= Icon::create('trash', 'clickable')->asImg() ?>
                    </a>
                </li>
            <? endforeach ?>
            </ul>
        <? else: ?>
            <p>
                <?= _('Diese Veranstaltung hat keine Unterveranstaltungen.') ?>
            </p>
        <? endif ?>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Unterveranstaltung hinzufügen') ?>
        </legend>
        <section>
            <?= $search->render() ?>
        </section>
    </fieldset>
    <footer>
        <?= Studip\Button::createAccept(_('Unterveranstaltung zuordnen'), 'assign') ?>
    </footer>
</form>
