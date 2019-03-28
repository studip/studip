<? $perm_dokumente = isset($perm_dokumente) ? $perm_dokumente : true; ?>
<fieldset>
    <legend><?= _('Referenzierte Dokumente und Materialien') ?></legend>
    <? if ($perm_dokumente) : ?>
        <div class="mvv-item-list-properties" style="display: none;">
            <div class="mvv-property-de"></div>
            <div class="mvv-property-en"></div>
        </div>
        <div>
            <?= $search_dokumente['html']->render(); ?>
            <? if (Request::submitted('search_dokumente')) : ?>
                <?= Icon::create(
                    'refresh',
                    Icon::ROLE_CLICKABLE,
                    [
                        'name'       => 'reset_dokumente',
                        'data-qs_id' => $search_dokumente['id']
                    ]
                )->asInput(); ?>
            <? else : ?>
                <?= Icon::create(
                    'search',
                    Icon::ROLE_CLICKABLE,
                    [
                        'name'         => 'search_dokumente',
                        'data-qs_id'   => $search_dokumente['id'],
                        'data-qs_name' => $search_dokumente['html']->getId(),
                        'class'        => 'mvv-qs-button'
                    ]
                )->asInput(); ?>
            <? endif; ?>
        </div>
    <? endif; ?>
    <ul id="dokumente_target" class="mvv-assigned-items mvv-with-properties mvv-dokumente sortable">
        <? if ($perm_dokumente) : ?>
            <li class="mvv-item-list-placeholder"<?= (empty($dokumente) ? '' : ' style="display: none;"') ?>><?= _('Fügen Sie Dokumente und Materialien hinzu.') ?></li>
        <? elseif (empty($dokumente)) : ?>
            <li class="mvv-item-list-placeholder"><?= _('Es wurden keine Dokumente zugeordnet.') ?></li>
        <? endif; ?>
        <? foreach ($dokumente as $dokument) : ?>
            <? if ($perm_dokumente) : ?>
                <li id="dokumente_<?= $dokument->dokument_id ?>" class="sort_items">
                    <div style="flex: 9;">
                        <?= htmlReady($dokument->document->isI18nField('name')
                            ? $dokument->document->name->original()
                            : $dokument->document->name) ?>
                    </div>
                    <div style="flex: 1; text-align: right;" class="mvv-item-list-buttons">
                        <a href="#" class="mvv-item-remove">
                            <?= Icon::create(
                                'trash',
                                Icon::ROLE_CLICKABLE,
                                ['title' => _('Dokument entfernen')]
                            )->asImg(); ?>
                        </a>
                        <a href="#" class="mvv-item-edit-properties">
                            <?= Icon::create(
                                'edit',
                                Icon::ROLE_CLICKABLE,
                                ['title' => _('Kommentar bearbeiten')]
                            )->asImg(); ?>
                        </a>
                    </div>
                    <fieldset class="mvv-item-document-comments" style="display: none;">
                        <label><?= _('Anmerkungen/Kommentare') ?>
                            <?= MvvI18N::textarea(
                                'beschreibung',
                                $dokument->kommentar,
                                ['class' => 'add_toolbar ui-resizable wysiwyg']
                            ) ?>
                        </label>
                        <?= _('Die Änderungen werden erst gespeichert, wenn das Hauptformular gespeichert wurde!') ?>
                    </fieldset>
                    <input type="hidden" name="dokumente_items[]" value="<?= $dokument->dokument_id ?>">
                </li>
            <? else : ?>
                <li id="dokumente_<?= $dokument->dokument_id ?>">
                    <div style="flex: 1;">
                        <?= htmlReady($dokument->document->isI18nField('name')
                            ? $dokument->document->name->original()
                            : $dokument->document->name) ?>
                    </div>
                    <input type="hidden" name="dokumente_items[]" value="<?= $dokument->dokument_id ?>">
                </li>
            <? endif; ?>
        <? endforeach; ?>
    </ul>
    <? if ($perm_dokumente) : ?>
        <div id="dokumente_edit-form-new">
            <?= _('Neues Dokument anlegen und hinzufügen') ?>
            <a data-dialog href="<?= URLHelper::getLink($controller->url_for('materialien/dokumente/dokument/')) ?>">
                <?= Icon::create(
                    'add',
                    Icon::ROLE_CLICKABLE,
                    ['title' => _('Neues Dokument anlegen')]
                )->asImg(); ?>
            </a>
        </div>
        <div style="padding-top: 15px; width: 100%; max-width: 48em;">
            <?= _('Die Reihenfolge der zugeordneten Dokumente kann durch Anklicken und Ziehen geändert werden. Für die Zuordnung eines Dokumentes kann ein zusätzlicher Text angegeben werden.') ?>
        </div>
    <? endif; ?>
</fieldset>
