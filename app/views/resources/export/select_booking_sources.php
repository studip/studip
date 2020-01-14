<? if ($available_rooms || $available_clipboards): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('resources/export/bookings') ?>">
        <?= CSRFProtection::tokenTag() ?>
        <article class="default">
            <header><?= _('Zeitbereich wählen') ?></header>
            <section>
                <label>
                    <?= _('Startzeitpunkt') ?>
                    <input type="text" class="has-date-picker" name="begin_date"
                           value="<?= $begin->format('d.m.Y') ?>">
                    <input type="text" class="has-time-picker" name="begin_time"
                           value="<?= $begin->format('H:i')?>">
                </label>
                <label>
                    <?= _('Endzeitpunkt') ?>
                    <input type="text" class="has-date-picker" name="end_date"
                           value="<?= $end->format('d.m.Y') ?>">
                    <input type="text" class="has-time-picker" name="end_time"
                           value="<?= $end->format('H:i')?>">
                </label>
            </section>
        </article>
        <? if ($available_rooms): ?>
            <table class="default">
                <caption>
                    <?= sprintf(
                        ngettext(
                            '%u Raum',
                            '%u Räume',
                            count($available_rooms)
                        ),
                        count($available_rooms)
                    ) ?>
                </caption>
                <colgroup>
                    <col class="checkbox">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox"
                                   data-proxyfor="input[name='selected_rooms[]']">
                        </th>
                        <th><?= _('Räume') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($available_rooms as $room): ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       name="selected_rooms[]"
                                       value="<?= htmlReady($room->id) ?>">
                            </td>
                            <td>
                                <?= htmlReady($room->name) ?>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
        <? endif ?>
        <? if ($available_clipboards): ?>
            <table class="default">
                <caption>
                    <?= sprintf(
                        ngettext(
                            '%u Raumgruppe',
                            '%u Raumgruppen',
                            count($available_clipboards)
                        ),
                        count($available_clipboards)
                    ) ?>
                </caption>
                <colgroup>
                    <col class="checkbox">
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox"
                                   data-proxyfor="input[name='selected_clipboards[]']">
                        </th>
                        <th><?= _('Raumgruppen') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($available_clipboards as $clipboard): ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       name="selected_clipboards[]"
                                       value="<?= htmlReady($clipboard->id) ?>">
                            </td>
                            <td>
                                <?= htmlReady($clipboard->name) ?>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
            </table>
        <? endif ?>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Liste mit Buchungen exportieren')) ?>
        </div>
    </form>
<? endif ?>
