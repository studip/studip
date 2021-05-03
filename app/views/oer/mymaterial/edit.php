<form action="<?= $controller->edit($material->isNew() ? '' : $material) ?>"
      method="post"
      class="default"
      onsubmit="$(window).off('beforeunload')"
      data-secure
      enctype="multipart/form-data">

    <div class="oercampus_editmaterial">
        <fieldset>
            <legend><?= _('Grunddaten') ?></legend>

            <label>
                <?= _('Name') ?>
                <input type="text"
                       name="data[name]"
                       class="oername"
                       value="<?= htmlReady($material['name'] ?: $template['name']) ?>"
                       @keyup="editName"
                       maxlength="64">
            </label>

            <? if ($template['tmp_file']) : ?>
                <input type="hidden" name="logo_tmp_file" value="<?= htmlReady($template['logo_tmp_file']) ?>">
            <? else : ?>
                <div>
                    <?= _('Vorschau') ?>
                </div>

                <div class="hgroup" @drop.prevent="dropImage">
                    <label for="oer_logo_uploader">
                        <article class="contentbox" :title="name">
                            <header>
                                <h1>
                                    <studip-icon shape="file"
                                                 role="clickable"
                                                 size="20"
                                                 class="text-bottom"></studip-icon>
                                    {{ name }}
                                </h1>
                            </header>
                            <div class="image"
                                 :style="'background-image: url(' + logo_url + ');' + (!customlogo ? ' background-size: 60% auto;': '')"></div>
                        </article>
                    </label>

                    <div>
                        <label class="file-upload logo_file"
                               data-oldurl="<?= htmlReady($material->getLogoURL()) ?>"
                               data-customlogo="<?= $material['front_image_content_type'] ? 1 : 0 ?>">
                            <?= _('Vorschau-Bilddatei (optional)') ?>
                            <input type="file"
                                   name="image"
                                   id="oer_logo_uploader"
                                   accept="image/*"
                                   @change="editImage">
                        </label>

                        <? if ($material['front_image_content_type']) : ?>
                            <label>
                                <input type="checkbox" name="delete_front_image" value="1">
                                <?= _('Logo löschen') ?>
                            </label>
                        <? endif ?>
                    </div>

                </div>

            <? endif ?>


            <? if ($template['tmp_file']) : ?>
                <input type="hidden" name="tmp_file" value="<?= htmlReady($template['tmp_file']) ?>">
                <input type="hidden" name="mime_type" value="<?= htmlReady($template['mime_type']) ?>">
                <input type="hidden" name="filename" value="<?= htmlReady($template['filename']) ?>">
            <? else : ?>

                <label class="file drag-and-drop"
                       data-filename="<?= htmlReady($material['filename']) ?>"
                       data-filesize="<?= htmlReady(!$material->isNew() ? filesize($material->getFilePath()) : "") ?>"
                       @drop.prevent="dropFile">
                    <?= _('Datei (gerne auch eine ZIP-Datei) auswählen') ?>
                    <input type="file" name="file" id="oer_file" @change="editFile">
                    <div v-if="filename">
                        <span>{{ filename }}</span>
                        <span>{{ filesize }}</span>
                    </div>
                </label>
            <? endif ?>

            <label>
                <?= _('Beschreibung') ?>
                <textarea
                        name="data[description]"><?= htmlReady($material['description'] ?: $template['description']) ?></textarea>
            </label>

            <label>
                <input type="hidden" name="data[draft]" value="0">
                <input type="checkbox" name="data[draft]" value="1"<?= $material['draft'] ? " checked" : "" ?>>
                <?= _('Entwurf (nicht veröffentlicht)') ?>
            </label>

            <label>
                <?= _('Kategorie') ?>
                <select name="data[category]">
                    <? if ($material->isNew()) : ?>
                        <option value="auto"><?= _('Automatisch erkennen') ?></option>
                    <? endif ?>
                    <option value="audio"<?= $material['category'] === "audio" ? " selected" : "" ?>>
                        <?= _('Audio') ?>
                    </option>
                    <option value="video"<?= $material['category'] === "video" ? " selected" : "" ?>>
                        <?= _('Video') ?>
                    </option>
                    <option value="presentation"<?= $material['category'] === "presentation" ? " selected" : "" ?>>
                        <?= _('Folien') ?>
                    </option>
                    <option value="elearning"<?= $material['category'] === "elearning" ? " selected" : "" ?>>
                        <?= _('Lernmodule') ?>
                    </option>
                    <option value=""<?= !$material['category'] && !$material->isNew() ? " selected" : "" ?>
                            title="<?= _('Fehlt eine Kategorie? Kein Problem, arbeiten Sie stattdessen mit Schlagwörtern. Die sind viel flexibler.') ?>">
                        <?= _('Ohne Kategorie') ?>
                    </option>
                </select>
            </label>

            <label>
                <?= _('Vorschau-URL (optional)') ?>
                <input type="text" name="data[player_url]"
                       value="<?= htmlReady($material['player_url'] ?: $template['player_url']) ?>">
            </label>

            <? if (!$material->isNew()) : ?>
                <div>
                    <h4><?= _('Autoren') ?></h4>
                    <ul class="clean autoren<?= count($material->users) > 1 ? " multiple" : "" ?>">
                        <? foreach ($material->users as $materialuser) : ?>
                            <li>
                                <? if ($materialuser['external_contact']) : ?>
                                    <? $user = $materialuser['oeruser'] ?>
                                    <? $image = $user['avatar'] ?>
                                    <label>
                                        <? if (count($material->users) > 1) : ?>
                                            <input type="checkbox" name="remove_users[]"
                                                   value="1_<?= htmlReady($user->getId()) ?>">
                                        <? endif ?>
                                        <div>
                                            <span class="avatar" style="background-image: url('<?= $image ?>');"></span>
                                            <span class="author_name">
                                            <?= htmlReady($user['name']) ?>
                                        </span>
                                            <? if (count($material->users) > 1) : ?>
                                                <?= Icon::create('trash')->asImg(16, ['class' => "text-bottom", 'title' => _('Person als Autor entfernen.')]) ?>
                                            <? endif ?>
                                        </div>
                                    </label>
                                <? else : ?>
                                    <? $user = User::find($materialuser['user_id']) ?>
                                    <? $image = Avatar::getAvatar($materialuser['user_id'])->getURL(Avatar::SMALL) ?>
                                    <label>
                                        <? if (count($material->users) > 1) : ?>
                                            <input type="checkbox" name="remove_users[]"
                                                   value="0_<?= htmlReady($user->getId()) ?>">
                                        <? endif ?>
                                        <div>
                                            <span class="avatar" style="background-image: url('<?= $image ?>');"></span>
                                            <span class="author_name">
                                                <?= htmlReady($user ? $user->getFullName() : _('unbekannt')) ?>
                                            </span>
                                            <? if (count($material->users) > 1) : ?>
                                                <?= Icon::create('trash')->asImg(['class' => "text-bottom", 'title' => _('Person als Autor/Autorin entfernen.')]) ?>
                                            <? endif ?>
                                        </div>
                                    </label>
                                <? endif ?>
                            </li>
                        <? endforeach ?>
                        <li>
                            <quicksearch name="new_user"
                                         searchtype="<?= htmlReady($usersearch) ?>"
                                         placeholder="<?= _('Person hinzufügen') ?>"></quicksearch>
                        </li>
                    </ul>
                </div>
            <? endif ?>


            <div class="oer_tags_container">
                <?= _('Themen (am besten mindestens 5)') ?>
                <?
                $tags = [];
                foreach ($material->getTopics() as $tag) {
                    $tags[] = $tag['name'];
                }
                foreach ((array) $template['tags'] as $tag) {
                    $tags[] = $tag;
                }
                ?>

                <ul class="clean oer_tags" data-defaulttags="<?= htmlReady(json_encode($tags)) ?>">
                    <li v-for="(tag, index) in displayTags" :key="index">
                        #
                        <quicksearch name="tags[]"
                                     searchtype="<?= htmlReady($tagsearch) ?>"
                                     v-model="tags[index]"
                                     :autocomplete="true"
                        ></quicksearch>
                        <a href="#"
                           @click.prevent="removeTag(index)"
                           title="<?= _('Thema aus der Liste streichen') ?>">
                            <studip-icon shape="trash" role="clickable" size="20" class="text-bottom"></studip-icon>
                        </a>

                    </li>
                </ul>
                <a href="#" @click.prevent="addTag">
                    <studip-icon shape="add" role="clickable" size="20" class="text-bottom"></studip-icon>
                    <?= _('Thema hinzufügen') ?>
                </a>
            </div>

            <div style="margin-top: 13px; max-width: 682px;">
                <?= _('Niveau') ?>

                <input type="hidden" id="difficulty_start" name="data[difficulty_start]"
                       value="<?= htmlReady($material['difficulty_start']) ?>">
                <input type="hidden" id="difficulty_end" name="data[difficulty_end]"
                       value="<?= htmlReady($material['difficulty_end']) ?>">

                <div style="display: flex; justify-content: space-between; font-size: 0.8em; color: grey;">
                    <div><?= _('Kindergarten') ?></div>
                    <div><?= _('Aktuelle Forschung') ?></div>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <? for ($i = 1; $i <= 12; $i++) : ?>
                        <div><?= ($i < 10 ? "&nbsp;" : "") . $i ?></div>
                    <? endfor ?>
                </div>
                <div id="difficulty_slider_edit" style="margin-left: 5px; margin-right: 9px;"></div>
            </div>

            <? if ($template['module_id']) : ?>
                <input type="hidden"
                       name="module_id"
                       value="<?= htmlReady($template['module_id']) ?>">
            <? endif ?>

        </fieldset>

        <? if (!Config::get()->OER_DISABLE_LICENSE) : ?>
            <? $license = $material->isNew()
                ? License::findDefault()
                : $material->license;
            ?>
            <fieldset class="oer_license_selector">
                <legend><?= _('Lizenz') ?></legend>
                <?=
                    _('Ich erkläre mich bereit, dass meine Lernmaterialien unter der angegebenen Lizenz an alle Nutzenden freigegeben werden. Ich bestätige zudem, dass ich das Recht habe, diese Dateien frei zu veröffentlichen, weil entweder ich selbst sie angefertigt habe, oder sie von anderen Quellen mit kompatibler Lizenz stammen.')
                ?>

                <div>
                    <select class="licenses_selector" name="data[license_identifier]">
                        <? foreach (License::findBySQL("1 ORDER BY name ASC") as $l) : ?>
                        <option value="<?= htmlReady($l->id) ?>" <?= $l->id === $license->id ? " selected" : "" ?>>
                            <?= htmlReady($l['name']) ?>
                        </option>
                        <? endforeach ?>
                    </select>
                </div>
            </fieldset>

        <? endif ?>
        <? if ($template['redirect_url']) : ?>
            <input type="hidden"
                   name="redirect_url"
                   value="<?= htmlReady($template['redirect_url']) ?>">
        <? endif ?>
    </div>

    <div data-dialog-button>
        <?= \Studip\Button::create($material->isNew() ? _('Hochladen') : _('Speichern'), "save") ?>
    </div>
</form>
