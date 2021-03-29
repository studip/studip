<form class="default" method="get" action="<?= $controller->link_for('files_dashboard/search') ?>">
    <?= $this->render_partial('files_dashboard/_input-group-search') ?>
</form>

<? if ($all_files) : ?>
    <?
    $tfoot_link = [
        'text' => sprintf(
            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $all_files_c),
            $all_files_c
        ),
        'href' => $controller->link_for('files/overview', ['view' => 'all_files'])
    ];
    ?>
    <div class="vue-file-table"
         data-topfolder="<?= htmlReady(json_encode($vue_topfolder)) ?>"
         data-files="<?= htmlReady(json_encode($all_files)) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <files-table :showdownloads="<?= $show_download_column ? "true" : "false" ?>"
                     :files="files"
                     :topfolder="topfolder"
                     enable_table_filter="false"
                     table_title="<?= _('Alle Dateien') ?>"
                     :show_bulk_actions="false"
                     :tfoot_link="<?= htmlReady(json_encode($tfoot_link)) ?>"
        ></files-table>
    </div>
<? endif ?>

<? if ($uploaded_files) : ?>
    <?
    $tfoot_link = [
        'text' => sprintf(
            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $uploaded_files_c),
            $uploaded_files_c
        ),
        'href' => $controller->link_for('files/overview', ['view' => 'my_uploaded_files'])
    ];
    ?>
    <div class="vue-file-table"
         data-topfolder="<?= htmlReady(json_encode($vue_topfolder)) ?>"
         data-files="<?= htmlReady(json_encode($uploaded_files)) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <files-table :showdownloads="<?= $show_download_column ? "true" : "false" ?>"
                     :files="files"
                     :topfolder="topfolder"
                     enable_table_filter="false"
                     table_title="<?= _('Persönlichr Dateibereich') ?>"
                     :show_bulk_actions="false"
                     :tfoot_link="<?= htmlReady(json_encode($tfoot_link)) ?>"
        ></files-table>
    </div>
<? endif ?>

<? if ($public_files) : ?>
    <?
    $tfoot_link = [
        'text' => sprintf(
            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $public_files_c),
            $public_files_c
        ),
        'href' => $controller->link_for('files/overview', ['view' => 'my_public_files'])
    ];
    ?>
    <div class="vue-file-table"
         data-topfolder="<?= htmlReady(json_encode($vue_topfolder)) ?>"
         data-files="<?= htmlReady(json_encode($public_files)) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <files-table :showdownloads="<?= $show_download_column ? "true" : "false" ?>"
                     :files="files"
                     :topfolder="topfolder"
                     enable_table_filter="false"
                     table_title="<?= _('Meine öffentlichen Dateien') ?>"
                     :show_bulk_actions="false"
                     :tfoot_link="<?= htmlReady(json_encode($tfoot_link)) ?>"
        ></files-table>
    </div>
<? endif ?>

<? if ($uploaded_unlic_files) : ?>
    <?
    $tfoot_link = [
        'text' => sprintf(
            ngettext('Insgesamt %d Datei', 'Insgesamt %d Dateien', $uploaded_unlic_files_c),
            $uploaded_unlic_files_c
        ),
        'href' => $controller->link_for('files/overview', ['view' => 'my_uploaded_files_unknown_license'])
    ];
    ?>
    <div class="vue-file-table"
         data-topfolder="<?= htmlReady(json_encode($vue_topfolder)) ?>"
         data-files="<?= htmlReady(json_encode($uploaded_unlic_files)) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <files-table :showdownloads="<?= $show_download_column ? "true" : "false" ?>"
                     :files="files"
                     :topfolder="topfolder"
                     enable_table_filter="false"
                     table_title="<?= _('Meine Dateien mit ungeklärter Lizenz') ?>"
                     :show_bulk_actions="false"
                     :tfoot_link="<?= htmlReady(json_encode($tfoot_link)) ?>"
        ></files-table>
    </div>
<? endif ?>

<? if ($no_files) : ?>
    <?= MessageBox::info(_('Es sind keine Dateien vorhanden, die für Sie zugänglich sind!')) ?>
<? endif ?>
