<?php
# Lifter002: TODO
# Lifter007: TODO
use Studip\Button, Studip\LinkButton;

/**
* Retrieve a WikiPage version from current seminar's WikiWikiWeb.
*
* Returns raw text data from database if requested version is
* available. If not, an
*
* @param string WikiWiki keyword to be retrieved
* @param int    Version number. If empty, latest version is returned.
*
**/
function getWikiPage($keyword, $version)
{
    $page = null;
    if ($version) {
        $page = WikiPage::find([Context::getId(), $keyword, $version]);
    }
    if ($page === null) {
        $page = WikiPage::findLatestPage(Context::getId(), $keyword);
    }

    if ($page) {
        return $page;
    }

    if ($keyword === 'WikiWikiWeb') {
        return WikiPage::getStartPage(Context::getId());
    }

    $page = new WikiPage();
    $page->range_id = Context::getId();
    $page->keyword  = $keyword;
    return $page;
}


/**
* Write a new/edited wiki page to database
*
* @param    string  keyword WikiPage name
* @param    string  version WikiPage version
* @param    string  body    WikiPage text
* @param    string  user_id Internal user id of editor
* @param    string  range_id    Internal id of seminar/einrichtung
*
**/
function submitWikiPage($keyword, $version, $body, $user_id, $range_id) {

    global $perm;
    releasePageLocks($keyword, $user_id); // kill lock that was set when starting to edit
    // write changes to db, show new page
    $latestVersion = getWikiPage($keyword, false);
    if ($latestVersion) {
        $date = time();
        $lastchange = $date - $latestVersion['chdate'];
    }

    StudipTransformFormat::addStudipMarkup('wiki-comments', '(\[comment\])', null, function(){return sprintf('[comment=%s]', get_fullname());});

    //TODO: Die $message Texte klingen fürchterlich. Halbsätze, Denglisch usw...
    if ($latestVersion && ($latestVersion['body'] == $body)) {
        $message = MessageBox::info(_('Keine Änderung vorgenommen.'));
        PageLayout::postMessage($message);
    } else if ($latestVersion && ($version !== null) && ($lastchange < 30*60) && ($user_id == $latestVersion['user_id'])) {
        // if same author changes again within 30 minutes, no new verison is created
        $wp = WikiPage::find([$range_id, $keyword, $version]);
        if ($wp) {
            if ($wp->isEditableBy($GLOBALS['user'])) {
                // apply replace-before-save transformations
                $wp->body = transformBeforeSave($body);
                $wp->store();
            } else {
                PageLayout::postInfo(_('Keine Änderung vorgenommen, da zwischenzeitlich die Editier-Berechtigung entzogen wurde.'));
            }
        }
    } else {
        if ($version === null) {
            $version = 0;
        } else {
            $version = $latestVersion['version'] + 1;
        }

        // apply replace-before-save transformations
        $body = transformBeforeSave($body);
        WikiPage::create(compact('range_id', 'user_id', 'keyword', 'body', 'version'));
    }
    StudipTransformFormat::removeStudipMarkup('wiki-comments');
    refreshBacklinks($keyword, $body);
}

/**
 * Retrieve latest version for a given keyword
 *
 * @param  string  keyword WikiPage name
 * @return array
 */
function getLatestVersion($keyword, $range_id) {
    $query = "SELECT *
              FROM wiki
              WHERE keyword = ? AND range_id = ?
              ORDER BY version DESC
              LIMIT 1";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$keyword, $range_id]);
    return $statement->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retrieve oldest version for a given keyword
 *
 * @param    string  WikiPage name
 * @return array
 */
function getFirstVersion($keyword, $range_id) {
    $query = "SELECT *
              FROM wiki
              WHERE keyword = ? AND range_id = ?
              ORDER BY version ASC
              LIMIT 1";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$keyword, $range_id]);
    return $statement->fetch(PDO::FETCH_ASSOC);
}

/**
 * Return array containing version numbes and chdates
 *
 * @param string $keyword  Wiki keyword for currently selected seminar
 * @param int    $limit    Number of links to be retrieved (default:10)
 * @param bool   $getfirst Should first (=most recent) version be retrieved too?
 * @return array
 */
function getWikiPageVersions($keyword, $limit = 10, $getfirst = false)
{
    $query = "SELECT version, chdate
              FROM wiki
              WHERE keyword = ? AND range_id = ?
              ORDER BY version DESC
              LIMIT " . (int) $limit;
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$keyword, Context::getId()]);
    $versions = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!$getfirst) {
        // skip first
        $versions = array_slice($versions, 1);
    }

    return $versions;
}


/**
 * Check if given keyword exists in current WikiWikiWeb.
 *
 * @param    string  WikiPage keyword
 */
function keywordExists($str, $sem_id = null) {
    static $keywords;

    if (is_null($keywords)) {
        $query = "SELECT DISTINCT keyword, 1 FROM wiki WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$sem_id ?: Context::getId()]);
        $keywords = $statement->fetchGrouped(PDO::FETCH_COLUMN);
    }
    // retranscode html entities to ascii values (as stored in db)
    // (nessecary for umlauts)
    // BUG: other special chars like accented vowels don't work yet!
    //
    $trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES));
    $nonhtmlstr = strtr($str, $trans_tbl);

    return $keywords[$nonhtmlstr];
}


/**
 * Check if keyword already exists or links to new page.
 * Returns HTML-Link-Representation.
 *
 * @param    string  WikiPage keyword
 * @param    string  current Page (for edit abort backlink)
 * @param    string  out format: "wiki"=link to wiki.php, "inline"=link on same page
 */
function isKeyword($str, $page, $format = 'wiki', $sem_id = null, $alt_str = null) {
    if (!$alt_str) {
        $alt_str = $str;
    }
    $trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES));
    $nonhtmlstr = strtr($str, $trans_tbl);
    if (!keywordExists($str, $sem_id)) {
        if ($format === 'wiki') {
            return " <a href=\"".URLHelper::getLink("?keyword=" . urlencode($nonhtmlstr) . "&view=editnew&lastpage=".urlencode($page))."\">" . $alt_str . "(?)</a>";
        } else if ($format === 'inline') {
            return $str;
        }
    } else {
        if ($format == 'wiki') {
            return " <a href=\"".URLHelper::getLink("?keyword=".urlencode($nonhtmlstr))."\">".$alt_str."</a>";
        } else if ($format == 'inline') {
            return " <a href=\"#".urlencode($nonhtmlstr)."\">".$alt_str."</a>";
        }
    }
}


/**
 * Get lock information about page
 * Returns displayable string containing lock information
 * (Template: Username1 (seit x Minuten), Username2 (seit y Minuten), ...)
 * or NULL if no locks set.
 *
 * @param    string  WikiPage keyword
 * @param    string  user_id  Internal user id
 */
function getLock($keyword, $user_id)
{
    $query = "SELECT user_id, chdate
              FROM wiki_locks
              WHERE range_id = ? AND keyword = ? AND user_id != ?
              ORDER BY chdate DESC";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([Context::getId(), $keyword, $user_id]);
    $locks = $statement->fetchAll(PDO::FETCH_ASSOC);

    $lockstring = '';
    foreach ($locks as $index => $lock) {
        if ($index) {
            if ($index == count($locks) - 1) {
                $lockstring .= _(' und ');
            } else {
                $lockstring .= ', ';
            }
        }
        $duration = ceil((time() - $lock['chdate']) / 60);

        $lockstring .= get_fullname($lock['user_id'], 'full', true);
        $lockstring .= sprintf(_(' (seit %d Minuten)'), $duration);
    }

    return $lockstring;
}

/**
 * Set lock for current user and current page
 *
 * @param    DB_Seminar  db  DB_Seminar instance (no longer neccessary)
 * @param    string      user_id Internal user id
 * @param    string      range_if    Internal seminar id
 * @param    string      keyword WikiPage name
 */
function setWikiLock($db, $user_id, $range_id, $keyword) {
    $query = "REPLACE INTO wiki_locks (user_id, range_id, keyword, chdate)
              VALUES (?, ?, ?, UNIX_TIMESTAMP())";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$user_id, $range_id, $keyword]);
}


