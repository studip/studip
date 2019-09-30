<? if (isset($title)): ?>
    <h1><?= htmlReady($title) ?></h1>
<? endif; ?>
<form method="POST" action="<?= $controller->url_for("multipersonsearch/no_js_form/?name=" . $name) ?>">
    <input type="hidden" name="search_persons_selectable_hidden" value="<?=htmlReady(studip_json_encode($selectableUsersHidden))?>">
    <input type="hidden" name="search_persons_selected_hidden" value="<?=htmlspecialchars(studip_json_encode($selectedUsersHidden))?>">
    <input type="hidden" name="last_search_hidden" value="<?= $search?>">
    <input type="hidden" name="last_search_preset" value="<?= $searchPreset?>">
    <input type="hidden" name="not_first_call" value="true">
    <?= CSRFProtection::tokenTag() ?>

    <!-- neue Suche -->
    <div id="search_persons" style="width: 800px;">
        <label>
            <input name="freesearch" type="text" placeholder="<?=_('Suchen')?>"
                   aria-label="<?= _('Suchbegriff') ?>" style="width: 45%" value="<?= $search ?>">
            <?= Icon::create('search', 'clickable', ['title' => _('Suche starten')])->asInput(['name'=>'submit_search','class'=>'stay_on_dialog',]) ?>
        </label>
        <br><br>
         <select name="search_preset" aria-label="<?= _('Vorauswahl bestimmter Bereiche, alternativ zur Suche') ?>" style="width: 45%">
            <option><?=_('--- Suchvorlagen ---')?></option>
        <? foreach ($quickfilter as $title) : ?>
            <option value="<?= htmlReady($title) ?>" <?= $searchPreset == $title ? "selected" : ""; ?>>
                <?= $title; ?>
            </option>
        <? endforeach; ?>
        </select>
        <?= Icon::create('accept', 'clickable', ['title' => _('Vorauswahl anwenden')])->asInput(['name'=>'submit_search_preset','class'=>'stay_on_dialog']) ?>

        <div id="search_persons_content">
            <div style="display: inline-block; float: left; width: 44%; height: 100%">
                <label><?=_('Suchergebnis')?><br>
                <select id="search_persons_selectable" name="search_persons_selectable[]" style="minWidth: 200px; width: 100%; height: 116px" style="height: 16px" multiple
                        aria-label="<?= _('Gefundene Personen, die der Gruppe hinzugefügt werden können') ?>">
                        <? if (count($selectableUsers) == 0) : ?>
                            <option disabled><?= _("Keine neuen Suchergebnisse gefunden"); ?></option>
                        <? else : ?>
                            <? foreach ($selectableUsers as $person): ?>
                                <option value="<?= $person->id ?>"><?= htmlReady($person->nachname . ', ' . $person->vorname) ?> - <?= htmlReady($person->perms) ?> (<?= htmlReady($person->username)?>)</option>
                            <? endforeach; ?>
                        <? endif; ?>
                </select>
                </label>
            </div>
            <div style="display: inline-block; width: 10%; text-align: center">
                <br>
                <br>
                <br>
                <?= Icon::create('arr_2right', 'clickable', ['title' => _('In den Suchergebnissen markierte Bereiche der Gruppe hinzufügen')])
                        ->asInput([
                            'id' => 'search_persons_add',
                            'name' => 'search_persons_add',
                            'class' => 'stay_on_dialog']) ?>
                <br><br>
                <?= Icon::create('arr_2left', 'clickable', ['title' => _('Bei den bereits ausgewählten Personen die markierten Personen entfernen')])
                        ->asInput([
                            'id' => 'search_persons_remove',
                            'name' => 'search_persons_remove',
                            'class' => 'stay_on_dialog']) ?>
            </div>
            <div style="display: inline-block; float: right; width: 44%">
                <label>
                <div>
                    <? $selectedCount = count($selectedUsers);
                    if ($selectedCount == 0) : ?>
                        <?=_('Niemand wurde ausgewählt.')?>
                    <? elseif ($selectedCount == 1) : ?>
                        <?=_('Eine Person wurde ausgewählt')?>
                    <? else : ?>
                        <?=sprintf(_('%s Personen wurden ausgewählt.'), $selectedCount)?>
                    <? endif ?>
                </div>
                <select id="search_persons_selected" name="search_persons_selected[]" style="minWidth: 200px; width: 100%; height: 116px" size="7" multiple
                        aria-label="<?= _('Personen, die in die Gruppe eingetragen werden') ?>"
                        >
                    <? foreach ($selectedUsers as $user): ?>
                        <option value="<?= $user->id ?>"><?= htmlReady($user->nachname . ', ' . $user->vorname) ?> - <?= htmlReady($user->perms) ?> (<?= htmlReady($user->username)?>)</option>
                    <? endforeach; ?>
                </select>
                </label>
            </div><br>
        </div>
    </div>
    <br>
    <?= $additionHTML; ?>
    <?= \Studip\Button::create(_('Speichern'), 'save') ?>
    <?= \Studip\Button::create(_('Abbrechen'), 'abort') ?>
</form>
