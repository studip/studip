<? if ($show_form): ?>
    <form class="default" method="post" action="<?= $form_action_link ?>" data-dialog="reload-on-close">
        <input type="hidden" name="origin_url" value="<?= htmlReady($origin_url) ?>">
        <input type="checkbox" id="multiday" <?= ($begin_date_str != $end_date_str) ? 'checked' : ''; ?>
               onClick="$('#end_date_section').toggle();" class="studip-checkbox">
        <label for="multiday">
            <?= _('Mehrtägig') ?>
        </label>
        <section>
            <label class="col-2" style="min-width: 40%">
                <?= _('Startdatum') ?>
                <input type="text" name="begin_date" class="has-date-picker size-s"
                       value="<?= htmlReady($begin_date_str) ?>">
            </label>
            <label id="end_date_section"
                   style="min-width: 40%;<?= ($begin_date_str == $end_date_str) ? 'display: none;' : ''; ?>"
                   class="col-2">
                <?= _('Enddatum') ?>
                <input type="text" name="end_date" class="has-date-picker size-s"
                       value="<?= htmlReady($end_date_str) ?>">
            </label>
        </section>
        <section>
            <label class="col-2">
                <?= _('Startuhrzeit') ?>
                <input type="text" name="begin_time" class="has-time-picker size-s"
                       value="<?= htmlReady($begin_time_str) ?>">
            </label>
            <label class="col-2">
                <?= _('Enduhrzeit') ?>
                <input type="text" name="end_time" class="has-time-picker size-s"
                       value="<?= htmlReady($end_time_str) ?>">
            </label>
        </section>
        <label>
            <?= _('Rüstzeit') ?>
            <input type="number" name="preparation_time" value="<?= htmlReady($preparation_time) ?>" min="0"
                   max="<?= htmlReady($max_preparation_time) ?>" class="size-s">
        </label>
        <label>
            <?= _('Anfragetext') ?>
            <textarea name="comment"><?= htmlReady($comment) ?></textarea>
        </label>
        <footer data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'save') ?>
            <? if ($request): ?>
                <?= \Studip\LinkButton::create(
                    _('Löschen'),
                    $controller->url_for('resources/room_request/delete/' . $request->id),
                    ['data-dialog' => '1']
                ) ?>
            <? endif ?>
        </footer>
    </form>
<? endif ?>
