<form action="<?= $controller->new() ?>" method="post" enctype="multipart/form-data" class="default">
    <fieldset>
        <legend>
            <?= _('Neues Banner anlegen') ?>
        </legend>

        <label class="file-upload">
            <?= _('Bilddatei auswählen') ?>
            <input id="imgfile" name="imgfile" type="file" accept="image/*">
        </label>

        <label>
            <?= _('Beschreibung') ?>

            <input type="text" id="description" name="description"
                   value="<?= htmlReady($this->flash['request']['description']) ?>"
                   style="width: 240px;" maxlen="254">
        </label>

        <label>
            <?= _('Alternativtext') ?>

            <input type="text" id="alttext" name="alttext"
                   value="<?= htmlReady($this->flash['request']['alttext']) ?>"
                   style="width: 240px;" maxlen="254">
        </label>

        <label>
            <?= _('Verweis-Typ') ?>

            <select id="target_type" name="target_type">
            <? foreach ($target_types as $key => $label): ?>
                <option value="<?= $key ?>"><?= $label ?></option>
            <? endforeach; ?>
            </select>
        </label>


        <label>
            <?= _('Verweis-Ziel') ?>

            <input type="url" class="target-url" name="target"
                   placeholder="<?= _('URL eingeben') ?>"
                   value="<?= htmlReady($this->flash['request']['target']) ?>"
                   style="width: 240px;" maxlen="254">

            <?= QuickSearch::get('seminar', new StandardSearch('Seminar_id'))
                           ->setInputStyle('width: 240px')
                           ->setInputClass('target-seminar')
                           ->render() ?>

            <?= QuickSearch::get('institut', new StandardSearch('Institut_id'))
                           ->setInputStyle('width: 240px')
                           ->setInputClass('target-inst')
                           ->render() ?>

            <?= QuickSearch::get('user', new StandardSearch('username'))
                           ->setInputStyle('width: 240px')
                           ->setInputClass('target-user')
                           ->render() ?>

            <span class="target-none"><?= _('Kein Verweisziel') ?></span>
        </label>

        <label>
            <?= _('Anzeigen ab') ?>
            <?= $this->render_partial('admin/banner/datetime-picker', ['prefix' => 'start_']) ?>
        </label>


        <label>
            <?= _('Anzeigen bis')?>
            <?= $this->render_partial('admin/banner/datetime-picker', ['prefix' => 'end_']) ?>
        </label>

        <label>
            <?= _('Priorität')?>

            <select name="priority">
            <? foreach ($priorities as $key => $label): ?>
                <option value="<?= $key ?>"><?= $label ?></option>
            <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'anlegen') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->indexURL()) ?>
    </footer>
</form>

<script type="text/javascript">
jQuery(function ($) {
    $('#target_type').change(function () {
        var target = $(this).val();
        $(this).closest('label').next().find('[class^="target"]').hide().filter('.target-' + target).show();
    }).change();
});
</script>
