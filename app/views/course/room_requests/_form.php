<p>
    <?= _('Geben Sie den gewünschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der zuständigen Raumvergabe bearbeitet.') ?>
    <div>
        <strong><?= _('Achtung') ?>:</strong>
        <?= _('Um später einen passenden Raum für Ihre Veranstaltung zu bekommen, geben Sie bitte immer die gewünschten Eigenschaften mit an!') ?>
</p>

<section class="times-rooms-grid">
    <section>
        <h2><?= _('Anfrage') ?></h2>
        <article>
            <?= htmlready($pseudo_request->getTypeString(), 1, 1); ?>
        </article>
    </section>
    <section>
        <h2><?= _('Bearbeitungsstatus') ?></h2>
        <article>
            <?= htmlReady($pseudo_request->getStatusText()); ?>
        </article>
    </section>
</section>

<? if ($selected_room): ?>
    <section>
        <h2><?= _('Angefragter Raum') ?></h2>
        <p>
            <input type="hidden" name="selected_room_id"
                   value="<?= $resource_id ?>">
            <strong><?= htmlReady($selected_room->name) ?></strong>
            <?= Icon::create(
                'trash',
                'clickable',
                [
                    'title' => _('Den ausgewählten Raum löschen')
                ]
            )->asInput(
                [
                    'type' => 'image',
                    'class' => 'text-bottom',
                    'name' => 'reset_resource_id'
                ]
            ) ?>

            <? if (!$search_by_name): ?>
                <input type="hidden" name="category_id"
                       value="<?= htmlReady($category_id) ?>">
                <? foreach ($selected_properties as $property_name => $property_value): ?>
                    <input type="hidden"
                           name="properties[<?= htmlReady($property_name) ?>]"
                           value="<?= htmlReady($property_value) ?>">
                <? endforeach ?>
            <? endif ?>

            <? if($selected_room->properties): ?>
                <? $property_data = $selected_room->getPropertyArray(true);
                $property_names = [];
                foreach ($property_data as $p) {
                    $property_names[] = $p['display_name'];
                }
                ?>
                <?= tooltipIcon(
                    _('Der ausgewählte Raum bietet folgende wünschbaren Eigenschaften:') . " \n"
                  . implode(', ', $property_names)
                ) ?>
            <? endif ?>
        </p>
        <? if ($search_by_name || $seats_selectable): ?>
            <div>
                <label class="undecorated">
                    <?= _('Erwartete Anzahl an benötigten Sitzplätzen:') ?>
                    <input type="number" name="properties[seats]"
                           value="<?= htmlReady($selected_properties['seats']) ?>">
                </label>
            </div>
        <? endif ?>
    </section>