/**
 * Release all locks for wiki page that are older than 30 minutes.
 *
 * @param    string  WikiPage keyword
 */
function releaseLocks($keyword)
{
    // Prepare statement that actually releases (removes) the lock
    $query = "DELETE FROM wiki_locks WHERE range_id = ? AND keyword = ? AND chdate = ?";
    $release_statement = DBManager::get()->prepare($query);

    // Prepare and execute statement that reads all locks
    $query = "SELECT range_id, keyword, chdate
              FROM wiki_locks
              WHERE range_id = ? AND keyword = ? AND chdate < UNIX_TIMESTAMP(NOW() - INTERVAL 30 MINUTE)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([Context::getId(), $keyword]);

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $release_statement->execute([
            $row['range_id'],
            $row['keyword'],
            $row['chdate'],
        ]);
    }
}

/**
 * Release locks for current wiki page and current user
 *
 * @param    string  keyword WikiPage name
 * @param    string  user_id Internal user id
 *
 */
function releasePageLocks($keyword, $user_id)
{
    $query = "DELETE FROM wiki_locks
              WHERE range_id = ? AND keyword = ? AND user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([Context::getId(), $keyword, $user_id]);
}


/**
* Return list of WikiWord in given page body ($str)
*
* @param    string  str
*
**/
function getWikiLinks($str) {
    $str = preg_replace('/\[nop\].*\[\/nop\]/', '', $str);
    $str = preg_replace('/\[code\].*\[\/code\]/', '', $str);
    preg_match_all(
        '/' . WikiFormat::getWikiMarkup('wiki-links')['start'] . '/',
        $str,
        $out_wikiwords,
        PREG_PATTERN_ORDER
    );
    return array_unique($out_wikiwords[1]);
}

/**
* Return list of WikiPages containing links to given page
*
* @param    string  Wiki keyword
*
**/
function getBacklinks($keyword)
{
    // don't show references from Table of contents (='toc')
    $query = "SELECT DISTINCT from_keyword
              FROM wiki_links
              WHERE range_id = ? AND to_keyword = ? AND from_keyword != 'toc'";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([Context::getId(), $keyword]);
    return $statement->fetchAll(PDO::FETCH_COLUMN);
}

/**
* Refresh wiki_links table for backlinks from given page to
* other pages
*
* @param    string  keyword WikiPage-name for $str content
* @param    string  str Page content containing links
*
**/
function refreshBacklinks($keyword, $str)
{
    // insert links from page to db
    // logic: all links are added, also links to nonexistant pages
    // (these will change when submitting other pages)

    // first delete all links
    $query = "DELETE FROM wiki_links WHERE range_id = ? AND from_keyword = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([Context::getId(), $keyword]);

    // then reinsert those (still) existing
    $wikiLinkList = getWikiLinks($str);
    if (!empty($wikiLinkList)) {
        $query = "INSERT INTO wiki_links (range_id, from_keyword, to_keyword)
                  VALUES (?, ?, ?)";
        $statement = DBManager::get()->prepare($query);

        foreach ($wikiLinkList as $key => $value) {
            $statement->execute([Context::getId(), $keyword, $value]);
        }
    }
}

/**
* Generate Meta-Information on Wiki-Page to display in top line
*
* @param    db-query result     all information about a wikiPage
* @return   string  Displayable HTML
*
**/
function getZusatz($wikiData)
{
    if (!$wikiData || $wikiData["version"] <= 0) {
        return "";
    }

    $user = User::find($wikiData['user_id']);

    $s =  '<a href="' . URLHelper::getLink('?keyword=' . urlencode($wikiData['keyword']) . '&version=' . $wikiData['version']). '">' . _('Version ') . $wikiData['version'] . '</a>';
    $s .= sprintf(_(', geändert von %s am %s'),
                  $user
                      ? '<a href="' . URLHelper::getLink('dispatch.php/profile?username=' . $user->username) .'">' . htmlReady($user->getFullName()) . '</a>'
                      : _('unbekannt'),
                  date('d.m.Y, H:i', $wikiData['chdate']));
    return $s;
}

/**
* Display yes/no dialog to confirm WikiPage version deletion.
*
* @param    string  WikiPage name
* @param    string  WikiPage version (if empty: take latest)
*
* @return   string  Version number to delete
*
**/
function showDeleteDialog($keyword, $version) {
    global $perm;
    if (!$perm->have_studip_perm("tutor", Context::getId())) {
        throw new AccessDeniedException(_('Sie haben keine Berechtigung, Seiten zu löschen.'));
    }
    $islatest=0; // will another version become latest version?
    $willvanish=0; // will the page be deleted entirely?
    if ($version=="latest") {
        $lv=getLatestVersion($keyword, Context::getId());
        $version=$lv["version"];
        if ($version==1) {
            $willvanish=1;
        }
        $islatest=1;
    }

    if (!$islatest) {
        throw new InvalidArgumentException(_('Die Version, die Sie löschen wollen, ist nicht die Aktuellste. Überprüfen Sie, ob inzwischen eine aktuellere Version erstellt wurde.'));
    }
    $msg= sprintf(_("Wollen Sie die untenstehende Version %s der Seite %s wirklich löschen?"), "<b>".htmlReady($version)."</b>", "<b>".htmlReady($keyword)."</b>") . "<br>\n";
    if (!$willvanish) {
        $msg .= _("Diese Version ist derzeit aktuell. Nach dem Löschen wird die nächstältere Version aktuell.") . "<br>";
    } else {
        $msg .= _("Diese Version ist die derzeit einzige. Nach dem Löschen ist die Seite komplett gelöscht.") . "<br>";
    }
    //TODO: modaler dialog benutzen
    $msg.=LinkButton::createAccept(_('Ja'), URLHelper::getURL("?cmd=really_delete&keyword=".urlencode($keyword)."&version=$version&dellatest=$islatest"));
    $lnk = "?keyword=".urlencode($keyword); // what to do when delete is aborted
    if (!$islatest) $lnk .= "&version=$version";
    $msg .= LinkButton::createCancel(_("Nein"), URLHelper::getURL($lnk));

    PageLayout::postMessage(MessageBox::info($msg));
    return $version;
}

/**
* Display yes/no dialog to confirm complete WikiPage deletion.
*
* @param    string  WikiPage name
*
**/
function showDeleteAllDialog($keyword) {
    global $perm;
    if (!$perm->have_studip_perm("tutor", Context::getId())) {
        throw new AccessDeniedException(_('Sie haben keine Berechtigung, Seiten zu löschen.'));
    }
    $msg= sprintf(_("Wollen Sie die Seite %s wirklich vollständig - mit allen Versionen - löschen?"), "<b>".htmlReady($keyword)."</b>") . "<br>\n";
    if ($keyword=="WikiWikiWeb") {
        $msg .= "<p>" . _("Sie sind im Begriff die Startseite zu löschen, die dann durch einen leeren Text ersetzt wird. Damit wären auch alle anderen Seiten nicht mehr direkt erreichbar.") . "</p>";
    } else {
        $numbacklinks=count(getBacklinks($keyword));
        if ($numbacklinks == 0) {
            $msg .= _("Auf diese Seite verweist keine andere Seite.").'<br>';
        } else if ($numbacklinks == 1) {
            $msg .= _("Auf diese Seite verweist 1 andere Seite.").'<br>';
        } else {
            $msg .= sprintf(_("Auf diese Seite verweisen %s andere Seiten."), count(getBacklinks($keyword)));
        }
    }
    //TODO: modaler dialog benutzen
    $msg.="<a href=\"".URLHelper::getLink("?cmd=really_delete_all&keyword=".urlencode($keyword))."\">" .Button::createAccept(_('Ja')) . "</a>&nbsp; \n";
    $lnk = "?keyword=".urlencode($keyword); // what to do when delete is aborted
    if (!$islatest) $lnk .= "&version=$version";
    $msg.="<a href=\"".URLHelper::getLink($lnk)."\">" . Button::createCancel(_('Nein')) . "</a>\n";
    PageLayout::postMessage(MessageBox::info($msg));
}



