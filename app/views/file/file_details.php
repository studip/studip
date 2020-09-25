<div id="file_details_window">
    <?= $this->render_partial('file/_file_aside.php') ?>

    <div id="preview_container">
        <? if ($file_info_template instanceof Flexi_Template) : ?>
            <?= $file_info_template->render() ?>
        <? endif ?>
        <h3><?=_('Pfad')?></h3>
        <article>
            <? foreach (array_values($fullpath) as $i => $one_folder) : ?>
                <? if ($i): ?>/<? endif; ?>
                <a href="<?= FileManager::getFolderLink($one_folder) ?>">
                    <?= htmlReady($one_folder->name) ?>
                </a>
            <? endforeach; ?>
        </article>

        <? if ($file->getDescription()) : ?>
            <h3><?= _('Beschreibung') ?></h3>
            <article>
                <?= htmlReady($file->getDescription() ?: _('Keine Beschreibung vorhanden.'), true, true) ?>
            </article>
        <? endif ?>
        <?= Feedback::getHTML($file->getId(), 'FileRef') ?>
    </div>
</div>

<footer data-dialog-button>
    <?
    $file_action_buttons = $file->getInfoDialogButtons(compact('from_plugin'));
    ?>
    <? if ($previous_file_ref_id): ?>
        <?= Studip\LinkButton::create(
            _('<< Vorherige Datei'),
            $controller->url_for(
                "file/details/{$previous_file_ref_id}",
                ['from_plugin' => $from_plugin, 'file_navigation' => $include_navigation]
            ),
            ['data-dialog' => '']
        ) ?>
    <? endif ?>
    <? if ($next_file_ref_id): ?>
        <?= Studip\LinkButton::create(
            _('Nächste Datei >>'),
            $controller->url_for(
                "file/details/{$next_file_ref_id}",
                ['from_plugin' => $from_plugin, 'file_navigation' => $include_navigation]
            ),
            ['data-dialog' => '']
        ) ?>
    <? endif ?>
    <? foreach ($file_action_buttons as $button) : ?>
        <?= $button ?>
    <? endforeach ?>
</footer>
