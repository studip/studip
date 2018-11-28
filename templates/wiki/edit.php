<form method="post" action="<?= URLHelper::getLink('?cmd=edit', compact('keyword')) ?>" data-secure class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= htmlReady($keyword) ?></legend>

        <input type="hidden" name="wiki" value="<?= htmlReady($keyword) ?>">
        <input type="hidden" name="version" value="<?= htmlReady($version) ?>">
        <input type="hidden" name="submit" value="true">
        <input type="hidden" name="cmd" value="show">

        <br>
        <textarea name="body" class="wiki-editor add_toolbar wysiwyg resizable size-l"
            data-editor="extraPlugins=studip-wiki"
        ><?= wysiwygReady($body) ?></textarea>
    </fieldset>

    <footer>
        <div class="button-group">
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\Button::create(_('Speichern und weiter bearbeiten'), 'submit-and-edit') ?>
        </div>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('?cmd=abortedit' . $lastpage, compact('keyword'))) ?>
    </footer>
</form>
