<form class="default" action="<?= $controller->link_for('course/lti/select_link') ?>" method="post">
    <label>
        <?= _('Auswahl des externen Tools') ?>
        <select name="tool_id">
            <? foreach ($tools as $tool): ?>
                <option value="<?= $tool->id ?>"><?= htmlReady($tool->name) ?></option>
            <? endforeach ?>
        </select>
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Tool auswählen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/lti')) ?>
    </footer>
</form>
