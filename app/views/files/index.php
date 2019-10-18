<? if ($topFolder): ?>
<?php
if (!$controllerpath) {
    $controllerpath = 'files/index';
    if ($topFolder->range_type !== 'user') {
        $controllerpath = $topFolder->range_type . '/' . $controllerpath;
    }
}
$show_downloads = Config::get()->DISPLAY_DOWNLOAD_COUNTER === 'always';
?>

<form method="post" action="<?= $controller->link_for('file/bulk/' . $topFolder->getId()) ?>">
<?= CSRFProtection::tokenTag() ?>
<input type="hidden" name="parent_folder_id" value="<?= $topFolder->getId() ?>" >
<table class="default documents sortable-table" data-sortlist="[[2, 0]]" data-folder_id="<?= htmlReady($topFolder->getId()) ?>">
    <caption>
        <div class="caption-container">
            <?
                $breadcrumbs = [];
                $folder = $topFolder;
                do {
                    $breadcrumbs[] = $folder;
                } while ($folder = $folder->getParent());
                $breadcrumbs = array_reverse($breadcrumbs);
                $root_dir    = array_shift($breadcrumbs);
                $last_crumb  = end($breadcrumbs);
            ?>
            <div>
                <a href="<?= $controller->link_for($controllerpath . '/' . $root_dir->getId()) ?>" title="<?= _('Zum Hauptordner') ?>">
                    <?= Icon::create('folder-home-full', 'clickable')->asImg(30, ['class' => 'text-bottom']) ?>
                    <? if (count($breadcrumbs) == 0): ?><?= htmlReady($root_dir->name) ?><? endif ?>
                </a>
                <? if(!empty($breadcrumbs)): ?>
                    <? if (count($breadcrumbs) > 5): ?>/&hellip;<?
                        $breadcrumbs = array_slice($breadcrumbs, -5);
                        ?>
                    <? endif ?>

                    <? foreach ($breadcrumbs as $crumb): ?>
                        /<a href="<?= $controller->link_for($controllerpath . '/' . $crumb->getId()) ?>">
                            <?= htmlReady($crumb->name) ?>
                        </a>
                    <? endforeach ?>
                <? endif ?>
            </div>
        </div>
        <? if (is_object($last_crumb) && ($description_template = $last_crumb->getDescriptionTemplate())) : ?>
        <div style="font-size: small">
        <?= $description_template instanceof Flexi_Template ? $description_template->render() : (string)$description_template ?>
        <? endif; ?>
        </div>
    </caption>
    <?= $this->render_partial('files/_files_thead.php', compact('show_downloads')) ?>

    <tbody class="subfolders">
        <tr class="empty" data-sort-fixed <?= count($topFolder->getFiles()) + count($topFolder->getSubfolders()) > 0 ? ' style="display: none;"' : "" ?>>
            <td colspan="<?= $show_downloads ? 8 : 7 ?>">
                <?= _('Dieser Ordner ist leer') ?>
            </td>
        </tr>
    <? foreach ($topFolder->getSubfolders() as $folder): ?>
        <? if (!$folder->isVisible($GLOBALS['user']->id)) continue; ?>
        <?= $this->render_partial('files/_folder_tr', [
            'folder'             => $folder,
            'marked_element_ids' => $marked_element_ids,
            'controllerpath'     => $controllerpath,
            'show_downloads'     => $show_downloads,
        ]) ?>
    <? endforeach ?>
    </tbody>
    <tbody class="files">
    <? if (count($topFolder->getFiles()) && $topFolder->isReadable($GLOBALS['user']->id)) : ?>
        <? foreach ($topFolder->getFiles() as $file_ref) : ?>
            <?= $this->render_partial('files/_fileref_tr', [
                'file_ref'       => $file_ref,
                'current_folder' => $topFolder,
                'controllerpath' => $controllerpath,
                'show_downloads' => $show_downloads,
            ]) ?>
        <? endforeach; ?>
    <? endif; ?>
    </tbody>
    <? if ($GLOBALS['user']->id !== 'nobody') : ?>
        <tfoot>
            <tr>
                <td colspan="<?= $show_downloads ? 8 : 7 ?>">
                    <span class="multibuttons">
                        <?= Studip\Button::create(_('Herunterladen'), 'download', [
                            'data-activates-condition' => 'table.documents tr[data-permissions*=d] :checkbox:checked'
                        ]) ?>
                    <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                        <?= Studip\Button::create(_('Verschieben'), 'move', [
                            'data-dialog'              => '',
                            'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
                        ]) ?>
                    <? endif; ?>
                    <? if ($topFolder->isReadable($GLOBALS['user']->id)): ?>
                        <?= Studip\Button::create(_('Kopieren'), 'copy', [
                            'data-dialog'              => '',
                            'data-activates-condition' => 'table.documents tr[data-permissions*=r] :checkbox:checked'
                        ]) ?>
                    <? endif; ?>
                    <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                        <?= Studip\Button::create(_('Löschen'), 'delete', [
                            'data-confirm'             => _('Soll die Auswahl wirklich gelöscht werden?'),
                            'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
                        ]) ?>
                    <? endif; ?>
                    </span>

                <? if ($topFolder->isSubfolderAllowed($GLOBALS['user']->id)): ?>
                    <?= Studip\LinkButton::create(
                        _('Neuer Ordner'),
                        $controller->url_for('file/new_folder/' . $topFolder->getId()),
                        ['data-dialog' => '']
                    ) ?>
                <? endif ?>
                <? if ($topFolder->isWritable($GLOBALS['user']->id)): ?>
                    <?= Studip\LinkButton::create(_('Datei hinzufügen'), '#', [
                        'onclick' => 'STUDIP.Files.openAddFilesWindow(); return false;'
                    ]) ?>
                <? endif ?>
                </td>
            </tr>
        </tfoot>
    <? endif ?>
</table>
</form>
<? if ($GLOBALS['user']->id !== 'nobody') : ?>

    <?= $this->render_partial('file/upload_window.php') ?>
    <?= $this->render_partial('file/add_files_window.php', [
        'folder_id' => $topFolder->getId(),
        'hidden'    => true,
        'upload_type' => FileManager::getUploadTypeConfig($topFolder->range_id, $GLOBALS['user']->id)
    ]) ?>
<? endif ?>
<? endif ?>
