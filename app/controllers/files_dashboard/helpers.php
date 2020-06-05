<?php

namespace FilesDashboard;

use Icon;
use URLHelper;

trait Helpers
{
    /**
     * Create an action menu for a file. This method is used by the template.
     *
     * @param FileRef    $fileRef the file whose action shall be created
     * @param FolderType $folder  the file's folder
     * @param User       $user    the user for whom the actions shall be created
     *
     * @return string the HTML fragment of the action menu
     */
    public function getActionMenu($fileRef, $folder, $user)
    {
        $actionMenu = \ActionMenu::get();

        $actionMenu->addLink(
            URLHelper::getURL('dispatch.php/file/details/'.$fileRef->id),
            _('Info'),
            Icon::create('info-circle', Icon::ROLE_CLICKABLE, ['size' => 20]),
            ['data-dialog' => 1]
        );

        require_once 'app/controllers/files.php';

        if ($rangeLink = \FilesController::getRangeLink($folder)) {
            $actionMenu->addLink(
                $rangeLink,
                _('Ordner öffnen'),
                Icon::create('folder-empty', Icon::ROLE_CLICKABLE, ['size' => 20])
            );
        }

        if ($folder->isFileEditable($fileRef->id, $user->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/edit/'.$fileRef->id),
                _('Datei bearbeiten'),
                Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => '']
            );
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/update/'.$fileRef->id),
                _('Datei aktualisieren'),
                Icon::create('refresh', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => '']
            );
        }

        if ($folder->isFileWritable($fileRef->id, $user->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/choose_destination/move/'.$fileRef->id),
                _('Datei verschieben'),
                Icon::create('file+move_right', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
        }

        if ($folder->isFileDownloadable($fileRef, $user->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/choose_destination/copy/'.$fileRef->id),
                _('Datei kopieren'),
                Icon::create('file+add', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
        }

        if ($folder->isFileWritable($fileRef->id, $user->id)) {
            $actionMenu->addLink(
                URLHelper::getURL('dispatch.php/file/delete/'.$fileRef->id),
                _('Datei löschen'),
                Icon::create('trash', Icon::ROLE_CLICKABLE, ['size' => 20]),
                ['onclick' => "return STUDIP.Dialog.confirmAsPost('".sprintf(_('Soll die Datei "%s" wirklich gelöscht werden?'), jsReady($fileRef->name))."', this.href);"]
            );
        }

        return $actionMenu->render();
    }

    /**
     * Takes a `text`, marks the first occurence of a `phrase` and
     * limits the length of this to `lim` characters. Used in the template.
     *
     * @param string $text   the text to be marked and cut
     * @param string $phrase the phrase to mark
     * @param int    $lim    the length of the abbreviated text
     *
     * @return string the marked and cut text
     */
    public function markPhrase($text, $phrase, $lim = 150)
    {
        if (mb_strlen($text) > $lim) {
            $text = mb_substr($text, max([0, $this->findWordPosition($phrase, $text) - $lim * 0.5]), $lim);
        }

        $words = str_replace(' ', '|', preg_quote(htmlReady($phrase), '/'));

        return preg_replace("/($words)/i", '<mark>\\1</mark>', htmlReady($text));
    }

    private function findWordPosition($word, $phrase)
    {
        return mb_stripos($phrase, $word) ?: 0;
    }
}