<? else: ?>
    <section class="times-rooms-grid">
        <? if (!Config::get()->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY): ?>
            <section>
                <h2><?= _("Raumeigenschaften angeben:") ?></h2>
                <? if ($available_room_categories): ?>
                    <label class="undecoracted">
                        <? if ($category_id): ?>
                            <?= _('Gewählte Raumkategorie:') ?>
                        <? else: ?>
                            <?= _('Bitte wählen Sie zuerst eine Raumkategorie aus:') ?>
                        <? endif ?>
                        <span class="flex-row">
                            <select name="category_id">
                                <option value=""><?= _('bitte auswählen') ?></option>
                                <? foreach ($available_room_categories as $rc): ?>
                                    <option value="<?= htmlReady($rc->id) ?>"
                                            <?= ($category_id == $rc->id)
                                              ? 'selected="selected"'
                                              : '' ?>>
                                        <?= htmlReady($rc->name) ?>
                                    </option>
                                <? endforeach ?>
                            </select>
                            <?= Icon::create(
                                'accept',
                                'clickable',
                                [
                                    'title' => _('Raumtyp auswählen')
                                ]
                            )->asInput(
                                '20px',
                                [
                                    'type' => 'image',
                                    'class' => 'text-bottom',
                                    'name' => 'select_category_id',
                                    'value' => _('Raumtyp auswählen'),
                                    'style' => 'margin-left: 0.2em; margin-top: 0.5em;'
                                ]
                            ) ?>
                            <? if ($category): ?>
                                <?= Icon::create(
                                    'refresh',
                                    'clickable',
                                    [
                                        'title' => _('alle Angaben zurücksetzen')
                                    ]
                                )->asInput(
                                    '20px',
                                    [
                                        'type' => 'image',
                                        'class' => 'text-bottom',
                                        'name' => 'reset_category_id',
                                        'style' => 'margin-left: 0.2em; margin-top: 0.5em;'
                                    ]
                                ) ?>
                            <? endif ?>
                        </span>
                    </label>
                <? endif ?>
                <? if ($available_properties): ?>
                    <h4><?= _('Die folgenden Eigenschaften sind wünschbar:') ?></h4>
                    <ul class="default">
                    <? foreach ($available_properties as $property): ?>
                        <li>
                            <?= $property->toHtmlInput(
                                $selected_properties[$property->name],
                                'properties[' . htmlReady($property->name) . ']',
                                true,
                                'undecorated',
                                false
                            ) ?>
                        </li>
                    <? endforeach ?>
                    </ul>
                <? else: ?>
                    <?= MessageBox::info(
                        _('Es sind keine wünschbaren Eigenschaften vorhanden.')
                    ) ?>
                <? endif ?>

                <? if ($category_id
                    and ResourceManager::userHasGlobalPermission($current_user, 'autor')): ?>
                    <section>
                        <?= Studip\Button::create(
                            _('Passende Räume suchen'),
                            'search_by_properties'
                        ) ?>
                    </section>
                <? endif ?>
            </section>
        <? endif ?>
        <section>
            <h2><?= _('Raum suchen') ?></h2>
            <? if ($search_requested): ?>
                <? if(empty($found_rooms)): ?>
                    <? if (Request::submitted('search_by_name')): ?>
                        <strong><?= _('Keinen Raum gefunden') ?></strong>
                    <? endif ?>
                <? elseif (count($found_rooms)): ?>
                    <p>
                        <strong>
                        <? if ($search_by_name): ?>
                            <?= sprintf(
                                _('%d Räume gefunden.'),
                                count($found_rooms)
                            ) ?>
                        <? else: ?>
                            <?= sprintf(
                                _('%d passende Räume gefunden.'),
                                count($found_rooms)
                            ) ?>
                        <? endif ?>
                        </strong>
                    </p>
                    <div class="selectbox">
                        <fieldset>
                            <? foreach ($found_rooms as $room): ?>
                                <div class="flex-row">
                                    <label class="horizontal">
                                    <? if ($overlaps[$room->id] <= 0.0): ?>
                                        <?= Icon::create(
                                            'check-circle',
                                            'status-green'
                                        )->asImg(
                                            '16px',
                                            [
                                                'class' => 'text-bottom'
                                            ]
                                        ) ?>
                                    <? elseif($overlaps[$room->id] >= 1.0): ?>
                                        <?= Icon::create(
                                            'decline-circle',
                                            'status-red'
                                        )->asImg(
                                            '16px',
                                            [
                                                'class' => 'text-bottom'
                                            ]
                                        ) ?>
                                    <? else: ?>
                                        <?= Icon::create(
                                            'exclaim-circle', 'status-yellow'
                                        )->asImg(
                                            '16px',
                                            [
                                                'class' => 'text-bottom'
                                            ]
                                        ) ?>
                                    <? endif ?>
                                    <input type="radio" name="selected_room_id"
                                           value="<?= htmlReady($room->id) ?>">
                                    <?= htmlReady(mb_substr($room->name, 0, 50)); ?>
                                    <? if ($room->properties): ?>
                                        <? $property_names = $room->properties->pluck('name') ?>
                                        <?= tooltipIcon(
                                            _('Der gefundene Raum bietet folgende Eigenschaften:')
                                          . " \n"
                                          . implode(',', $property_names)
                                        ) ?>
                                    <? endif ?>
                                    </label>
                                </div>
                            <? endforeach ?>
                        </fieldset>
                    </div>
                    <?= Studip\Button::create(
                        _("Anfragen"),
                        'select_room'
                    ) ?>
                    <?= Studip\Button::create(
                        _("neue Suche starten"),
                        'reset_search'
                    ) ?>
                <? endif ?>
            <? endif ?>
            <? if (!$search_by_name or !count($found_rooms)): ?>
                <section>
                    <input type="text" size="30" maxlength="255"
                           class="no-hint" name="room_name"
                           placeholder="<?= _('Geben Sie zur Suche den Raumnamen ganz oder teilweise ein') ?>">
                        <?= Icon::create(
                            'search',
                            'clickable',
                            [
                                'title' => _('Suche starten')
                            ]
                        )->asInput(
                            [
                                'type' => 'image',
                                'class' => 'middle',
                                'name' => 'search_by_name'
                            ]
                        ) ?>
                </section>
            <? endif ?>
        </section>
    </section>
<? endif ?>
<? if ($user_is_global_resource_admin) : ?>
    <section>
        <h2><?= _('Benachrichtigungen') ?></h2>
        <label>
            <input type="checkbox" name="notify_lecturers" value="1"
                   <?= $notify_lecturers
                     ? 'checked="checked"'
                     : ''
                   ?>>
            <?= _('Benachrichtigung bei Ablehnung der Raumanfrage auch an alle Lehrenden der Veranstaltung senden') ?>
        </label>
    </section>
<? endif ?>

<section>
    <h2><?= _('Nachricht an die Raumvergabe') ?></h2>
    <textarea name="comment" cols="58" rows="4"
              placeholder="<?= _('Weitere Wünsche oder Bemerkungen zur angefragten Raumbelegung') ?>"
              style="width:90%"><?= htmlReady($comment) ?></textarea>
</section>