/**
* Delete WikiPage version and adjust backlinks.
*
* @param    string  WikiPage name
* @param    string  WikiPage version
* @param    string  ID of seminar/einrichtung
*
* @return   string  WikiPage name to display next
*
**/
function deleteWikiPage($keyword, $version, $range_id) {
    global $perm, $dellatest;
    if (!$perm->have_studip_perm("tutor", Context::getId())) {
        throw new AccessDeniedException(_('Sie haben keine Berechtigung, Seiten zu löschen.'));
    }
    $lv=getLatestVersion($keyword, Context::getId());
    if ($lv["version"] != $version) {
        throw new InvalidArgumentException(_('Die Version, die Sie löschen wollen, ist nicht die aktuellste. Überprüfen Sie, ob inzwischen eine aktuellere Version erstellt wurde.'));
    }

    $wp = WikiPage::find([$range_id, $keyword, $version]);
    if ($wp) {
        $wp->delete();
    }

    if (!keywordExists($keyword)) { // all versions have gone
        $addmsg = '<br>' . sprintf(_("Damit ist die Seite %s mit allen Versionen gelöscht."),'<b>'.htmlReady($keyword).'</b>');
        $newkeyword = "WikiWikiWeb";
    } else {
        $newkeyword = $keyword;
        $addmsg = "";
    }
    $message = MessageBox::info(sprintf(_('Version %s der Seite %s gelöscht.'), htmlReady($version), '<b>'.htmlReady($keyword).'</b>') . $addmsg);
    PageLayout::postMessage($message);
    if ($dellatest) {
        $lv=getLatestVersion($keyword, Context::getId());
        if ($lv) {
            $body="";
        } else {
            $body=$lv["body"];
        }
        refreshBacklinks($keyword, $body);
    }
    return $newkeyword;
}

/**
* Delete complete WikiPage with all versions and adjust backlinks.
*
* @param    string  WikiPage name
* @param    string  ID of seminar/einrichtung
*
**/
function deleteAllWikiPage($keyword, $range_id) {
    global $perm;
    if (!$perm->have_studip_perm("tutor", Context::getId())) {
        throw new AccessDeniedException(_('Sie haben keine Berechtigung, Seiten zu löschen.'));
    }

    WikiPage::deleteBySQL("keyword = ? AND range_id = ?", [$keyword, $range_id]);
    $message = MessageBox::info(sprintf(_('Die Seite %s wurde mit allen Versionen gelöscht.'), '<b>'.htmlReady($keyword).'</b>'));
    PageLayout::postMessage($message);
    refreshBacklinks($keyword, "");
    return "WikiWikiWeb";
}



/**
* List all topics in this seminar's wiki
*
* @param  mode  string  Either "all" or "new", affects default sorting and page title.
* @param  sortby  string  Different sortings of entries.
**/
function listPages($mode, $sortby = NULL)
{
    $lastlogindate = null;
    if ($mode === 'all') {
        $selfurl = '?view=listall';
        $sort = "ORDER by MAX(chdate) DESC"; // default sort order for "all pages"
        $nopages = _('In dieser Veranstaltung wurden noch keine WikiSeiten angelegt.');

        // help texts
        $help = _('Zeigt eine tabellarische Übersicht aller Wiki-Seiten an.');
        Helpbar::get()->ignoreDatabaseContents();
        Helpbar::get()->addPlainText('', $help);
    } else if ($mode === 'new') {
        $lastlogindate = object_get_visit(Context::getId(), 'wiki');

        $selfurl = '?view=listnew';
        $sort = "ORDER by MAX(chdate)"; // default sort order for "new pages"
        $nopages = _('Seit Ihrem letzten Login gab es keine Änderungen.');

        // help texts
        $help = _('Zeigt eine tabellarische Übersicht neu erstellter bzw. bearbeiteter Wiki-Seiten an.');
        Helpbar::get()->ignoreDatabaseContents();
        Helpbar::get()->addPlainText('', $help);
    } else {
        throw new InvalidArgumentException(_('Fehler! Falscher Anzeigemodus:') . $mode);
    }

    $titlesortlink   = 'title';
    $versionsortlink = 'version';
    $changesortlink  = 'lastchange';

    switch ($sortby) {
        case 'title':
            // sort by keyword, prepare link for descending sorting
            $sort = "ORDER BY keyword";
            $titlesortlink = 'titledesc';
            break;
        case 'titledesc':
            // sort descending by keyword, prep link for asc. sort
            $sort = "ORDER BY keyword DESC";
            break;
        case 'version':
            $sort = "ORDER BY MAX(version) DESC, keyword ASC";
            $versionsortlink = 'versiondesc';
            break;
        case 'versiondesc':
            $sort = "ORDER BY MAX(version), keyword ASC";
            break;
        case 'lastchange':
            // sort by change date, default: newest first
            $sort = "ORDER BY MAX(chdate) DESC, keyword ASC";
            $changesortlink = 'lastchangedesc';
            break;
        case 'lastchangedesc':
            // sort by change date, oldest first
            $sort = "ORDER BY MAX(chdate), keyword ASC";
            break;
    }

    if ($mode === 'all') {
        $query = "SELECT keyword
                  FROM wiki
                  WHERE range_id = ?
                  GROUP BY keyword
                  {$sort}";
        $parameters = [Context::getId()];
    } else if ($mode === 'new') {
        $query = "SELECT keyword
                  FROM wiki
                  WHERE range_id = ? AND chdate > ?
                  GROUP BY keyword
                  {$sort}";
        $parameters = [Context::getId(), $lastlogindate];
    }

    $pages = DBManager::get()->fetchFirst($query, $parameters, function ($keyword) {
        return WikiPage::findLatestPage(Context::getId(), $keyword);
    });

    if (count($pages) === 0) {
        PageLayout::postInfo($nopages);
    } else {
        $template = $GLOBALS['template_factory']->open('wiki/list.php');
        $template->mode            = $mode;
        $template->url             = $selfurl;
        $template->titlesortlink   = $titlesortlink;
        $template->versionsortlink = $versionsortlink;
        $template->changesortlink  = $changesortlink;
        $template->pages           = $pages;
        $template->lastlogindate   = $lastlogindate;
        echo $template->render();
    }

    if ($mode === 'all'){
        $help_url = format_help_url('Basis.VerschiedenesFormat');

        $widget = Sidebar::get()->addWidget(new ExportWidget());
        $widget->addLink(
            _('PDF-Ausgabe aller Wiki-Seiten'),
            URLHelper::getURL('?view=exportall_pdf', ['sortby' => $sortby]),
            Icon::create('file-pdf'),
            ['target' => '_blank']
        );
        $widget->addLink(
            _('Druckansicht aller Wiki-Seiten'),
            URLHelper::getURL('?view=wikiprintall'),
            Icon::create('print'),
            ['target' => '_blank']
        );
    }

    showPageFrameEnd([]);
}

