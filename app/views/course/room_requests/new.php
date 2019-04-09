<form method="POST" class="default" name="new_room_request"
      action="<?= $this->controller->link_for('course/room_requests/edit/' . $course_id, $url_params) ?>" <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _("Raumanfrage erstellen") ?></legend>

        <? if (count($options)) : ?>
        <label>
            <?= _("Art der Raumanfrage:") ?>
            <select id="new_room_request_type" name="new_room_request_type">
                <? foreach ($options as $one) : ?>
                    <option value="<?= $one['value'] ?>">
                        <?= htmlReady($one['name']) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>

        <div class="text-center" data-dialog-button>

        </div>
        <? else : ?>
            <?= MessageBox::info(_("In dieser Veranstaltung können keine weiteren Raumanfragen gestellt werden.")) ?>
        <? endif ?>
    </fieldset>

    <footer data-dialog-button>
        <? if (count($options)) : ?>
            <?= Studip\Button::create(_('Erstellen')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('course/room_requests/index/' . $course_id)) ?>
        <? else : ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->link_for('course/room_requests/index/' . $course_id), ['data-dialog' => 'size=big']) ?>
        <? endif ?>
    </footer>
</form>
