<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_save_groups_selfassign') ?>" method="post">
    <label>
        <input type="checkbox" name="selfassign" value="1"<?= $selfassign == 1 ? ' checked' : '' ?>>
        <?= _('Selbsteintrag erlaubt') ?>
        <?= $selfassign == -1 ? '<br>(' . _('verschiedene Werte') . ')' : '' ?>
    </label>

    <label>
        <input type="checkbox" name="exclusive" value="1"<?= $exclusive == 1 ? ' checked' : '' ?>>
        <?= _('Exklusiver Selbsteintrag (in nur eine Gruppe)') ?>
        <?= $exclusive == -1 ? '<br>(' . _('verschiedene Werte') . ')' : '' ?>
    </label>

    <label class="col-3" style="vertical-align: top">
        <?= _('Selbsteintrag erlaubt ab') ?>
        <input type="text" class="size-s" data-datetime-picker id="selfassign_start"  size="20" name="selfassign_start" value="<?= $selfassign_start != -1 ?
            $selfassign_start : '' ?>">
        <?= $selfassign_start == -1 ? '(' . _('verschiedene Werte') . ')' : '' ?>
    </label>

    <label class="col-3" style="vertical-align: top">
        <?= _('Selbsteintrag erlaubt bis') ?>
        <input type="text" class="size-s" data-datetime-picker='{">":"#selfassign_start"}' size="20" name="selfassign_end" value="<?= $selfassign_end != -1 ?
            $selfassign_end : '' ?>">
        <?= $selfassign_end == -1 ? '(' . _('verschiedene Werte') . ')' : '' ?>
    </label>

    <?php foreach ($groups as $g) : ?>
        <input type="hidden" name="groups[]" value="<?= $g->id ?>">
    <?php endforeach ?>
    <?= CSRFProtection::tokenTag() ?>
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
