<form class="default" name="write_message" action="<?= URLHelper::getLink("dispatch.php/messages/send") ?>" method="post" style="margin-left: auto; margin-right: auto;" data-dialog data-secure="#adressees > li:eq(1), .files > li:eq(1)">
    <input type="hidden" name="message_id" id="message_id" value="<?= htmlReady($default_message->id) ?>">
    <input type="hidden" name="answer_to" value="<?= htmlReady($answer_to) ?>">
    <fieldset>
        <legend><?= _('Neue Nachricht') ?></legend>
    <div>
        <label for="user_id_1"><?= _("An") ?></label>
        <ul class="list-csv" id="adressees">
            <li id="template_adressee" style="display: none;" class="adressee">
                <input type="hidden" name="message_to[]" value="">
                <span class="visual"></span>
                <a class="remove_adressee"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
            </li>
            <? foreach ($default_message->getRecipients() as $user) : ?>
            <li class="adressee">
                <input type="hidden" name="message_to[]" value="<?= htmlReady($user['user_id']) ?>">
                <span class="visual">
                    <?= htmlReady($user['fullname']) ?>
                </span>
                <a class="remove_adressee"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
            </li>
            <? endforeach ?>
        </ul>
        <div class="message-search-wrapper">
        <?= QuickSearch::get('user_id', new StandardSearch('user_id'))
            ->fireJSFunctionOnSelect('STUDIP.Messages.add_adressee')
            ->withButton()
            ->render();

        $mps = MultiPersonSearch::get('add_adressees')
           ->setLinkText(_('Mehrere Adressaten hinzufügen'))
            //->setDefaultSelectedUser($defaultSelectedUser)
            ->setTitle(_('Mehrere Adressaten hinzufügen'))
            ->setExecuteURL($controller->url_for('messages/write'))
            ->setJSFunctionOnSubmit('STUDIP.Messages.add_adressees')
            ->setSearchObject($this->mp_search_object);
        foreach (Statusgruppen::findContactGroups() as $group) {
            $mps->addQuickfilter(
                $group['name'],
                $group->members->pluck('user_id')
            );
        }
        echo $mps->render();
        ?>
        </div>
        <script>
            STUDIP.MultiPersonSearch.init();
        </script>
    </div>
    <div>
        <label>
            <?= _("Betreff") ?>
            <input type="text" name="message_subject" style="width: 100%" required value="<?= htmlReady($default_message['subject']) ?>">
        </label>
    </div>
    <div>
        <label>
            <?= _("Nachricht") ?>
            <textarea style="width: 100%; height: 200px;" name="message_body" class="add_toolbar wysiwyg"><?= wysiwygReady($default_message['message'],false) ?></textarea>
        </label>
    </div>
    <div>
        <ul class="message-options">
        <? if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']): ?>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('attachments'); return false;">
                    <?= Icon::create('staple', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Anhänge") ?></strong>
                </a>
            </li>
        <? endif; ?>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('tags'); return false;">
                    <?= Icon::create('star', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Schlagworte") ?></strong>
                </a>
            </li>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('settings'); return false;">
                    <?= Icon::create('admin', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Optionen") ?></strong>
                </a>
            </li>
            <? if (!\Studip\Markup::editorEnabled()) : ?>
            <li>
                <a href="" onClick="STUDIP.Messages.toggleSetting('preview'); STUDIP.Messages.previewComposedMessage(); return false;">
                    <?= Icon::create('visibility-visible', 'clickable')->asImg(40) ?>
                    <br>
                    <strong><?= _("Vorschau") ?></strong>
                </a>
            </li>
            <? endif ?>
        </ul>
    </div>

<? if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']): ?>
    <div id="attachments" style="<?= $default_attachments ? '' : 'display: none;'?>">
        <?= _("Anhänge") ?>
        <div>
            <ul class="files">
                <li style="display: none;" class="file">
                    <span class="icon"></span>
                    <span class="name"></span>
                    <span class="size"></span>
                    <a class="remove_attachment"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                </li>
                <? if ($default_attachments) : ?>
                    <? foreach ($default_attachments as $a) : ?>
                    <li class="file" data-document_id="<?=$a['document_id']?>">
                    <span class="icon"><?=$a['icon']?></span>
                    <span class="name"><?=$a['name']?></span>
                    <span class="size"><?=$a['size']?></span>
                    <a class="remove_attachment"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                    </li>
                    <? endforeach ?>
                <? endif ?>
            </ul>
            <div id="statusbar_container">
                <div class="statusbar" style="display: none;">
                    <div class="progress"></div>
                    <div class="progresstext">0%</div>
                </div>
            </div>
            <label style="cursor: pointer;">
                <input type="file" id="fileupload" multiple onChange="STUDIP.Messages.upload_from_input(this);" style="display: none;">
                <?= Icon::create('upload', 'clickable', ['title' => _("Datei hochladen"), 'class' => "text-bottom"])->asImg(20) ?>
                <?= _("Datei hochladen") ?>
            </label>

            <div id="upload_finished" style="display: none"><?= _("wird verarbeitet") ?></div>
            <div id="upload_received_data" style="display: none"><?= _("gespeichert") ?></div>
        </div>
    </div>
<? endif; ?>
    <div id="tags" style="<?= Request::get("default_tags") ? "" : 'display: none; ' ?>">
        <label>
            <?= _("Schlagworte") ?>
            <input type="text" name="message_tags" style="width: 100%" placeholder="<?= _("z.B. klausur termin statistik etc.") ?>" value="<?= htmlReady(Request::get("default_tags")) ?>">
        </label>
    </div>
    <div id="settings" style="display: none;">
        <?= _("Optionen") ?>
        <label for="message_mail">
            <input type="checkbox" name="message_mail" id="message_mail" value="1"<?= $mailforwarding ? " checked" : "" ?>>
            <?= _("Immer per E-Mail weiterleiten") ?>
        </label>

        <label for="show_adressees">
            <input type="checkbox" name="show_adressees" id="show_adressees" value="1"<?= $show_adressees ? " checked" : "" ?>>
            <?= _("Sollen die Adressaten für die Empfänger sichtbar sein?") ?>
        </label>
    </div>
    </fieldset>

    <? if (!\Studip\Markup::editorEnabled()) : ?>
    <div id="preview" style="display: none;">
        <?= _("Vorschau") ?>
        <p class="message_body"></p>
    </div>
    <? endif ?>

    <footer data-dialog-button>
        <?= \Studip\Button::create(_('Abschicken'), null, ['onclick' => "STUDIP.Messages.checkAdressee();"]) ?>
    </footer>

</form>
