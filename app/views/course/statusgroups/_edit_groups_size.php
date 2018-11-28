<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_save_groups_size') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <label>
        <?= _('Gruppengröße') ?>
        <input type="number" name="size" value="<?= intval($size) ?>" min="0">
        <?= $different_sizes ? '(' . _('verschiedene Werte') . ')' : '' ?>
    </label>

    <?php foreach ($groups as $g) : ?>
        <input type="hidden" name="groups[]" value="<?= $g->id ?>">
    <?php endforeach ?>

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
