<form class="default" action="<?= $controller->url_for('/add_dokument', $origin,  $range_type, $range_id, $mvvfile_id) ?>" method="post" data-dialog="size=auto">
    <input type="hidden" name="mvvfile_id" id="mvvfile_id" value="<?= htmlReady($mvvfile_id) ?>">
    <input type="hidden" name="range_id" id="range_id" value="<?= htmlReady($range_id) ?>">
    <input type="hidden" name="range_type" id="range_type" value="<?= htmlReady($range_type) ?>">



    <label>
        <?= _('Jahr') ?>
        <input name="doc_year" type="text" value="<?= htmlReady($doc_year) ?>"<?= $perm->disable('year') ?>>
    </label>

    <input type="hidden" name="doc_type" value="<?= $doc_type ?>">
    <label>
        <?= _('Art der Datei') ?>
        <select name="doc_type"<?= $perm->haveFieldPerm('type') ? '' : ' disable' ?>>
        <? foreach ($GLOBALS['MVV_DOCUMENTS']['TYPE']['values'] as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $doc_type ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <table class="default">
    <? foreach($GLOBALS['MVV_LANGUAGES']['values'] as $key => $entry) : ?>
        <tr>
            <td rowspan="2">
                <?= Assets::img('languages/lang_' . mb_strtolower($key) . '.gif') ?>
            </td>
            <td>
                <label>
                    <?= _('Angezeigter Name des Dokuments') ?>
                    <input name="doc_displayname_<?= $key; ?>" type="text" value="<?= (!$documents || !key_exists($key, $documents))  ? '' : htmlReady($documents[$key]->name) ?>"<?= $perm->disable('name') ?>>
                </label>
            </td>
        </tr>
        <tr>
            <td>
                <div class="attachments" style="<?= (!$documents || !key_exists($key, $documents))  ? '' : 'display: none;'?>">
                    <span style="cursor:pointer;" onClick="$('#fileselector_<?= $key; ?>').toggle();$(this).toggle();">
                        <?= Icon::create('file+add', Icon::ROLE_CLICKABLE, ['title' => _("Datei hinzufügen"), 'class' => 'text-bottom']); ?>
                        <?= _("Datei hinzufügen") ?>
                    </span>
                    <div id="fileselector_<?= $key; ?>" style="display:none;">
                        <ul class="stgfiles list-unstyled">
                            <li style="display: none;" class="stgfile">
                                <input type="hidden" name="document_id" id="document_id" value="<?= htmlReady($document_id) ?>">
                                <span class="icon"></span>
                                <span class="name"></span>
                                <span class="size"></span>
                                <a class="remove_attachment"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                            </li>
                        </ul>
                        <div id="statusbar_container">
                            <div class="statusbar" style="display: none;">
                                <div class="progress"></div>
                                <div class="progresstext">0%</div>
                            </div>
                        </div>
                        <label id="upload_chooser" style="cursor: pointer;">
                            <input type="file" id="fileupload" multiple onChange="STUDIP.MVV.Document.upload_from_input(this, '<?= $key; ?>');" style="display: none;">
                            <?= Icon::create('upload', 'clickable', ['title' => _("Datei hochladen"), 'class' => "text-bottom"])->asImg(20) ?>
                            <?= _("Datei hochladen") ?>
                        </label>
                        <br>
                        <b><?= _('oder'); ?></b>
                        <label>
                            <?= _('Link hinzufügen') ?>
                            <input name="doc_url_<?= $key; ?>" type="text" value="" placeholder="https://...">
                        </label>
                        <div id="upload_finished" style="display: none"><?= _("wird verarbeitet") ?></div>
                        <div id="upload_received_data" style="display: none"><?= _("gespeichert") ?></div>
                    </div>
                </div>
                <div id="fileviewer_<?= $key; ?>">
                    <ul class="stgfiles list-unstyled">
                    <? if ($documents && key_exists($key, $documents)): ?>
                        <li class="stgfile">
                            <input type="hidden" name="document_id" id="document_id" value="<?= htmlReady($documents[$key]->fileref_id) ?>">
                            <span class="icon"><?= Icon::create('file', Icon::ROLE_INFO, ['class' => 'text-bottom']); ?></span>
                            <span class="name"><?= htmlReady($documents[$key]->filename) ?></span>
                            <span class="size"></span>
                            <a class="remove_attachment"><?= Icon::create('trash', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                        </li>
                    <? endif; ?>
                    </ul>
                </div>
            </td>
        </tr>
    <? endforeach; ?>
    </table>

    <label>
        <?= _('Kategoriezuordnung') ?>
        <select name="doc_cat">
        <? foreach ($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'] as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == $doc_cat ? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <label for="mvv-files-tags">
        <?= _('Schlagwörter') ?>
        <select id="mvv-files-tags" multiple name="doc_tags[]">
            <option value=""></option>
        <? foreach ($GLOBALS['MVV_DOCUMENTS']['TAG']['values'] as $key => $entry) : ?>
            <option value="<?= $key ?>"<?= $key == in_array($key, explode(';', $doc_tags))? ' selected' : '' ?>><?= htmlReady($entry['name']) ?></option>
        <? endforeach; ?>
        </select>
    </label>

    <label>
        <input name="doc_extvisible" type="checkbox" value="1" <?= $doc_extvisible?'checked':''; ?>>
        <?= _('Sichtbarkeit nach außen') ?>
    </label>

    <?= CSRFProtection::tokenTag(); ?>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store_document') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>

</form>

<script>
    $(document).ready(function() {
        $('#mvv-files-tags').select2({
            placeholder: '<?= _('Schlagwörter wählen') ?>'
        });
    });
</script>
