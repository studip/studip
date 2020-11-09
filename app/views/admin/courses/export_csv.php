<form action="<?= $controller->link_for('admin/courses/export_csv') ?>" method="get" class="default">
    <fieldset>
    <? foreach ($fields as $index => $name) : ?>
        <? if ($index !== 'contents') : ?>
            <label>
                <input type="checkbox" name="fields[]" value="<?= htmlReady($index) ?>"
                       <?= in_array($index, $selection) ? ' checked' : "" ?>>
                <?= htmlReady($name) ?>
            </label>
        <? endif ?>
    <? endforeach ?>
    </fieldset>
    <div data-dialog-button>
        <?= Studip\Button::create(_('Auswahl exportieren')) ?>
    </div>
</form>
