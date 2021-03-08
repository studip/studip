<?php
namespace RESTAPI\Routes;


/**
 * This file contains the REST class for the clipboard system.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       4.5
 * @deprecated  Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 */
class Clipboard extends \RESTAPI\RouteMap
{
    /**
     * Adds a new clipboard.
     *
     * @post /clipboard/add
     */
    public function addClipboard()
    {
        $name = \Request::get('name');

        if (!$name) {
            $this->halt(400, _('Es wurde kein Name angegeben!'));
        }

        $clipboard = new \Clipboard();
        $clipboard->user_id = $GLOBALS['user']->id;
        $clipboard->name = $name;
        if (!$clipboard->store()) {
            $this->halt(500, _('Fehler beim Speichern des Merkzettels!'));
        }

        $result = $clipboard->toRawArray();
        //A special treatment for the widget_id parameter:
        //It is passed through:
        $widget_id = \Request::get('widget_id');
        if ($widget_id) {
            $result['widget_id'] = $widget_id;
        }

        return $result;
    }


    /**
     * Edits a clipboard.
     *
     * @put /clipboard/:clipboard_id
     */
    public function editCliboard($clipboard_id = null)
    {
        $clipboard = \Clipboard::find($clipboard_id);
        if (!$clipboard) {
            $this->notFound(_('Ungültige Merkzettel-ID!'));
        }

        if ($clipboard->user_id != $GLOBALS['user']->id) {
            //Thou shalt not delete clipboards
            //which don't belong to you!
            throw new AccessDeniedException();
        }

        $name = $this->data['name'];
        if (!$name) {
            $this->halt(400, _('Es wurde kein Name angegeben!'));
        }

        $clipboard->name = $name;

        $success = false;

        if ($clipboard->isDirty()) {
            $success = $clipboard->store();
        } else {
            $success = true;
        }

        if (!$success) {
            $this->halt(500, _('Fehler beim Bearbeiten des Merkzettels!'));
        }

        $result = $clipboard->toRawArray();

        //A special treatment for the widget_id parameter:
        //It is passed through:
        $widget_id = \Request::get('widget_id');
        if ($widget_id) {
            $result['widget_id'] = $widget_id;
        }

        return $result;
    }


    /**
     * Deletes a clipboard.
     *
     * @delete /clipboard/:clipboard_id
     */
    public function deleteClipboard($clipboard_id = null)
    {
        $clipboard = \Clipboard::find($clipboard_id);
        if (!$clipboard) {
            $this->notFound(_('Ungültige Merkzettel-ID!'));
        }

        if ($clipboard->user_id != $GLOBALS['user']->id) {
            //Thou shalt not delete items of clipboards
            //which don't belong to you!
            throw new AccessDeniedException();
        }

        if (!$clipboard->delete()) {
            $this->halt(500, _('Fehler beim Löschen des Merkzettels!'));
        }

        return "";
    }


    /**
     * Adds an item to a clipboard.
     *
     * @post /clipboard/:clipboard_id/item
     */
    public function addClipboardItem($clipboard_id = null)
    {
        $clipboard = \Clipboard::find($clipboard_id);
        if (!$clipboard) {
            $this->notFound(_('Ungültige Merkzettel-ID!'));
        }

        if ($clipboard->user_id != $GLOBALS['user']->id) {
            //Thou shalt not add items to clipboards
            //which don't belong to you!
            throw new AccessDeniedException();
        }

        $range_id = \Request::get('range_id');
        $range_type = \Request::get('range_type');
        $widget_id = \Request::get('widget_id');

        if (!is_a($range_type, $clipboard->allowed_item_class, true)) {
            $this->halt(
                400,
                sprintf(
                    _('Die Klasse %s ist in dieser Merkzettel-Klasse nicht erlaubt!'),
                    $range_type
                )
            );
        }

        try {
            $item = $clipboard->addItem($range_id, $range_type);

            $result = $item->toRawArray();
            $result['name'] = $item->__toString();
            if ($widget_id) {
                $result['widget_id'] = $widget_id;
            }
            return $result;
        } catch (Exception $e) {
            $this->halt(500, $e->getMessage());
        }
    }


    /**
     * Removes an item (selected by its range-ID) from a clipboard.
     *
     * @delete /clipboard/:clipboard_id/item/:range_id
     */
    public function removeClipboardItem($clipboard_id = null, $range_id = null)
    {
        $clipboard = \Clipboard::find($clipboard_id);
        if (!$clipboard) {
            $this->notFound(_('Ungültige Merkzettel-ID!'));
        }

        if ($clipboard->user_id != $GLOBALS['user']->id) {
            //Thou shalt not delete items of clipboards
            //which don't belong to you!
            throw new AccessDeniedException();
        }

        if ($clipboard->removeItem($range_id)) {
            return ['range_id' => $range_id];
        } else {
            $this->halt(500, _('Fehler beim Löschen des Eintrags!'));
        }
    }
}
