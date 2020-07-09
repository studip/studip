<? if ($document_types) : ?>
    <form class="default" method="post" data-dialog="size=auto"
          action="<?= $controller->link_for('library_file/create/' . htmlReady($folder_id)) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <label>
            <?= _('Von welchem Dokumenttyp ist der Bibliothekseintrag?') ?>
            <select name="document_type">
                <option value=""><?= _('Bitte wählen') ?></option>
                <? foreach ($document_types as $type) : ?>
                    <?
                    $display_name = $type['display_name'][$user_language] ?: $type['name'];
                    ?>
                    <option value="<?= htmlReady($type['name']) ?>"><?= htmlReady($display_name) ?></option>
                <? endforeach ?>
            </select>
        </label>
        <div data-dialog-button>
            <?= \Studip\Button::create(_('Auswählen')) ?>
        </div>
    </form>
<?endif ?>
