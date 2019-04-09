<form class="default collapsable" action="<?= $controller->url_for('course/statusgroups/save', $group->id) ?>" method="post" data-secure>
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Einstellungen') ?>
        </legend>

        <label for="name" class="required">
            <?= _('Name') ?>
        </label>
        <input type="text" name="name" size="75" maxlength="255" value="<?= htmlReady($group->name) ?>" required>

        <label for="size">
            <?= _('Gruppengröße') ?>
        </label>
        <input type="number" name="size" value="<?= intval($group->size) ?>" min="0">

        <?php if ($group->isNew() || !$group->hasFolder()) : ?>
            <label>
                <input type="checkbox" name="makefolder" value="1">
                <?= _('Dateiordner anlegen') ?>
            </label>
        <?php elseif ($group->hasFolder()) : ?>
            <label>
                <input type="checkbox" checked disabled>
                <?= _('Zu dieser Gruppe gehört ein Dateiordner.')  ?>
            </label>
        <?php endif ?>

        <label>
            <input type="checkbox" name="selfassign" value="1"<?= $group->selfassign ? ' checked' : '' ?>>
            <?= _('Selbsteintrag erlaubt') ?>
        </label>

        <label>
            <input type="checkbox" name="exclusive" value="1"<?= $group->selfassign == 2 ? ' checked' : '' ?>>
            <?= _('Exklusiver Selbsteintrag (in nur eine Gruppe)') ?>
        </label>

        <label class="col-3">
            <?= _('Selbsteintrag erlaubt ab') ?>
            <input class="size-s" type="text" size="20" name="selfassign_start" id="selfassign_start" value="<?= $group->selfassign_start ?
                date('d.m.Y H:i', $group->selfassign_start) : '' ?>" data-datetime-picker>
        </label>

        <label class="col-3">
            <?= _('Selbsteintrag erlaubt bis') ?>
            <input class="size-s" type="text" size="20" name="selfassign_end" value="<?= $group->selfassign_end ?
                date('d.m.Y H:i', $group->selfassign_end) : '' ?>" data-datetime-picker='{">":"#selfassign_start"}'>
        </label>
    </fieldset>

    <h1>
        <?= _('Zuordnung von Terminen') ?>
    </h1>

    <?php if ($cycles || $singledates) : ?>
        <?php if ($cycles) : ?>
            <fieldset class="collapsed">
                <legend><?= _('Regelmäßige Zeiten') ?></legend>
                <?php foreach ($cycles as $c) : ?>
                    <article class="<?= ContentBoxHelper::classes($c->id) ?>" id="<?= $c->id ?>">
                        <header>
                            <h1>
                                <a href="<?= ContentBoxHelper::href($c->id, ['contentbox_type' => 'news']) ?>">
                                    <?= htmlReady($c->toString()) ?>
                                </a>
                            </h1>
                        </header>
                        <section>
                            <?php foreach ($c->dates as $d) : ?>
                                <label for="<?= $d->id ?>">
                                    <input type="checkbox" name="dates[]" value="<?= $d->id ?>" id="<?= $d->id?>"
                                    <?= $group->dates->find($d->id) ? ' checked' : '' ?>>
                                    <?= htmlReady($d->getFullname()) ?>
                                </label>
                            <?php endforeach ?>
                        </section>
                    </article>
                <?php endforeach ?>
            </fieldset>
        <?php endif ?>
        <?php if ($singledates) : ?>
            <fieldset class="collapsed">
                <legend>
                    <?= _('Einzeltermine') ?>
                </legend>
                <?php foreach ($singledates as $s) : ?>
                    <label for="<?= $s->id ?>">
                        <input type="checkbox" name="dates[]" value="<?= $s->id ?>" id="<?= $s->id?>"
                            <?= $group->dates->find($s->id) ? ' checked' : '' ?>>
                        <?= htmlReady($s->getFullname()) ?>
                    </label>
                <?php endforeach ?>
            </fieldset>
        <?php endif ?>
    <?php else : ?>
        <?= MessageBox::info(_('Diese Veranstaltung hat keine Termine.')); ?>
    <?php endif ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
<script type="text/javascript">
    //<!--
    STUDIP.Statusgroups.initInputs();
    //-->
</script>
