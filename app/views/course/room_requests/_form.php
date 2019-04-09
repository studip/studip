<input type="hidden" name="room_request_form" value="1">
<? if (isset($new_room_request_type)) : ?>
    <input type="hidden" name="new_room_request_type" value="<?= $new_room_request_type ?>">
<? endif ?>
<?= MessageBox::info(_('Geben Sie den gewünschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der
                    zuständigen Raumverwaltung bearbeitet.'),
    [_('<b>Achtung:</b> Um später einen passenden Raum für Ihre Veranstaltung zu bekommen,
        geben Sie bitte <span style="text-decoration: underline">immer</span> die gewünschten Eigenschaften mit an!')
    ]) ?>

<section class="times-rooms-grid ">
    <section>
        <h2><?= _('Art des Wunsches') ?></h2>
        <article>
            <?= htmlready($request->getTypeExplained(), 1, 1); ?>
        </article>
    </section>
    <section>
        <h2><?= _('Bearbeitungsstatus') ?></h2>
        <article>
            <? if ($request->isNew()) : ?>
                <?= _("Diese Anfrage ist noch nicht gespeichert") ?>
            <? else : ?>
                <?= htmlReady($request->getStatusExplained()); ?>
            <? endif ?>
        </article>
    </section>
</section>


<div style="clear: both"></div>

<?
if ($request_resource_id = $request->getResourceId()) :
    $resObject = ResourceObject::Factory($request_resource_id);
?>
    <section style="margin: 20px 0;">
        <h2><?= _('Gewünschter Raum') ?></h2>

        <p>
            <strong><?= htmlReady($resObject->getName()) ?></strong>
            <?= Icon::create('trash', 'clickable', ['title' => _('den ausgewählten Raum löschen')])->asInput(['type' => 'image', 'style' => 'vertical-align:middle', 'name' => 'reset_resource_id']) ?>
            <? if($resObject->getPlainProperties(false, true)): ?>
            <?= tooltipIcon(_('Der ausgewählte Raum bietet folgende der wünschbaren Eigenschaften:') . " \n" . $resObject->getPlainProperties(false, true)) ?>
            <? endif; ?>
        </p>
        <input type="hidden" name="selected_room" value="<?= htmlready($request_resource_id) ?>">
    </section>
<? endif ?>


<section class="times-rooms-grid ">
    <? if (!Config::get()->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY) : ?>
    <section>
        <h2>
            <?= _("Raumeigenschaften angeben:") ?>
        </h2>
        <? if ($request->getCategoryId()) : ?>
            <? if (count($room_categories)) : ?>
                <label for="select_room_type">
                    <?= _('Gewählter Raumtyp') ?>
                </label>


                <select name="select_room_type" id="select_room_type" style="width: auto;" >
                    <? foreach ($room_categories as $rc) : ?>
                        <?= sprintf('<option value="%s" %s>%s </option>',
                            $rc["category_id"],
                            ($request->category_id == $rc["category_id"]) ? "selected" : "",
                            htmlReady($rc["name"])
                        ) ?>
                    <? endforeach ?>
                </select>
                <?= Icon::create("accept", "clickable", ['title' => _('Raumtyp auswählen')])
                        ->asInput(['type'  => "image",
                                   'style' => "vertical-align:middle",
                                   'name'  => "send_room_type",
                                   'value' => _("Raumtyp auswählen")]) ?>
                <?= Icon::create('refresh', 'clickable', ['title' => _('alle Angaben zurücksetzen')])->asInput(["type" => "image", "style" => "vertical-align:middle", "name" => "reset_room_type"]) ?>


            <? endif ?>
            <? $props = $request->getAvailableProperties() ?>
            <? if (!empty($props)) : ?>
                <h4><?= _('Folgende Eigenschaften sind wünschbar:') ?></h4>
                <? foreach ($props as $index => $prop) : ?>
                    <section>

                        <? if ($prop['type'] != 'bool') : ?>
                            <label for="<?= $prop['type'] ?>_<?= $index ?>">
                                <?= htmlReady($prop["name"]) ?>
                            </label>
                        <? endif ?>

                        <? if ($prop['type'] == 'bool') : ?>

                            <label for="bool_<?= $index ?>" class="horizontal">
                                <input type="checkbox" id="bool_<?= $index ?>"
                                       name="request_property_val[<?= $prop["property_id"] ?>]"
                                    <?= $request->getPropertyState($prop["property_id"]) ? "checked" : "" ?>>
                                <?= htmlReady($prop["name"]) ?>
                            </label>


                        <? elseif ($prop['type'] == 'num'): ?>
                            <? if ($prop['system'] == 2) : ?>
                                <input type="number" id="num_<?= $index ?>"
                                       name="request_property_val[<?= $prop["property_id"] ?>]"
                                       value="<?= htmlReady($request->getPropertyState($prop["property_id"])) ?>">
                            <? else : ?>
                                <input id="num_<?= $index ?>" type="text" size="4" maxlength="4"
                                       name="request_property_val[<?= $prop["property_id"] ?>]"
                                       value="<?= htmlReady($request->getPropertyState($prop["property_id"]))  ?>">
                            <? endif ?>
                        <? elseif ($prop['type'] == 'text') : ?>
                            <textarea id="text_<?= $index ?>" name="request_property_val[<?= $prop["property_id"] ?>]"
                                      cols="30"
                                      rows="2"><?= htmlReady($request->getPropertyState($prop["property_id"])) ?></textarea>
                        <? else : ?>
                            <? $options = explode(";", $prop["options"]); ?>
                            <select id="select_<?= $index ?>" name="request_property_val[<?= $prop["property_id"] ?>]">
                                <option value="">--</option>
                                <? foreach ($options as $a) : ?>
                                    <option <?= ($request->getPropertyState($prop["property_id"]) == $a) ? "selected" : "" ?>
                                        value="<?= $a ?>"><?= htmlReady($a) ?></option>
                                <? endforeach ?>
                            </select>
                        <? endif ?>



                    </section>
                <? endforeach ?>
            <? endif ?>
        <? else : ?>
            <label for="select_room_type">
                <?= _('Bitte geben Sie zunächst einen Raumtyp an, der für Sie am besten geeignet ist') ?>
            </label>
            <select name="select_room_type" id="select_room_type" style="width:auto;">
                <option value=""><?= _('bitte auswählen') ?></option>
                <? foreach ($room_categories as $rc) : ?>
                    <option value="<?= $rc["category_id"] ?>"><?= htmlReady($rc["name"]) ?></option>
                <? endforeach ?>
            </select>
            <?= Icon::create("accept", "clickable", ['title' => _('Raumtyp auswählen')])
                    ->asInput(['type'  => "image",
                               'style' => "vertical-align:middle",
                               'name'  => "send_room_type",
                               'value' => _("Raumtyp auswählen")]) ?>
        <? endif ?>

        <? if ($request->category_id) : ?>
            <section>
                <?= Studip\Button::create(_('Passende Räume suchen'), 'search_properties') ?>
            </section>
        <? endif ?>
    </section>
    <? endif; ?>
    <section>
        <h2>
            <?= _('Raum suchen') ?>
        </h2>
        <? if (!empty($search_result)) : ?>
            <? if (count($search_result)) : ?>
                <p>
                    <strong><?= sizeof($search_result) ?></strong> <?= (!$search_by_properties ? _("Räume gefunden:") : _("passende Räume gefunden.")) ?>
                </p>
            <? endif ?>
            <div class="selectbox">
            <?  $match_found = false;
                foreach ($search_result as $key => $val)  : ?>
                <? $resObject = ResourceObject::Factory($key);
                    $selected = $val['icon']->getRole() === 'status-green';
                ?>

                <div>
                    <input type="radio" name="select_room" value="<?= $key ?>"
                           id="select_room_<?= $key ?>"
                           <? if (!$match_found && $selected) echo 'checked'; ?>>

                    <label class="undecorated" for="select_room_<?= $key ?>">
                        <?= $val['icon']->asImg(['class' => 'text-bottom']) ?>
                        <?= htmlReady(my_substr($val['name'], 0, 50)); ?>
                    <? if ($resObject->getPlainProperties(false, true)): ?>
                        <?= tooltipIcon(_('Der gefundene Raum bietet folgende Eigenschaften:') . " \n" . $resObject->getPlainProperties(false, true)) ?>
                    <? endif; ?>
                    </label>
                </div>
            <? $match_found = $match_found || $selected;
                endforeach ?>

            </div>
            <?= Studip\Button::create(_("Raum als Wunschraum auswählen"), 'send_room') ?>
            <?= Studip\Button::create(_("neue Suche starten"), 'reset_room_search') ?>
            <? if ($search_by_properties) : ?>
                <p><strong><?= _('Diese Räume erfüllen die Wunschkriterien, die Sie links angegeben haben.') ?></strong>
                </p>
            <? endif ?>
        <? else : ?>
            <? if (Request::get('search_exp_room')) : ?>
                <p><strong><?= _('Keinen') ?></strong> <?= _('Raum gefunden') ?></p>
            <? endif ?>

        <? endif ?>
        <? if (empty($search_result)) : ?>
            <section>

                <input id="search_exp_room" type="text" size="30" maxlength="255" name="search_exp_room"
                       placeholder="<?= _('Geben Sie zur Suche den Raumnamen ganz oder teilweise ein') ?>">
                <?= Icon::create('search', 'clickable', ['title' => _('Suche starten')])->asInput( ["type" => "image", "class" => "middle", "name" => "search_room"]) ?>
            </section>
        <? endif ?>
    </section>
</section>

<? if ($is_resources_admin) : ?>
    <section>
        <h2><?= _('Benachrichtigungen') ?></h2>


        <label for="reply_recipients_lecturer" class="horizontal">
            <input type="checkbox" name="reply_recipients" id="reply_recipients_lecturer"
                   value="lecturer" <?= ($request->reply_recipients == 'lecturer' ? 'checked' : '') ?>>
            <?= _('Benachrichtigung bei Ablehnung der Raumanfrage auch an alle Lehrenden der Veranstaltung senden') ?>
        </label>
    </section>
<? endif ?>

<section>
    <h2><?= _('Nachricht an den Raumadministrator') ?></h2>


        <textarea name="comment" cols="58" rows="4" placeholder="<?= _('Weitere Wünsche oder Bemerkungen zur gewünschten Raumbelegung') ?>"
                  style="width:90%"><?= htmlReady($request->getComment()); ?></textarea>
</section>
