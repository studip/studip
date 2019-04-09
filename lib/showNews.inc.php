<?php
# Lifter001: DONE
# Lifter002: TEST
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
 * showNews.inc.php - Anzeigefunktion fuer News
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Ralf Stockmann <rstockm@gwdg.de>
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Stefan Suchi <suchi@gmx.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Arne Schröder <schroeder@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     news
 */

require_once 'lib/object.inc.php';

/**
 * generates proper text for confirmation question and deletes comments
 *
 *
 * @param mixed $comment_id (single or array)
 * @return string text for confirmation question or empty string after deletion
 */
function delete_comments($delete_comments_array = '')
{
    $text = '';
    $confirmed = false;
    if (! is_array($delete_comments_array))
        $delete_comments_array = [$delete_comments_array];
    if (Request::submitted('yes') AND Request::isPost()) {
        CSRFProtection::verifySecurityToken();
        $confirmed = true;
    }
    if ($confirmed) {
        foreach ($delete_comments_array as $comment_id) {
            $delete_comment = new StudipComment($comment_id);
            if (!$delete_comment->isNew()) {
                if (!is_object($news[$delete_comment->getValue("object_id")]))
                    $news[$delete_comment->getValue("object_id")] = new StudipNews($delete_comment->getValue("object_id"));
                // user has to have delete permission for news
                if ($news[$delete_comment->getValue("object_id")]->havePermission('delete')) {
                    $delete_comment->delete();
                    $delete_counter++;
                }
                else
                    PageLayout::postMessage(MessageBox::error(_('Keine Berechtigung zum Löschen des Kommentars.')));
            }
        }
        if ($delete_counter > 1)
            PageLayout::postMessage(MessageBox::success(sprintf(_('%s Kommentare wurden gelöscht.'), $delete_counter)));
        elseif ($delete_counter == 1)
            PageLayout::postMessage(MessageBox::success(_('Kommentar wurde gelöscht.')));
    }
    else {
        if (count($delete_comments_array) > 1)
            $text = sprintf(_('Wollen Sie die %s Komentare jetzt löschen?'), count($delete_comments_array));
        elseif (count($delete_comments_array) == 1)
            $text = _('Wollen Sie den Kommentar jetzt löschen?');
    }
    return $text;
}

/**
 * generates proper text for confirmation question and deletes news
 *
 *
 * @param mixed $delete_news_array (single id or array)
 * @return string text for confirmation question or empty string after deletion
 */
function delete_news($delete_news_array)
{
    $text = '';
    $confirmed = false;
    if (! is_array($delete_news_array))
        $delete_news_array = [$delete_news_array];
    if (Request::submitted('yes') AND Request::isPost()) {
        CSRFProtection::verifySecurityToken();
        $confirmed = true;
    }
    foreach ($delete_news_array as $news_id) {
        if ($news_id) {
            $delete_news = new StudipNews($news_id);
            $delete_news_titles[] = $delete_news->getValue('topic');
            if ($confirmed) {
                $msg_object = new messaging();
                if ($delete_news->havePermission('delete')) {
                    PageLayout::postMessage(MessageBox::success(sprintf(_('Ankündigung "%s" wurde gelöscht.'), htmlReady($delete_news->getValue('topic')))));
                    if ($delete_news->getValue('user_id') != $GLOBALS['auth']->auth['uid']) {
                        setTempLanguage($delete_news->getValue('user_id'));
                        $msg = sprintf(_('Ihre Ankündigung "%s" wurde von der Administration gelöscht!.'), $delete_news->getValue('topic'), get_fullname() . ' ('.get_username().')'). "\n";
                        $msg_object->insert_message($msg, get_username($delete_news->getValue('user_id')) , "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Ankündigung geändert"));
                        restoreLanguage();
                    }
                    $delete_news->delete();
                }
                else
                    PageLayout::postMessage(MessageBox::error(sprintf(_('Keine Berechtigung zum Löschen der Ankündigung "%s".'), htmlReady($delete_news->getValue('topic')))));
            }
        }
    }
    if (! $confirmed) {
        if (count($delete_news_titles) == 1)
            $text = sprintf(_('- Die Ankündigung "%s" wird unwiderruflich gelöscht.'), $delete_news_titles[0])."\n";
        elseif (count($delete_news_titles) > 1)
            $text = sprintf(_('- Die %s Ankündigungen "%s" werden unwiderruflich gelöscht.'), count($delete_news_titles), implode('", "', $delete_news_titles))."\n";
    }
    return $text;
}

/**
 * generates proper text for confirmation question and removes range_id from news
 *
 *
 * @param $remove_array array with $news_id as key and array of range_ids as value
 * @param string $range_id
 * @return string text for confirmation question or empty string after removal
 */
