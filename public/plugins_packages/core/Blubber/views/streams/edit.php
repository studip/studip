<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<input type="hidden" id="base_url" value="plugins.php/blubber/streams/">
<form action="?" method="post" id="edit_stream" enctype="multipart/form-data" class="default">
    <fieldset>
        <legend><?= ('Neuen Stream erstellen') ?></legend>
        <label for="stream_name">
            <?= _("Titel") ?>
            <input type="text" name="name" id="stream_name" required value="<?= htmlReady($stream['name']) ?>">
        </label>
        <label for="stream_sort">
            <?= _("Sortierung der Threads") ?>
            <select id="stream_sort" name="sort">
                <option value="activity"<?= $stream['sort'] === "activity" ? " selected" : "" ?>><?= _("Nach neuster Aktivität") ?></option>
                <option value="age"<?= $stream['sort'] === "age" ? " selected" : "" ?>><?= _("Nach Alter") ?></option>
            </select>
        </label>

        <label for="stream_image" class="file-upload">
            <?= _("Bild") ?>
            <input type="file" name="image" id="stream_image">
        </label>

        <label for="stream_defaultstream">
            <input type="checkbox" id="stream_defaultstream" name="defaultstream" value="1"<?= $stream['defaultstream'] ? " checked" : "" ?>>
            <?= _("Standardstream, wenn man auf Community klickt") ?>
        </label>

        <label>
            <?= _("Bisherige Anzahl Threads in diesem Stream") ?>
            <?= $stream->isNew() ? "0" : $stream->fetchNumberOfThreads() ?>
        </label>
    </fieldset>

    <fieldset>
        <legend><?= _("Sammlung") ?></legend>

        <div>
            <?= _("Definiere, welche Postings Dein Stream umfassen soll.") ?>
        </div>

        <table class="default select nohover">
            <tbody>
                <tr>
                    <td width="33%" class="<?= $stream['pool_courses'] && count($stream['pool_courses']) ? "selected " : "" ?>">
                        <div class="label">
                            <?= Icon::create('seminar', 'info')->asImg(32) ?>
                            <br>
                            <?= _("Veranstaltungen") ?>
                        </div>
                        <? $label = _("Wählen Sie die Veranstaltungen aus, deren Blubber im Stream auftauchen sollen.") ?>
                        <select multiple name="pool_courses[]" style="max-width: 220px;" size="8"
                                aria-label="<?= $label ?>" title="<?= $label ?>"
                                class="selector"
                                >
                            <option value="all"<?= in_array("all", (array) $stream['pool_courses']) ? " selected" : "" ?>><?= _("alle") ?></option>
                            <? foreach (User::find($GLOBALS['user']->id)->course_memberships as $membership) : ?>
                            <option value="<?= $membership['Seminar_id'] ?>"<?= in_array($membership['Seminar_id'], (array) $stream['pool_courses']) ? " selected" : "" ?>><?= htmlReady($membership->course['name']) ?></option>
                            <? endforeach ?>
                        </select>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="pool_courses_check" id="pool_courses_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['pool_courses'] && count($stream['pool_courses']) ? " checked" : "" ?>>
                    </td>
                    <td width="33%" class="<?= $stream['pool_groups'] && count($stream['pool_groups']) ? "selected " : "" ?>">
                        <div class="label">
                            <?= Icon::create('community', 'info')->asImg(32) ?>
                            <br>
                            <?= _("Kontaktgruppen") ?>
                        </div>
                        <? $label = _("Wählen Sie die Kontaktgruppen aus, deren Blubber im Stream erscheinen sollen.") ?>
                        <select multiple name="pool_groups[]" style="max-width: 220px;"
                                aria-label="<?= $label ?>" title="<?= $label ?>" size="8"
                                class="selector"
                                >
                            <option value="all"<?= in_array("all", (array) $stream['pool_groups']) ? " selected" : "" ?>><?= _("alle Buddies") ?></option>
                            <? foreach ($contact_groups as $group) : ?>
                            <option value="<?= $group['statusgruppe_id'] ?>"<?= in_array($group['statusgruppe_id'], (array) $stream['pool_groups']) ? " selected" : "" ?>><?= htmlReady($group['name']) ?></option>
                            <? endforeach ?>
                        </select>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="pool_groups_check" id="pool_groups_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['pool_groups'] && count($stream['pool_groups'])? " checked" : "" ?>>
                    </td>
                    <td width="33%" class="<?= $stream['pool_hashtags'] ? "selected " : "" ?>">
                        <div class="label">
                            <img src="<?= $assets_url."/images/hash.png" ?>">
                            <br>
                            <?= _("Hashtags") ?>
                        </div>
                        <? $label = _("Benennen Sie beliebig viele mit Leerzeichen getrennte #Hashtags. Alle für Sie potentiell sichtbaren Blubber (öffentlich, privat oder aus Veranstaltungen) mit dem Hashtag tauchen dann im Stream auf.") ?>
                        <div>
                        <textarea name="pool_hashtags" rows="6" style="width: 98%; max-width: 220px;"
                                  aria-label="<?= $label ?>" title="<?= $label ?>"
                                  placeholder="<?= _("z.B. #opensource #mathematik") ?>"
                                  class="selector"
                                  ><?= $stream['pool_hashtags'] ? htmlReady("#".implode(" #", $stream['pool_hashtags'])) : "" ?></textarea>
                        </div>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="pool_hashtags_check" id="pool_hashtags_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['pool_hashtags'] ? " checked" : "" ?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <fieldset>
        <legend>
            <?= _("Filterung") ?>
        </legend>

        <div>
            <?= _("Grenze die oben definierte Sammlung an Postings ein") ?>
        </div>

        <table class="default select nohover">
            <tbody>
                <tr>
                    <td width="20%" class="<?= $stream['filter_type'] && count($stream['filter_type']) ? "selected " : "" ?>">
                        <div class="label">
                            <?= Icon::create('doit', 'info')->asImg(32) ?>
                            <br>
                            <?= _("Blubber-Typen") ?>
                        </div>
                        <? $label = _("Nur Blubber von folgendem Typ einbeziehen.") ?>
                        <select multiple name="filter_type[]" style="max-width: 220px;" size="8"
                                aria-label="<?= $label ?>" title="<?= $label ?>"
                                class="selector"
                                >
                            <option value="public"<?= in_array("public", (array) $stream['filter_type']) ? " selected" : "" ?>><?= _("Öffentlich") ?></option>
                            <option value="private"<?= in_array("private", (array) $stream['filter_type']) ? " selected" : "" ?>><?= _("Privat") ?></option>
                            <option value="course"<?= in_array("course", (array) $stream['filter_type']) ? " selected" : "" ?>><?= _("Veranstaltungsblubber") ?></option>
                        </select>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="filter_type_check" id="filter_type_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['filter_type'] && count($stream['filter_type']) ? " checked" : "" ?>>
                    </td>
                    <td width="20%" class="<?= $stream['filter_courses'] && count($stream['filter_courses']) ? "selected " : "" ?>">
                        <div class="label">
                            <?= Icon::create('seminar', 'info')->asImg(32) ?>
                            <br>
                            <?= _("Veranstaltungen") ?>
                        </div>
                        <? $label = _("Wählen Sie Veranstaltungen aus, die nicht im Stream berücksichtigt werden sollen.") ?>
                        <select multiple name="filter_courses[]" style="max-width: 220px;" size="8"
                                aria-label="<?= $label ?>" title="<?= $label ?>"
                                class="selector"
                                >
                            <option value="all"<?= in_array("all", (array) $stream['filter_courses']) ? " selected" : "" ?>><?= _("alle") ?></option>
                            <? foreach (User::find($GLOBALS['user']->id)->course_memberships as $membership) : ?>
                            <option value="<?= $membership['Seminar_id'] ?>"<?= in_array($membership['Seminar_id'], (array) $stream['filter_courses']) ? " selected" : "" ?>><?= htmlReady($membership->course_name) ?></option>
                            <? endforeach ?>
                        </select>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="filter_courses_check" id="filter_courses_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['filter_courses'] && count($stream['filter_courses']) ? " checked" : "" ?>>
                    </td>
                    <td width="20%" class="<?= $stream['filter_groups'] && count($stream['filter_groups']) ? "selected " : "" ?>">
                        <div class="label">
                            <?= Icon::create('community', 'info')->asImg(32) ?>
                            <br>
                            <?= _("Kontaktgruppen") ?>
                        </div>
                        <? $label = _("Wählen Sie die Kontaktgruppen aus, deren Blubber im Stream nicht erscheinen sollen.") ?>
                        <select multiple name="filter_groups[]" style="max-width: 220px;"
                                aria-label="<?= $label ?>" title="<?= $label ?>" size="8"
                                class="selector"
                                >
                            <option value="all"<?= in_array("all", (array) $stream['filter_groups']) ? " selected" : "" ?>><?= _("alle Buddies") ?></option>
                            <? foreach ($contact_groups as $group) : ?>
                            <option value="<?= $group['statusgruppe_id'] ?>"<?= in_array($group['statusgruppe_id'], (array) $stream['filter_groups']) ? " selected" : "" ?>><?= htmlReady($group['name']) ?></option>
                            <? endforeach ?>
                        </select>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="filter_groups_check" id="filter_groups_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['filter_groups'] && count($stream['filter_groups'])? " checked" : "" ?>>
                    </td>
                    <td width="20%" class="<?= $stream['filter_hashtags'] ? "selected " : "" ?>">
                        <div class="label">
                            <img src="<?= $assets_url."/images/hash.png" ?>">
                            <br>
                            <?= _("Nur mit Hashtags") ?>
                        </div>
                        <? $label = _("Benennen Sie beliebig viele mit Leerzeichen getrennte #Hashtags. ") ?>
                        <div>
                        <textarea name="filter_hashtags" rows="6" style="width: 98%; max-width: 220px;"
                                  aria-label="<?= $label ?>" title="<?= $label ?>"
                                  placeholder="<?= _("z.B. #opensource #mathematik") ?>"
                                  class="selector"
                                  ><?= $stream['filter_hashtags'] ? htmlReady("#".implode(" #", $stream['filter_hashtags'])) : "" ?></textarea>
                        </div>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="filter_hashtags_check" id="filter_hashtags_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['filter_hashtags'] ? " checked" : "" ?>>
                    </td>
                    <td width="20%" class="<?= $stream['filter_nohashtags'] ? "selected " : "" ?>">
                        <div class="label">
                            <img src="<?= $assets_url."/images/hash.png" ?>">
                            <br>
                            <?= _("Ohne Hashtags") ?>
                        </div>
                        <? $label = _("Folgende Hashtags dürfen nicht in den Blubberpostings des Streams vorkommen.") ?>
                        <div>
                        <textarea name="filter_nohashtags" rows="6" style="width: 98%; max-width: 220px;"
                                  aria-label="<?= $label ?>" title="<?= $label ?>"
                                  placeholder="<?= _("z.B. #catcontent") ?>"
                                  class="selector"
                                  ><?= $stream['filter_nohashtags'] ? htmlReady("#".implode(" #", $stream['filter_nohashtags'])) : "" ?></textarea>
                        </div>
                        <div class="checkicons">
                            <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "uncheck text-bottom"]) ?>
                            <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "check text-bottom"]) ?>
                        </div>
                        <input type="checkbox" name="filter_nohashtags_check" id="filter_nohashtags_check" onChange="jQuery(this).closest('td').toggleClass('selected');" value="1"<?= $stream['filter_nohashtags'] ? " checked" : "" ?>>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <footer>
        <?= \Studip\Button::createAccept(_("Speichern"), []) ?>
    </footer>
</form>

<?php
$sidebar = Sidebar::get();
$sidebar->setImage("sidebar/blubber-sidebar.png");
$streamAvatar = StreamAvatar::getAvatar($stream->getId());
if ($streamAvatar->is_customized()) {
    $sidebar->setContextAvatar($streamAvatar);
}

$actions = new ActionsWidget();
if (!$stream->isNew()) {
    $actions->addLink(_("Diesen Stream löschen"), PluginEngine::getURL($plugin, [], 'streams/delete/'.$stream->getId()), Icon::create("trash", "clickable"), ['onclick' => "return window.confirm('"._("Wirklich löschen?")."');"]);
}
$sidebar->addWidget($actions);