/**
* Search Wiki
*
* @param  searchfor  string  String to search for.
* @param  searchcurrentversions  bool  it true, only consider most recent versions or pages
* @param  keyword  string  last shown page or keyword for local (one page) search
* @param keyword bool if localsearch is set, only one page (all versions) is searched
**/
function searchWiki($searchfor, $searchcurrentversions, $keyword, $localsearch)
{
    $range_id = Context::getId();
    $result   = null;

    // check for invalid search string
    if (mb_strlen($searchfor) < 3) {
        $invalid_searchstring = true;
    } else if ($localsearch && !$keyword) {
        $invalid_searchstring = true;
    } else {
        // make search string
        if ($localsearch) {
            $query = "SELECT *
                      FROM wiki
                      WHERE range_id = ? AND body LIKE CONCAT('%', ?, '%') AND keyword = ?
                      ORDER BY version DESC";
            $parameters = [$range_id, htmlReady($searchfor), $keyword];
        } else if (!$searchcurrentversions) {
            // search in all versions of all pages
            $query = "SELECT *
                      FROM wiki
                      WHERE range_id = ? AND body LIKE CONCAT('%', ?, '%')
                      ORDER BY keyword ASC, version DESC";
            $parameters = [$range_id, htmlReady($searchfor)];
        } else {
            // search only latest versions of all pages
            $query = "SELECT *
                      FROM wiki AS w1
                      WHERE range_id = ? AND w1.body LIKE CONCAT('%', ?, '%') AND version = (
                          SELECT MAX(version)
                          FROM wiki AS w2
                          WHERE w2.range_id = ? AND w2.keyword = w1.keyword
                      )
                      ORDER BY w1.keyword ASC";
             $parameters = [$range_id, htmlReady($searchfor), $range_id];
        }
        $pages = DBManager::get()->fetchAll($query, $parameters, function ($row) {
            return WikiPage::buildExisting($row);
        });

        $pages = array_filter($pages, function ($page) {
            return $page->isVisibleTo($GLOBALS['user']);
        });
    }

    // quit if no pages found / search string was invalid
    if ($invalid_searchstring || count($pages) == 0) {
        if ($invalid_searchstring) {
            $message = MessageBox::error(_('Suchbegriff zu kurz. Geben Sie mindestens drei Zeichen ein.'));
        } else {
            $message = MessageBox::info(sprintf(_("Die Suche nach &raquo;%s&laquo; lieferte keine Treffer."), htmlReady($searchfor)));
        }
        PageLayout::postMessage($message);
        showWikiPage($keyword, NULL);
        return;
    }

    showPageFrameStart();

    // show hits
?>
<table class="default">
    <caption>
        <?= sprintf(_('Treffer für Suche nach %s'), '&raquo;' . htmlReady($searchfor) . '&laquo;') ?>
    <? if ($localsearch): ?>
        <?= sprintf(_('in allen Versionen der Seite %s'), '&raquo;' . htmlReady($keyword) . '&laquo;') ?>
    <? elseif ($searchcurrentversions): ?>
        <?= _('in aktuellen Versionen') ?>
    <? else: ?>
        <?= _('in allen Versionen') ?>
    <? endif; ?>
    </caption>
    <colgroup>
        <col width="10%">
        <col width="65%">
        <col width="25%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Seite') ?></th>
            <th><?= _('Treffer') ?></th>
            <th><?= _('Version') ?></th>
        </tr>
    </thead>
    <tbody>
<?php
    $c=1;
    $last_keyword="";
    $last_keyword_count=0;
    foreach ($pages as $result) {
        if (!$localsearch) {
            // don't display more than one hit in a page's versions
            // offer link instead
            if ($result['keyword']==$last_keyword) {
                $last_keyword_count++;
                continue;
            } else if ($last_keyword_count>0) {
                print($tdheadleft."&nbsp;".$tdtail);
                if ($last_keyword_count==1) {
                    $hitstring=_("Weitere Treffer in %s älteren Version. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
                } else {
                    $hitstring=_("Weitere Treffer in %s älteren Versionen. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
                }
                print($tdheadleft."<em>".sprintf($hitstring,$last_keyword_count,"<b><a href=\"".URLHelper::getLink("?view=search&searchfor=$searchfor&keyword=".urlencode($last_keyword)."&localsearch=1")."\">","</a></b>")."</em>".$tdtail);
                print($tdheadleft."&nbsp;".$tdtail);
                print("</tr>");
            }
            $last_keyword=$result['keyword'];
            $last_keyword_count=0;
        }

        $tdheadleft="<td>";
        $tdheadcenter="<td>";
        $tdtail="</td>";

        print("<tr>");
        // Pagename
        print($tdheadleft);
        print("<a href=\"".URLHelper::getLink("?keyword=".$result['keyword']."&version=".$result['version']."&hilight=$searchfor&searchfor=$searchfor")."\">");
        print(htmlReady($result['keyword'])."</a>");
        print($tdtail);
        // display hit previews
        $offset=0; // step through text
        $ignore_next_hits=0; // don't show hits more than once
        $first_line=1; // don't print <br> before first hit
        print($tdheadleft);
        // find all occurences
        while ($offset < mb_strlen($result['body'])) {
            $pos=mb_stripos($result['body'], $searchfor,$offset);
            if ($pos===FALSE) break;
            $offset=$pos+1;
            if (($ignore_next_hits--)>0) {
                // if more than one occurence is found
                // in a fragment to be displayed,
                // the fragment is only shown once
                continue;
            }
            // show max 80 chars
            $fragment = '';
            $split_fragment = preg_split('/('.preg_quote($searchfor,'/').')/i', mb_substr($result['body'],max(0, $pos-40), 80), -1, PREG_SPLIT_DELIM_CAPTURE);
            for ($i = 0; $i < count($split_fragment); ++$i) {
                if ($i % 2) {
                    $fragment .= '<span style="background-color:#FFFF88">';
                    $fragment .= htmlready($split_fragment[$i], false);
                    $fragment .= '</span>';
                } else {
                    $fragment .= htmlready($split_fragment[$i], false);
                }
            }
            $found_in_fragment = (count($split_fragment) - 1) / 2; // number of hits in fragment
            $ignore_next_hits = ($found_in_fragment > 1) ? $found_in_fragment - 1 : 0;
            print("...".$fragment."...");
            print "<br>";
        }
        print($tdtail);
        // version info
        print($tdheadleft);
        print(date("d.m.Y, H:i", $result['chdate'])." ("._("Version")." ".$result['version'].")");
        print($tdtail);
        print "</tr>";

    }

    if (!$localsearch && $last_keyword_count>0) {
        print("<tr>");
        print($tdheadleft."&nbsp;".$tdtail);
        if ($last_keyword_count==1) {
            $hitstring=_("Weitere Treffer in %s älteren Version. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
        } else {
            $hitstring=_("Weitere Treffer in %s älteren Versionen. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
        }
        print($tdheadleft."<em>".sprintf($hitstring,$last_keyword_count,"<b><a href=\"".URLHelper::getLink("?view=search&searchfor=$searchfor&keyword=".urlencode($last_keyword)."&localsearch=1")."\">","</a></b>")."</em>".$tdtail);
        print($tdheadleft."&nbsp;".$tdtail);
        print("</tr>");
    }

    echo "</tbody></table><p>&nbsp;</p>";

    // search
    $widget = new SearchWidget(URLHelper::getLink('?view=search&keyword=' . urlencode($keyword)));
    $widget->addNeedle(_('Im Wiki suchen'), 'searchfor', true);
    $widget->addFilter(_('Nur in aktuellen Versionen'), 'searchcurrentversions');
    Sidebar::get()->addWidget($widget);

    showPageFrameEnd([]);
}

/**
* Display edit form for wiki page.
*
* @param    string  keyword WikiPage name
* @param    array   wikiData    Array from DB with WikiPage data
* @param    string  user_id     Internal user id
* @param    string  backpage    Page to display if editing is aborted
*
**/
function wikiEdit($keyword, $wikiData, $user_id, $backpage=NULL)
{
    if (!$wikiData || $wikiData->isNew()) {
        $body     = '';
        $version  = 0;
        $lastpage = $backpage;
    } else {
        $body     = $wikiData->body;
        $version  = $wikiData->version;
        $lastpage = null;
    }
    releaseLocks($keyword); // kill old locks
    $locks=getLock($keyword, $user_id);
    $cont='';
    if ($locks && $lock['user_id'] !== $user_id) {
        $message = MessageBox::info(sprintf(_("Die Seite wird eventuell von %s bearbeitet."), htmlReady($locks)), [_("Wenn Sie die Seite trotzdem ändern, kann ein Versionskonflikt entstehen."), _("Es werden dann beide Versionen eingetragen und müssen von Hand zusammengeführt werden."),  _("Klicken Sie auf Abbrechen, um zurückzukehren.")]);
        PageLayout::postMessage($message);
    }
    if ($keyword === 'toc') {
        $message = MessageBox::info(_("Sie bearbeiten die QuickLinks."), [_("Verwenden Sie Aufzählungszeichen (-, --, ---), um Verweise auf Seiten hinzuzufügen.")]);
        PageLayout::postMessage($message);
        if (!$body) {
            $body=_("- WikiWikiWeb\n- BeispielSeite\n-- UnterSeite1\n-- UnterSeite2");
        }
    }

    $template = $GLOBALS['template_factory']->open('wiki/edit.php');
    $template->keyword  = $keyword;
    $template->version  = $version;
    $template->lastpage = $lastpage;
    $template->body     = $body;
    echo $template->render();

    // help texts
    Helpbar::get()->ignoreDatabaseContents();

    $help = _('Der Editor dient zum Einfügen und Ändern von beliebigem Text.');
    Helpbar::get()->addPlainText('', $help);

    $tip = _('Links entstehen automatisch aus Wörtern, die von zwei paar eckigen Klammern umgeben sind (Beispiel: [nop][[[/nop]%%Schlüsselwort%%[nop]]][/nop]');
    Helpbar::get()->addPlainText(_('Tip'), $tip, Icon::create('info-circle'));
}

/**
* Display wiki page for print.
*
* @param    string  keyword WikiPage name
* @param    string  version WikiPage version
*
**/
function printWikiPage($keyword, $version)
{
    $wikiData=getWikiPage($keyword, $version);
    PageLayout::removeStylesheet('studip-base.css');
    PageLayout::addStylesheet('print.css'); // use special stylesheet for printing
    include ('lib/include/html_head.inc.php'); // Output of html head
    echo "<p><em>" . htmlReady(Context::getHeaderLine()) ."</em></p>";
    echo "<h1>" . htmlReady($keyword) ."</h1>";
    echo "<p><em>";
    echo sprintf(_("Version %s, letzte Änderung %s von %s."), $wikiData['version'],
    date("d.m.Y, H:i", $wikiData['chdate']), get_fullname($wikiData['user_id'], 'full', 1));
    echo "</em></p>";
    echo "<hr>";
    echo wikiReady($wikiData['body'], TRUE, FALSE, "none");
    echo "<hr><p><font size=-1>created by Stud.IP Wiki-Module ";
    echo date("d.m.Y, H:i", time());
    echo " </font></p>";
    include ('lib/include/html_end.inc.php');
}

function exportWikiPagePDF($keyword, $version)
{
    $wikiData=getWikiPage($keyword,$version);

    $document = new ExportPDF();
    $document->SetTitle(_('Wiki: ') . $keyword);
    $document->setHeaderTitle(sprintf(_("Wiki von \"%s\""), Context::get()->Name));
    $document->setHeaderSubtitle(sprintf(_("Seite: %s"), $keyword));
    $document->addPage();
    $document->addContent(deleteWikiLinks($wikiData["body"]));
    $document->dispatch(Context::getHeaderLine() ." - ".$keyword);
}

function exportAllWikiPagesPDF($mode, $sortby)
{
    $titlesortlink   = 'title';
    $versionsortlink = 'version';
    $changesortlink  = 'lastchange';

    switch ($sortby) {
        case 'title':
            // sort by keyword, prepare link for descending sorting
            $sort = "ORDER BY keyword";
            break;
        case 'titledesc':
            // sort descending by keyword, prep link for asc. sort
            $sort = "ORDER BY keyword DESC";
            break;
        case 'version':
            $sort = "ORDER BY MAX(version) DESC, keyword ASC";
            break;
        case 'versiondesc':
            $sort = " ORDER BY MAX(version), keyword ASC";
            break;
        case 'lastchange':
            // sort by change date, default: newest first
            $sort = " ORDER BY MAX(chdate) DESC, keyword ASC";
            break;
        case 'lastchangedesc':
            // sort by change date, oldest first
            $sort = " ORDER BY MAX(chdate), keyword ASC";
            break;
    }

    $query = "SELECT keyword
              FROM wiki
              WHERE range_id = ?
              GROUP BY keyword
              {$sort}";

    $statement = DBManager::get()->prepare($query);
    $statement->execute([Context::getId()]);

    $document = new ExportPDF();
    $document->SetTitle(_('Wiki: ') . Context::get()->name);
    $document->setHeaderTitle(sprintf(_('Wiki von "%s"'), Context::get()->name));

    while ($keyword = $statement->fetch(PDO::FETCH_COLUMN)) {
        $page = WikiPage::findLatestPage(Context::getId(), $keyword);
        if (!$page->isVisibleTo($GLOBALS['user'])) {
            continue;
        }

        $document->setHeaderSubtitle(sprintf(_('Seite: %s'), $page->keyword));
        $document->addPage();

        // We need the @ in front since TCPDF might throw warning that can lead
        // to errors viewing the document
        @$document->addContent(deleteWikiLinks($page->body));
    }

    $document->dispatch(Context::getHeaderLine());
}

function deleteWikiLinks($keyword) {
    $keyword = preg_replace('/\[\[[^|\]]*\|([^]]*)\]\]/', '$1', $keyword);
    $keyword = preg_replace('/\[\[([^|\]]*)\]\]/', '$1', $keyword);
    return $keyword;
}

/**
* Show export all dialog
*
**/
function exportWiki() {
    showPageFrameStart();
    $message = MessageBox::info(_('Alle Wiki-Seiten werden als große HTML-Datei zusammengefügt und in einem neuen Fenster angezeigt. Von dort aus können Sie die Datei abspeichern.'));
    PageLayout::postMessage($message);

    print '<div style="text-align: center;">';
    print LinkButton::create( _('Weiter'). ' >>' , URLHelper::getURL("?view=wikiprintall"), ['id'=>'wiki_export','title'=>_('Seiten exportieren'),'target'=>'_blank' ]);
    echo '</div>'; // end of content area
    showPageFrameEnd();
}

/**
* Print HTML-dump of all wiki pages.
*
* @param    string  ID of veranstaltung/einrichtung
* @param    string  Short title (header) of veranstaltung/einrichtung
*
**/
function printAllWikiPages($range_id, $header) {
    echo getAllWikiPages($range_id, $header, TRUE);

    showPageFrameEnd();
}

/**
* Return HTML-dump of all wiki pages.
* Implements an iterative breadth-first traversal of WikiPage-tree.
*
* @param string $range_id ID of veranstaltung/einrichtung
* @param string $header   Short title (header) of veranstaltung/einrichtung
* @param bool   $fullhtml Include html/head/body tags?
* @return string
**/
function getAllWikiPages($range_id, $header, $fullhtml = true) {
    $query = "SELECT DISTINCT keyword FROM wiki WHERE range_id = ? ORDER BY keyword DESC";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$range_id]);
    $allpages = $statement->fetchAll(PDO::FETCH_COLUMN);

    $out = [];
    $visited = []; // holds names of already visited pages
    $tovisit = []; // holds names of pages yetto visit/expand

    $tovisit[] = 'WikiWikiWeb'; // start with top level page
    if ($fullhtml) {
        $out[] = '<html><head><title>' . htmlReady($header) . '</title></head>';
        $out[] = '<body>';
    }
    $out[]="<p><a name=\"top\"></a><em>" . htmlReady($header) ."</em></p>";
    while (!empty($tovisit)) { // while there are still pages left to visit
        $pagename = array_shift($tovisit);
        if (!in_array($pagename, $visited)){
            $pagedata = WikiPage::findLatestPage($range_id, $pagename);

            // consider only pages with content and that are visible to the user
            if ($pagedata && $pagedata->isVisibleTo($GLOBALS['user'])) {
                $visited[] = $pagename;
                $linklist = getWikiLinks($pagedata['body']);
                foreach ($linklist as $l) {
                    // add pages not visited yet to queue
                    if (!in_array($l, $visited)) {
                        $tovisit[] = $l; // breadth-first
                    }
                }
                $out[] = "<hr><a name=\"$pagename\"></a><h1>" . htmlReady($pagename) . "</h1>";
                $out[] = "<font size=-1><p><em>";
                $out[] = sprintf(_("Version %s, letzte Änderung %s von %s."), $pagedata['version'], date("d.m.Y, H:i", $pagedata['chdate']), get_fullname($pagedata['user_id'], 'full', 1));
                $out[] = "</em></p></font>";
                // output is html without comments
                $out[] = wikiReady($pagedata['body'], true, false, 'none');
                $out[] = '<p><font size=-1>(<a href="#top">' . _("nach oben") . '</a>)</font></p>';
            }
        }
        if (empty($tovisit)){
            while (!empty($allpages)) {
                $l = array_pop($allpages);
                if (!in_array($l, $visited)) {
                    $tovisit[] = $l;
                    break;
                }
            }
        }
    }
    $out[] = '<hr><p><font size=-1>' . _('exportiert vom Stud.IP Wiki-Modul') . ' , ';
    $out[] = date('d.m.Y, H:i');
    $out[] = ' </font></p>';
    if ($fullhtml) {
        $out[] = '</body></html>';
    }
    return implode("\n",$out);
}


/**
* Display start of page "frame", i.e. open correct table structure.
*
**/
function showPageFrameStart() {
    echo '<div id="main_content" role="main">';
}

/**
* Display the right and bottom part of a page "frame".
*
* Renders an infobox and closes the table.
*
* @param    array   ready to pass to print_infoxbox()
*
**/
function showPageFrameEnd()
{
    echo '</div>';
}

/**
* Returns an infobox string holding information and action links for
* current page.
* If newest version is displayed, infobox includes backlinks.
*
* @param    string  WikiPage name
* @param    bool    Is version displayed latest version?
*
**/
function getShowPageInfobox($keyword, $latest_version)
{
    $edit_perms = CourseConfig::get(Context::getId())->WIKI_COURSE_EDIT_RESTRICTED ? 'tutor' : 'autor';

    $versions = getWikiPageVersions($keyword);

    if (!$latest_version) {
        $message = sprintf(
            _('Sie betrachten eine alte Version, die nicht mehr geändert werden kann. Verwenden Sie dazu die %saktuelle Version%s.'),
            '<a href="' . URLHelper::getLink('', compact('keyword')) . '">',
            '</a>'
        );
        PageLayout::postInfo($message);
    }

    $sidebar = Sidebar::get();

    // Table of Contents/QuickLinks
    $widget = $sidebar->addWidget(new ListWidget());
    $widget->setTitle(_('QuickLinks'));

    $toccont = get_toc_content();
    $toccont_empty = !trim(strip_tags($toccont));

    if ($GLOBALS['perm']->have_studip_perm($edit_perms, Context::getId())) {
        $extra = sprintf(
            '<a href="%s">%s</a>',
            URLHelper::getLink('?keyword=toc&view=edit'),
            $toccont_empty
                ? Icon::create('add')->asImg(['title' => _('erstellen')])
                : Icon::create('edit')->asImg(['title' => _('bearbeiten')])
        );
        $widget->setExtra($extra);
    }

    $element = new WidgetElement($toccont_empty ? _('Keine QuickLinks vorhanden') : $toccont);
    $element->icon = Icon::create('link-intern');
    $widget->addElement($element);

    // Actions:
    $widget = $sidebar->addWidget(new ActionsWidget());
    if ($GLOBALS['perm']->have_studip_perm($edit_perms, Context::getId())) {
        $widget->addLink(
            _('Neue Wiki-Seite anlegen'),
            URLHelper::getURL('dispatch.php/wiki/create', compact('keyword')),
            Icon::create('add')
        )->asDialog('size=auto');
    }

    if ($GLOBALS['perm']->have_studip_perm('tutor', Context::getId())) {
        if (Context::getType() == Context::COURSE) {
            $widget->addLink(
                _('Seiten importieren'),
                URLHelper::getURL('dispatch.php/wiki/import/' . Context::getId()),
                Icon::create('wiki+add')
            )->asDialog('size=auto');
        }

        // Change wiki course permissions
        $widget->addLink(
            _('Wiki-Einstellungen ändern'),
            URLHelper::getURL('dispatch.php/wiki/change_courseperms', compact('keyword')),
            Icon::create('admin')
        )->asDialog('size=auto');
    }

    // Backlinks
    if ($latest_version) {
        $widget = $sidebar->addWidget(new LinksWidget());
        $widget->setTitle(_('Seiten, die auf diese Seite verweisen'));
        foreach(getBacklinks($keyword) as $backlink) {
            $widget->addLink(
                $backlink,
                URLHelper::getURL('', ['keyword' => $backlink])
            );
        }
    }

    // Ansichten
    $widget = $sidebar->addWidget(new ViewsWidget());
    $widget->addLink(
        _('Standard'),
        URLHelper::getURL('?view=show', compact('keyword')),
        Icon::create('wiki')
    )->setActive(true);
    if (count($versions) >= 1) {
        $widget->addLink(
            _('Textänderungen anzeigen'),
            URLHelper::getURL('?view=diff', compact('keyword'))
        );
        $widget->addLink(
            _('Text mit Autor/-innenzuordnung anzeigen'),
            URLHelper::getURL('?view=combodiff', compact('keyword'))
        );
    }

    // Suche
    $widget = $sidebar->addWidget(new SearchWidget(URLHelper::getURL('?view=search', compact('keyword'))));
    $widget->addNeedle(_('Im Wiki suchen'), 'searchfor', true);
    $widget->addFilter(_('Nur in aktuellen Versionen'), 'searchcurrentversions');

    // Versionen
    if (count($versions) > 0) {
        $widget = $sidebar->addWidget(new SelectWidget(
            _('Alte Versionen dieser Seite'),
            URLHelper::getLink('', compact('keyword')),
            'version'
        ));
        $widget->addElement(new SelectElement('', _('Aktuelle Version')));
        foreach ($versions as $version) {
            $widget->addElement(new SelectElement(
                $version['version'],
                sprintf(
                    _('Version %u (%s)'),
                    $version['version'],
                    date('d.m.Y, H:i', $version['chdate'])
                ),
                $version['version'] == Request::int('version', 0)
            ));
        }
    }

    // Kommentare
    $widget = $sidebar->addWidget(new OptionsWidget(), 'comments');
    $widget->setTitle(_('Kommentare'));
    $widget->addRadioButton(
        _('einblenden'),
        URLHelper::getURL('?wiki_comments=all', compact('keyword')),
        $GLOBALS['show_wiki_comments'] === 'all'
    );
    $widget->addRadioButton(
        _('als Icons einblenden'),
        URLHelper::getURL('?wiki_comments=icon', compact('keyword')),
        $GLOBALS['show_wiki_comments'] === 'icon'
    );
    $widget->addRadioButton(
        _('ausblenden'),
        URLHelper::getURL('?wiki_comments=none', compact('keyword')),
        $GLOBALS['show_wiki_comments'] === 'none'
    );

    // Exportfunktionen
    $version = Request::int('version') ?: '';

    $widget = $sidebar->addWidget(new ExportWidget());
    $widget->addLink(
        _('Druckansicht'),
        URLHelper::getURL('?view=wikiprint', compact('keyword', 'version')),
        Icon::create('print'),
        ['target' => '_blank']
    );
    $widget->addLink(
        _('PDF-Ausgabe'),
        URLHelper::getURL('?view=export_pdf', compact('keyword', 'version')),
        Icon::create('file-pdf'),
        ['target' => '_blank']
    );

    return [];
}

/**
* Returns an infobox string holding information and action links for
* diff view of current page.
*
* @param    string  WikiPage name
*
**/
function getDiffPageInfobox($keyword) {

    $versions = getWikiPageVersions($keyword);

    // Aktuelle Version
    $widget = Sidebar::get()->addWidget(new ViewsWidget());
    $widget->addLink(
        _('Standard'),
        URLHelper::getURL('?view=show', compact('keyword'))
    );
    if (count($versions) >= 1) {
        $widget->addLink(
            _('Textänderungen anzeigen'),
            URLHelper::getURL('?view=diff', compact('keyword'))
        )->setActive(Request::option('view') === 'diff');
        $widget->addLink(
            _('Text mit Autor/-innenzuordnung anzeigen'),
            URLHelper::getURL('?view=combodiff', compact('keyword'))
        )->setActive(Request::option('view') === 'combodiff');
    }

    // Versionen
    if (count($versions) > 0) {
        $widget = Sidebar::get()->addWidget(new SelectWidget(
            _('Alte Versionen dieser Seite'),
            URLHelper::getLink('?keyword=' . urlencode($keyword)),
            'version'
        ));
        $widget->addElement(new SelectElement('', _('Aktuelle Version')));
        foreach ($versions as $version) {
            $widget->addElement(new SelectElement(
                $version['version'],
                sprintf(
                    _('Version %u (%s)'),
                    $version['version'],
                    date('d.m.Y, H:i', $version['chdate'])
                ),
                $version['version'] == Request::int('version', 0)
            ));
        }
    }

    return [];
}

function get_toc_toggler() {
    $toc=getWikiPage("toc",0);
    if (!$toc) return '';
    $cont="";
    $ToggleText=[_("verstecken"),_("anzeigen")];
    $cont.="<script type=\"text/javascript\">
        function toggle(obj) {
            var elstyle = document.getElementById(obj).style;
            var text    = document.getElementById(obj + \"tog\");
            if (elstyle.display == 'none') {
            elstyle.display = 'block';
            text.innerHTML = \"{$ToggleText[0]}\";
            } else {
            elstyle.display = 'none';
            text.innerHTML = \"{$ToggleText[1]}\";
            }
        }
        </script>";
    $cont.="<span class='wikitoc_toggler'> (<a id=\"00toctog\" href=\"javascript:toggle('00toc');\">{$ToggleText[0]}</a>)</span>";
    return $cont;
}

function get_toc_content() {
    // Table of Contents / Wiki navigation
    $toc = getWikiPage('toc',0);
    if (!$toc) {
        return '';
    }

    $toccont  = '<div class="wikitoc" id="00toc">';
    $toccont .= wikiReady($toc['body'], true, false, $show_comments);
    $toccont .= '</div>';
    return $toccont;
}

/**
* Display wiki page.
*
* @param    string  WikiPage name
* @param    string  WikiPage version
* @param    string  ID of special dialog to be printed (delete, delete_all)
* @param    string  Comment show mode (all, none, icon)
*
**/
function showWikiPage($keyword, $version, $special="", $show_comments="icon", $hilight=NULL) {
    // show dialogs if any..
    //
    if ($special === 'delete') {
        $version = showDeleteDialog($keyword, $version);
    } else if ($special === 'delete_all') {
        showDeleteAllDialog($keyword);
    }

    $wikiData = getWikiPage($keyword, $version);
    $content = wikiReady($wikiData["body"], TRUE, FALSE, $show_comments);

    if ($hilight) {
        // Highlighting must only take place outside HTML tags, so
        // 1. save all html tags in array $founds[0]
        // 2. replace all html tags with  \007\007
        // 3. highlight
        // 4. replace all \007\007 with corresponding saved tags
        $founds = [];
        preg_match_all("/<[^>].*>/U", $content, $founds);
        $content = preg_replace("/<[^>].*>/U", "\007\007", $content);
        $content = preg_replace("/(".preg_quote(htmlReady($hilight), "/").")/i", "<span style='background-color:#FFFF88'>\\1</span>", $content, -1);
        foreach($founds[0] as $f) {
            $content = preg_replace("/\007\007/", $f, $content, 1);
        }
    }

    $template = $GLOBALS['template_factory']->open('wiki/show.php');
    $template->wikipage = $wikiData;
    $template->content  = $content;
    echo $template->render();

    getShowPageInfobox($keyword, $wikiData->isLatestVersion());
}

/**
* Display Page diffs, restrictable to recent versions
*
* @param    string  WikiPage name
* @param    string  Only show versions newer than this timestamp
*
**/
function showDiffs($keyword, $versions_since)
{
    $query = "SELECT *
              FROM wiki
              WHERE keyword = ? AND range_id = ?
              ORDER BY version DESC";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$keyword, Context::getId()]);
    $versions = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($versions) === 0) {
        throw new InvalidArgumentException(_('Es gibt keine zu vergleichenden Versionen.'));
    }

    $content = "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";

    $version     = array_shift($versions);
    $last        = $version['body'];
    $lastversion = $version['version'];
    $zusatz      = getZusatz($version);

    foreach ($versions as $version) {
        $content .= '<tr>';
        $current        = $version['body'];
        $currentversion = $version['version'];

        $diffarray = '<b><font size=-1>'. _("Änderungen zu") . " </font> $zusatz</b><p>";
        $diffarray .= "<table cellpadding=0 cellspacing=0 border=0 width=\"100%\">\n";
        $diffarray .= do_diff($current, $last);
        $diffarray .= "</table>\n";
        $content .= printcontent(0, 0, $diffarray, '', false);
        $content .= '</tr>';

        $last        = $current;
        $lastversion = $currentversion;
        $zusatz      = getZusatz($version);
        if ($versions_since && $version['chdate'] < $versions_since) {
            break;
        }
    }
    $content .= '</table>';

    $wikiData = getWikiPage($keyword, null);

    $template = $GLOBALS['template_factory']->open('wiki/show.php');
    $template->wikipage = $wikiData;
    $template->content  = $content;
    echo $template->render();

    getDiffPageInfobox($keyword);

    // help texts
    $help = _('Die Ansicht zeigt den Verlauf der Textänderungen einer Wiki-Seite.');
    Helpbar::get()->ignoreDatabaseContents();
    Helpbar::get()->addPlainText('', $help);
}

/////////////////////////////////////////////////
// DIFF funcitons adapted from:
// PukiWiki - Yet another WikiWikiWeb clone.
// http://www.pukiwiki.org (GPL'd)
//
//
//
function do_diff($strlines1,$strlines2)
{
    $plus="<td width=\"3\" bgcolor=\"green\">&nbsp;</td>";
    $minus="<td width=\"3\" bgcolor=\"red\">&nbsp;</td>";
    $equal="<td width=\"3\" bgcolor=\"grey\">&nbsp;</td>";
    $obj = new line_diff($plus, $minus, $equal);
    $str = $obj->str_compare($strlines1,$strlines2);
    return $str;
}

function toDiffLineArray($lines, $who) {
    $dla = [];
    $lines = explode("\n",preg_replace("/\r/",'',$lines));
    foreach ($lines as $l) {
        $dla[] = new DiffLine($l, $who);
    }
    return $dla;
}

function showComboDiff($keyword, $db=NULL)
{
    $version2=getLatestVersion($keyword, Context::getId());
    $version1=getFirstVersion($keyword, Context::getId());
    $version2=$version2["version"];
    $version1=$version1["version"];

    $content = "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";

    // create combodiff

    $wd1 = getWikiPage($keyword, $version1);
    $diffarray1 = toDiffLineArray($wd1['body'], $wd1['user_id']);
    $current_version = $version1 + 1;
    $differ = new line_diff();
    while ($current_version <= $version2) {
        $wd2 = getWikiPage($keyword, $current_version);
        if ($wd2) {
            $diffarray2 = toDiffLineArray($wd2['body'], $wd2['user_id']);
            $newarray = $differ->arr_compare("diff", $diffarray1, $diffarray2);
            $diffarray1=[];
            foreach ($newarray as $i) {
                if ($i->status["diff"] != "-") {
                    $diffarray1[]=$i;
                }
            }
        }
        $current_version++;
    }
    $legend="<table>";
    $count=0;
    $authors=[];
    foreach ($diffarray1 as $i) {
        if ($i && !in_array($i->who, $authors)) {
            $authors[]=$i->who;
            if ($count % 4 == 0) {
                $legend.= "<tr width=\"100%\">";
            }
            $legend.= "<td bgcolor=".create_color($count)." width=14>&nbsp;</td><td><font size=-1>".get_fullname($i->who,'full',1)."</font></td><td>&nbsp;</td>";
            if ($count % 4 == 3) {
                $legend .= "</tr>";
            }
            $count++;
        }
    }
    $content .= "<tr><td colspan=2>";
    $content .= "<p><font size=-1>&nbsp;<br>";
    $content .= _("Legende der Autor/-innenfarben:");
    $content .= "<table cellpadding=6 cellspacing=6>$legend</table>\n";
    $content .= "</p>";
    $content .= "<table cellpadding=0 cellspacing=0 width=\"100%\">";
    $last_author=None;
    $collect="";
    $diffarray1[]=NULL;
    foreach ($diffarray1 as $i) {
        if (!$i || $last_author != $i->who) {
            if (trim($collect)!="") {
                $idx=array_search($last_author, $authors);
                $col=create_color($idx);
                $content .= "<tr bgcolor=$col>";
                $content .= "<td width=30 align=center valign=top>";
                $content .= Icon::create('info-circle', 'inactive', ['title' => _("Änderung von").' ' . get_fullname($last_author)])->asImg();
                $content .= "</td>";
                $content .= "<td><font size=-1>";
                $content .= wikiReady($collect);
                $content .= "</font></td>";
                $content .= "</tr>";
            }
            $collect="";
        }
        if ($i) {
            $last_author = $i->who;
            $collect .= $i->text;
        }
    }
    $content .= "</table></td></tr>";
    $content .= "</table>     ";

    $wikiData = getWikiPage($keyword, null);

    $template = $GLOBALS['template_factory']->open('wiki/show.php');
    $template->wikipage = $wikiData;
    $template->content  = $content;
    echo $template->render();

    getDiffPageInfobox($keyword);

    // help texts
    $help = [
        _('Die Ansicht zeigt den Verlauf der Textänderungen einer Wiki-Seite '.
          'mit einer Übersicht, welche Autor/-innen welche Textänderungen ' .
          'vorgenommen haben.')];
    Helpbar::get()->ignoreDatabaseContents();
    Helpbar::get()->addPlainText('', $help);
}

function create_color($index) {
    $shades=["e","b","d","a","c","9","8","7","6","5"];
    if ($index>70) {
        $index=$index%70;
    }
    $shade=$shades[$index/7]."0";
    switch ($index % 7) {
        case 0: return "#".$shade.$shade.$shade;
        case 1: return "#ff".$shade.$shade;
        case 2: return "#".$shade."ff".$shade;
        case 3: return "#".$shade.$shade."ff";
        case 4: return "#ffff".$shade;
        case 5: return "#ff".$shade."ff";
        case 6: return "#".$shade."ffff";
    }
}

/*
line_diff

S. Wu, <a href="http://www.cs.arizona.edu/people/gene/vita.html">
E. Myers,</a> U. Manber, and W. Miller,
<a href="http://www.cs.arizona.edu/people/gene/PAPERS/np_diff.ps">
"An O(NP) Sequence Comparison Algorithm,"</a>
Information Processing Letters 35, 6 (1990), 317-323.
*/

class line_diff
{
    var $arr1,$arr2,$m,$n,$pos,$key,$plus,$minus,$equal,$reverse;

    function __construct($plus='+',$minus='-',$equal='=')
    {
        $this->plus = $plus;
        $this->minus = $minus;
        $this->equal = $equal;
    }
    function arr_compare($key,$arr1,$arr2)
    {
        $this->key = $key;
        $this->arr1 = $arr1;
        $this->arr2 = $arr2;
        $this->compare();
        $arr = $this->toArray();
        return $arr;
    }
    function set_str($key,$str1,$str2)
    {
        $this->key = $key;
        $this->arr1 = [];
        $this->arr2 = [];
        $str1 = preg_replace("/\r/",'',$str1);
        $str2 = preg_replace("/\r/",'',$str2);
        foreach (explode("\n",$str1) as $line)
        {
            $this->arr1[] = new DiffLine($line, 'nobody');
        }
        foreach (explode("\n",$str2) as $line)
        {
            $this->arr2[] = new DiffLine($line, 'nobody');
        }
    }
    function str_compare($str1, $str2, $show_equal=FALSE)
    {
        $this->set_str('diff',$str1,$str2);
        $this->compare();

        $str = '';
        $lastdiff = "";
        $textaccu = "";
        $template = "<tr>%s<td width=\"10\">&nbsp;</td><td><font size=-1>%s</font>&nbsp;</td></tr>";
        foreach ($this->toArray() as $obj)
        {
            if ($show_equal || $obj->get('diff') != $this->equal) {
                if ($lastdiff && $obj->get("diff") != $lastdiff) {
                    $str .= sprintf($template, $lastdiff, wikiReady($textaccu));
                    $textaccu="";
                }
                $textaccu .= $obj->text();
                $lastdiff = $obj->get("diff");
            }
        }
        if ($textaccu) {
            $str .= sprintf($template, $lastdiff, wikiReady($textaccu));
        }
        return $str;
    }
    function compare()
    {
        $this->m = count($this->arr1);
        $this->n = count($this->arr2);

        if ($this->m == 0 or $this->n == 0) // no need compare.
        {
            $this->result = [['x'=>0,'y'=>0]];
            return;
        }

        // sentinel
        array_unshift($this->arr1,new DiffLine(''));
        $this->m++;
        array_unshift($this->arr2,new DiffLine(''));
        $this->n++;

        $this->reverse = ($this->n < $this->m);
        if ($this->reverse) // swap
        {
            $tmp = $this->m; $this->m = $this->n; $this->n = $tmp;
            $tmp = $this->arr1; $this->arr1 = $this->arr2; $this->arr2 = $tmp;
            unset($tmp);
        }

        $delta = $this->n - $this->m; // must be >=0;

        $fp = [];
        $this->path = [];

        for ($p = -($this->m + 1); $p <= ($this->n + 1); $p++)
        {
            $fp[$p] = -1;
            $this->path[$p] = [];
        }

        for ($p = 0;; $p++)
        {
            for ($k = -$p; $k <= $delta - 1; $k++)
            {
                $fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
            }
            for ($k = $delta + $p; $k >= $delta + 1; $k--)
            {
                $fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
            }
            $fp[$delta] = $this->snake($delta, $fp[$delta - 1], $fp[$delta + 1]);
            if ($fp[$delta] >= $this->n)
            {
                $this->pos = $this->path[$delta]; //
                return;
            }
        }
    }
    function snake($k, $y1, $y2)
    {
        if ($y1 >= $y2)
        {
            $_k = $k - 1;
            $y = $y1 + 1;
        }
        else
        {
            $_k = $k + 1;
            $y = $y2;
        }
        $this->path[$k] = $this->path[$_k];//
        $x = $y - $k;
        while ((($x + 1) < $this->m) and (($y + 1) < $this->n)
            and $this->arr1[$x + 1]->compare($this->arr2[$y + 1]))
        {
            $x++; $y++;
            $this->path[$k][] = ['x'=>$x,'y'=>$y]; //
        }
        return $y;
    }
    function toArray()
    {
        $arr = [];
        if ($this->reverse) //
        {
            $_x = 'y'; $_y = 'x'; $_m = $this->n; $arr1 =& $this->arr2; $arr2 =& $this->arr1;
        }
        else
        {
            $_x = 'x'; $_y = 'y'; $_m = $this->m; $arr1 =& $this->arr1; $arr2 =& $this->arr2;
        }

        $x = $y = 1;
        $this->add_count = $this->delete_count = 0;
        $this->pos[] = ['x'=>$this->m,'y'=>$this->n]; // sentinel
        foreach ($this->pos as $pos)
        {
            $this->delete_count += ($pos[$_x] - $x);
            $this->add_count += ($pos[$_y] - $y);

            while ($pos[$_x] > $x)
            {
                $arr1[$x]->set($this->key,$this->minus);
                $arr[] = $arr1[$x++];
            }

            while ($pos[$_y] > $y)
            {
                $arr2[$y]->set($this->key,$this->plus);
                $arr[] =  $arr2[$y++];
            }

            if ($x < $_m)
            {
                $arr1[$x]->merge($arr2[$y]);
                $arr1[$x]->set($this->key,$this->equal);
                $arr[] = $arr1[$x];
            }
            $x++; $y++;
        }
        return $arr;
    }
}

class DiffLine
{
    var $text;
    var $status;
    var $who; // who originally wrote this line?

    function __construct($text, $who=NULL)
    {
        $this->text = "$text\n";
        $this->status = [];
        $this->who = $who;
    }
    function compare($obj)
    {
        return $this->text == $obj->text;
    }
    function set($key,$status)
    {
        $this->status[$key] = $status;
    }
    function get($key)
    {
        return array_key_exists($key,$this->status) ? $this->status[$key] : '';
    }
    function merge($obj)
    {
        $this->status += $obj->status;
    }
    function text()
    {
        return $this->text;
    }
}
