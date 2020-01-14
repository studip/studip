<? if ($show_form): ?>
    <form class="default" method="post" id="room_request-form"
          action="<?= $form_action_link ?>"
          data-dialog="reload-on-close">
        <input type="hidden" name="origin_url"
               value="<?= htmlReady($origin_url)?>">
        <fieldset>
            <legend><?= _('Zeitbereich') ?></legend>

            <fieldset id="start_date_section">
                <label>
                    <input type="checkbox" id="multiday" <?= ($begin_date_str != $end_date_str)?'checked':'';?> onClick="$('#end_date_section').toggle();">
                    <?= _('Mehrtägig') ?>
                </label>

                <legend><?= _('Datum') ?></legend>
                <label>
                    <?= _('Start') ?>
                    <input type="text" name="begin_date" class="has-date-picker"
                        value="<?= htmlReady($begin_date_str) ?>">
                </label>
                <label id="end_date_section" style="<?= ($begin_date_str == $end_date_str)?'display: none;':'';?>">
                    <?= _('Ende') ?>
                    <input type="text" name="end_date" class="has-date-picker"
                        value="<?= htmlReady($end_date_str) ?>">
                </label>
            </fieldset>

            <fieldset>
                <legend><?= _('Zeit') ?></legend>

                <label>
                    <?= _('Start') ?>
                    <input type="text" name="begin_time" class="has-time-picker"
                        value="<?= htmlReady($begin_time_str) ?>">
                </label>
                <label>
                    <?= _('Ende') ?>
                    <input type="text" name="end_time" class="has-time-picker"
                        value="<?= htmlReady($end_time_str) ?>">
                </label>
                <label>
                    <?= _('Rüstzeit') ?>
                    <input type="number" name="preparation_time"
                           value="<?= htmlReady($preparation_time) ?>"
                           min="0" max="<?= htmlReady($max_preparation_time )?>">
                </label>
            </fieldset>

        </fieldset>
        <fieldset>
            <legend><?= _('Anfragetext') ?></legend>
            <textarea name="comment"><?= htmlReady($comment) ?></textarea>
        </fieldset>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Speichern'), 'save') ?>
            <? if ($request): ?>
                <?= \Studip\LinkButton::create(
                    _('Löschen'),
                    $controller->url_for(
                        'resources/room_request/delete/' . $request->id
                    ),
                    ['data-dialog' => '1']
                ) ?>
            <? endif ?>
        </div>
    </form>
<? endif ?>
