<?php
/**
 * ForumHelpers.php - Some useful helpers for the forum
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

class ForumHelpers {

    /**
     * The page for the current script run, modified by a global page-handle
     * @var int
     */
    static $page = 1;

    /**
     * helper_function for highlight($text, $highlight)
     *
     * @param  string  $text
     * @param  array   $highlight
     * @return string
     */
    static function do_highlight($text, $highlight)
    {
        foreach ($highlight as $hl) {
            $text = preg_replace('/' . preg_quote(htmlReady($hl), '/') . '/i',
                                 '<span class="highlight">$0</span>',
                                 $text);
        }
        return $text;
    }

    /**
     * This function highlights Text HTML-safe
     * (tags or words in tags are not highlighted, words between tags ARE highlighted)
     *
     * @param string $text the text where to words shall be highlighted, may contain tags
     * @param array $highlight an array of words to be highlighted
     * @return string the highlighted text
     */
    function highlight($text, $highlight)
    {
        if (empty($highlight)) {
            return $text;
        }

        $data = [];
        $treffer = [];

        // split text at every tag
        $pattern = '/<[^<]*>/U';
        preg_match_all($pattern, $text, $treffer, PREG_OFFSET_CAPTURE);

        if (sizeof($treffer[0]) == 0) {
            return self::do_highlight($text, $highlight);
        }

        // cycle trough the text between the tags and highlight all hits
        $last_pos = 0;
        foreach ($treffer[0] as $taginfo) {
            $size = mb_strlen($taginfo[0]);
            if ($taginfo[1] != 0) {
                $data[] = self::do_highlight(mb_substr($text, $last_pos, $taginfo[1] - $last_pos), $highlight);
            }

            $data[] = mb_substr($text, $taginfo[1], $size);
            $last_pos = $taginfo[1] + $size;
        }

        // don't miss the last portion of a posting
        if ($last_pos < mb_strlen($text)) {
            $data[] = self::do_highlight(mb_substr($text, $last_pos, mb_strlen($text) - $last_pos), $highlight);
        }

        return implode('', $data);
    }

    /**
     * Returns a human-readable version of the passed global Stud.IP permission.
     *
     * @param  string  $perm
     * @return string
     */
    public static function translate_perm($perm)
    {
        $mapping = [
            'root'   => _('Root'),
            'admin'  => _('Administrator/-in'),
            'dozent' => _('Lehrende/-r'),
            'tutor'  => _('Tutor/-in'),
            'autor'  => _('Autor/-in'),
            'user'   => _('Leser/-in'),
        ];

        // TODO: Activate next when devboard reliably runs on PHP7
        // return $mapping[$perm] ?? '';

        return isset($mapping[$perm]) ? $mapping[$perm] : '';
    }

    /**
     * return the currently chosen page
     *
     * @return  int
     */
    static function getPage()
    {
        return self::$page;
    }

    /**
     * set the current page
     *
     * @param int $page_num the page
     */
    static function setPage($page_num)
    {
        self::$page = $page_num;
    }

    /**
     * Return an info-text explaining the visit-status of the passed topic_di
     * which has the passed number of new entries.
     *
     * @param string $num_entries  the number of new entries
     * @param string $topic_id     the id of the topic
     *
     * @return string  a human readable, localized text
     */
    static function getVisitText($num_entries, $topic_id)
    {
        if ($num_entries > 0) {
            $text = sprintf(_('Seit Ihrem letzten Besuch gibt es %s neue Beiträge'), $num_entries);
        } else {
            $all_entries = max(ForumEntry::countEntries($topic_id) - 1, 0);
            if ($all_entries == 0) {
                $text = sprintf(_('Es gibt bisher keine Beiträge.'));
            } else if ($all_entries == 1) {
                $text = sprintf(_('Seit Ihrem letzten Besuch gab es nichts Neues.'
                      . ' Es ist ein alter Beitrag vorhanden.'));
            } else {
                $text = sprintf(_('Seit Ihrem letzten Besuch gab es nichts Neues.'
                      . ' Es sind %s alte Beiträge vorhanden.'), $all_entries);
            }
        }

        return $text;
    }

    /**
     * return the online status of the passed user, one of three possible
     * states is returned:
     *  - available
     *  - away
     *  - offline
     *
     * @staticvar type $online_status
     *
     * @param string $user_id
     *
     * @return string
     */
    static function getOnlineStatus($user_id)
    {
        static $online_status;

        // check if the corresponding user's profile is visible
        if (get_visibility_by_id($user_id) == false) {
            return 'offline';
        }

        if ($GLOBALS['user']->id == $user_id) {
            return 'available';
        }

        if (!$online_status) {
            $online_users = get_users_online(10);
            foreach ($online_users as $username => $data) {
                if ($data['last_action'] >= 300) {
                    $online_status[$data['user_id']] = 'away';
                } else {
                    $online_status[$data['user_id']] = 'available';
                }
            }
        }

        return $online_status[$user_id] ?: 'offline';
    }

    /**
     * Create a pdf of all postings belonging to the passed seminar located
     * under the passed topic_id. The PDF is dispatched automatically.
     *
     * BEWARE: This function never returns, it dies after the PDF has been
     * (succesfully or not) dispatched.
     *
     * @param string $seminar_id
     * @param string $parent_id
     */
    static function createPdf($seminar_id, $parent_id = null)
    {
        $seminar_name = get_object_name($seminar_id, 'sem');
        $data = ForumEntry::getList('dump', $parent_id ?: $seminar_id);
        $first_page = true;

        $document = new ExportPDF();
        $document->SetTitle(_('Forum'));
        $document->setHeaderTitle(sprintf(_("Forum \"%s\""), $seminar_name['name']));
        $document->addPage();

        foreach ($data as $entry) {
            if (Config::get()->FORUM_ANONYMOUS_POSTINGS && $entry['anonymous']) {
                $author = _('anonym');
            } else {
                $author = $entry['author'];
            }
            if ($entry['depth'] == 1) {
                if (!$first_page) {
                    $document->addPage();
                }
                $first_page = false;
                $document->addContent('!! '. _('Bereich') . ": {$entry['name_raw']}\n");
                $document->addContent($entry['content_raw']);
                $document->addContent("\n\n");
            } else if ($entry['depth'] == 2) {
                $document->addContent('! '. _('Thema') . ": {$entry['name_raw']}\n");
                $document->addContent('%%' . sprintf(_('erstellt von %s am %s'), $author,
                    strftime('%A %d. %B %Y, %H:%M', (int)$entry['mkdate'])) . '%%' . "\n");
                $document->addContent($entry['content_raw']);
                $document->addContent("\n--\n");
            } else if ($entry['depth'] == 3) {
                $document->addContent("**{$entry['name_raw']}**\n");
                $document->addContent('%%' . sprintf(_('erstellt von %s am %s'), $author,
                    strftime('%A %d. %B %Y, %H:%M', (int)$entry['mkdate'])) . '%%' . "\n");
                $document->addContent($entry['content_raw']);
                $document->addContent("\n--\n");
            }
        }

        $document->dispatch($seminar_name['name'] ." - Forum");
        die;
    }


    /**
     * Returns the id of the currently selected seminar or false, if no seminar
     * is selected
     *
     * @return mixed  seminar_id or false
     */
    static function getSeminarId()
    {
        return Context::getId();
    }

    /**
     * replace in the passed text every %%% with <% and every ### with %>
     * This is used to work around a limitation of the Button-API in combination
     * with the underscore.js way of inserting template vars.
     *
     * The Button-API correctly replaces < > with tags, but underscore.js is
     * unable to find them in their tag-represenation
     *
     * @param string $text the text to apply the replacements on
     *
     * @return string the modified text
     */
    static function replace($text)
    {
        return str_replace('%%%', '<%', str_replace('###', '%>', $text));
    }
}