function remove_news($remove_array)
{
    $confirmed = false;
    $question_text = [];
    if (! is_array($remove_array))
        return false;
    if (Request::submitted('yes') AND Request::isPost()) {
        CSRFProtection::verifySecurityToken();
        $confirmed = true;
    }
    foreach ($remove_array as $news_id => $ranges) {
        $remove_news = new StudipNews($news_id);
        $remove_news_title = $remove_news->getValue('topic');
        if (! is_array($ranges))
            $ranges = [$ranges];
        // should we delete news completely
        if (count($ranges) == count($remove_news->getRanges())) {
            $text = delete_news($news_id);
            if ($text)
                $question_text[] = $text;
        // or just remove range_id(s)?
        } else {
            $text = '';
            if ($confirmed AND ! $remove_news->isNew() AND count($ranges)) {
                foreach ($ranges as $key => $range_id) {
                    if ($remove_news->havePermission('unassign', $range_id)) {
                        $remove_news->deleteRange($range_id);
                    } else {
                        unset($ranges[$key]);
                        PageLayout::postMessage(MessageBox::error(sprintf(_('Keine Berechtigung zum Entfernen der Ankündigung "%s" aus diesem Bereich.'), htmlReady($remove_news->getValue('topic')))));
                    }
                    if (count($ranges)) {
                        if (count($ranges) == 1)
                            PageLayout::postMessage(MessageBox::success(sprintf(_('Ankündigung "%s" wurde aus dem Bereich entfernt.'), htmlReady($remove_news->getValue('topic')))));
                        else
                            PageLayout::postMessage(MessageBox::success(sprintf(_('Ankündigung "%s" wurde aus %s Bereichen entfernt.'), htmlReady($remove_news->getValue('topic')), count($ranges))));
                        $remove_news->store();
                    }
                }
            } elseif (! $confirmed) {
                if (count($ranges) == 1)
                    $text = sprintf(_('- Die Ankündigung "%s" wird aus dem aktiven Bereich entfernt. '
                                      .'Sie wird dadurch nicht endgültig gelöscht. Es wird nur die Zuordnung entfernt.'), $remove_news_title)."\n";
                elseif (count($ranges) > 1)
                    $text = sprintf(_('- Die Ankündigung "%s" wird aus den %s gewählten Bereichen entfernt. '
                                      .'Sie wird dadurch nicht endgültig gelöscht. Es werden nur die Zuordnungen entfernt.'), $remove_news_title, count($ranges))."\n";
            }
            if ($text)
               $question_text[] = $text;
        }
    }
    if (count($question_text) > 1)
        return _('Wollen Sie die folgenden Aktionen jetzt ausführen?') . "\n" . implode($question_text);
    elseif (count($question_text) == 1)
        return _('Wollen Sie diese Aktion jetzt ausführen?') . "\n" . implode($question_text);
}

/**
 *
 * @param unknown_type $range_id
 * @param unknown_type $type
 */
function show_rss_news($range_id, $type)
{
    $item_url_fmt = '%1$s&contentbox_open=%2$s#%2$s';

    switch ($type){
        case 'user':
            $studip_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/profile?again=yes&username=' . get_username($range_id);
            $title = get_fullname($range_id) . ' (Stud.IP - ' . Config::get()->UNI_NAME_CLEAN . ')';
            $description = _('Persönliche Neuigkeiten') . ' ' . $title;
        break;
        case 'sem':
            $studip_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/course/overview?cid=' . $range_id;
            $sem_obj = Seminar::GetInstance($range_id);
            if ($sem_obj->read_level > 0) {
                $studip_url .= '&again=yes';
            }
            $title = $sem_obj->getName() . ' (Stud.IP - ' . Config::get()->UNI_NAME_CLEAN . ')';
            $description = _('Neuigkeiten der Veranstaltung') . ' ' . $title;

        break;
        case 'inst':
            $studip_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/institute/overview?auswahl=' . $range_id;
            $object_name = get_object_name($range_id, $type);
            if (!Config::get()->ENABLE_FREE_ACCESS) {
                $studip_url .= "&again=yes";
            }
            $title = $object_name['name'] . ' (Stud.IP - ' . Config::get()->UNI_NAME_CLEAN . ')';
            $description = _('Neuigkeiten der Einrichtung') . ' ' . $title;
        break;
        case 'global':
            $studip_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/start?again=yes';
            $title = 'Stud.IP - ' . Config::get()->UNI_NAME_CLEAN;
            $description = _('Allgemeine Neuigkeiten') . ' ' . $title;
        break;
    }

    $items = StudipNews::GetNewsByRange($range_id, true);
    $last_changed = 0;

    foreach ($items as &$item) {
        if ($last_changed < $item['chdate']) {
            $last_changed = $item['chdate'];
        }

        if ($item['date'] < $item['chdate']) {
            $item['date'] = $item['chdate'];
        }
        list($body, $admin_msg) = explode('<admin_msg>', $item['body']);
        $item['body'] = $body;
    }

    header('Content-type: application/rss+xml; charset=utf-8');

    $template = $GLOBALS['template_factory']->open('news/rss-feed');
    $template->items        = $items;
    $template->title        = $title;
    $template->studip_url   = $studip_url;
    $template->description  = $description;
    $template->last_changed = $last_changed;
    $template->item_url_fmt = $item_url_fmt;
    echo $template->render();

    return true;
}
