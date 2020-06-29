<form class="default" method="get" action="<?= $controller->link_for('files_dashboard/search') ?>">
    <?= $this->render_partial('files_dashboard/_input-group-search') ?>
</form>

<? if ($all_file_refs) : ?>
    <table class="default documents sortable-table" data-sortlist="[[4, 1]]" data-shiftcheck
           data-table_id="new_files">
        <caption><?= _('Alle Dateien') ?></caption>
        <?= $this->render_partial(
            'files/_files_thead.php',
            [
                'show_bulk_checkboxes' => false
            ]
        ) ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <a href="<?= $controller->link_for('files/overview', ['view' => 'all_files']) ?>">
                        <?= htmlReady(sprintf(
                            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $all_files_c),
                            $all_files_c
                        )) ?>
                    </a>
                </td>
            </tr>
        </tfoot>
        <tbody class="files">
            <? foreach ($all_file_refs as $file_ref) : ?>
                <?
                $folder = $file_ref->folder;
                if ($folder instanceof Folder) {
                    $folder = $folder->getTypedFolder();
                }
                ?>
                <?= $this->render_partial('files/_fileref_tr', [
                    'file_ref'       => $file_ref,
                    'current_folder' => $folder,
                    'controllerpath' => $controllerpath,
                    'show_bulk_checkboxes' => false
                ]) ?>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<? if ($uploaded_file_refs) : ?>
    <table class="default documents sortable-table" data-sortlist="[[4, 1]]" data-shiftcheck
           data-table_id="public_files">
        <caption><?= _('Meine Dateien') ?></caption>
        <?= $this->render_partial(
            'files/_files_thead.php',
            [
                'show_bulk_checkboxes' => false
            ]
        ) ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <a href="<?= $controller->link_for('files/overview', ['view' => 'my_uploaded_files']) ?>">
                        <?= htmlReady(sprintf(
                            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $uploaded_files_c),
                            $uploaded_files_c
                        )) ?>
                    </a>
                </td>
            </tr>
        </tfoot>
        <tbody class="files">
            <? foreach ($uploaded_file_refs as $file_ref) : ?>
                <?
                $folder = $file_ref->folder;
                if ($folder instanceof Folder) {
                    $folder = $folder->getTypedFolder();
                }
                ?>
                <?= $this->render_partial('files/_fileref_tr', [
                    'file_ref'       => $file_ref,
                    'current_folder' => $folder,
                    'controllerpath' => $controllerpath,
                    'show_bulk_checkboxes' => false
                ]) ?>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<? if ($public_file_refs) : ?>
    <table class="default documents sortable-table" data-sortlist="[[4, 1]]" data-shiftcheck
           data-table_id="public_files">
        <caption><?= _('Meine öffentlichen Dateien') ?></caption>
        <?= $this->render_partial(
            'files/_files_thead.php',
            [
                'show_bulk_checkboxes' => false
            ]
        ) ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <a href="<?= $controller->link_for('files/overview', ['view' => 'my_public_files']) ?>">
                        <?= htmlReady(sprintf(
                            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $public_files_c),
                            $public_files_c
                        )) ?>
                    </a>
                </td>
            </tr>
        </tfoot>
        <tbody class="files">
            <? foreach ($public_file_refs as $file_ref) : ?>
                <?
                $folder = $file_ref->folder;
                if ($folder instanceof Folder) {
                    $folder = $folder->getTypedFolder();
                }
                ?>
                <?= $this->render_partial('files/_fileref_tr', [
                    'file_ref'       => $file_ref,
                    'current_folder' => $folder,
                    'controllerpath' => $controllerpath,
                    'show_bulk_checkboxes' => false
                ]) ?>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<? if ($uploaded_unlic_file_refs) : ?>
    <table class="default documents sortable-table" data-sortlist="[[4, 1]]" data-shiftcheck
           data-table_id="public_files">
        <caption><?= _('Meine Dateien mit ungeklärter Lizenz') ?></caption>
            <?= $this->render_partial(
                'files/_files_thead.php',
                [
                    'show_bulk_checkboxes' => false
                ]
            ) ?>
        <tfoot>
            <tr>
                <td colspan="6">
                    <a href="<?= $controller->link_for('files/overview', ['view' => 'my_uploaded_files_unknown_license']) ?>">
                        <?= htmlReady(sprintf(
                            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $uploaded_unlic_files_c),
                            $uploaded_unlic_files_c
                        )) ?>
                    </a>
                </td>
            </tr>
        </tfoot>
        <tbody class="files">
            <? foreach ($uploaded_unlic_file_refs as $file_ref) : ?>
                <?
                $folder = $file_ref->folder;
                if ($folder instanceof Folder) {
                    $folder = $folder->getTypedFolder();
                }
                ?>
                <?= $this->render_partial('files/_fileref_tr', [
                    'file_ref'       => $file_ref,
                    'current_folder' => $folder,
                    'controllerpath' => $controllerpath,
                    'show_bulk_checkboxes' => false
                ]) ?>
            <? endforeach ?>
        </tbody>
    </table>
<? endif ?>

<? if ($no_files) : ?>
    <?= MessageBox::info(_('Es sind keine Dateien vorhanden, die für Sie zugänglich sind!')) ?>
<? endif ?>
