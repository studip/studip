<?php

/**
 * Class TableOfContents
 * Creates a Table of Contents (TOC) and / or a breadcrumb on any page
 *
 * @author  Michaela Brückner (brueckner@data-quest.de)
 *
 * How to use:
 *
 * $tocObject = new TableOfContents();
 * // create TOC
 * $entries = $tocObject->createTocElements($toc);
 *
 * // create Breadcrumb
 * $breadcrumb = $tocObject->createBreadcrumb($toc);
 *
 *
 */
class TableOfContents
{

    public function __construct()
    {
        $this->page_counter = 0;
        $this->numbering    = "";
        $this->entry_page   = "WikiWikiWeb";
    }

    /**
     * @param $title
     */
    public function setEntryPage($title)
    {
        $this->entry_page = $title;
    }

    /**
     * @return string
     * @throws Flexi_TemplateNotFoundException
     */
    public function getWikiPages()
    {
        $wikistartpage = WikiPage::getStartPage(Context::getId());
        $template      = $GLOBALS['template_factory']->open('toc/toc_start');
        $template->set_attribute(
            'active',
            (Request::get('keyword') == $wikistartpage->keyword  || Request::get('keyword') == '') ? 'active' : ''
        );
        $template->set_attribute('wikiwikiweb', $wikistartpage->keyword);
        $template->set_attribute('children', $this->getChildPages($wikistartpage->children));

        $page_elements = $template->render();
        $this->page_counter = $this->countWikiPages(Context::getId());
        return $page_elements;
    }

    /**
     * @param $descendants
     * @param int $i
     * @return string
     * @throws Flexi_TemplateNotFoundException
     */
    public function getChildPages($descendants, $i = 0)
    {
        $template = $GLOBALS['template_factory']->open('toc/toc_chapters');
        $template->set_attribute('descendants', $descendants);
        $template->set_attribute('numbering', $this->numbering);
        $template->set_attribute('i', $i);
        return $template->render();
    }

    /**
     * @param $counter
     * @param $children
     * @return mixed
     * @throws Flexi_TemplateNotFoundException
     */
    public function renderTocElements($counter, $children)
    {
        $template = $GLOBALS['template_factory']->open('toc/toc');
        $template->set_attribute('toc_counter', $counter);
        $template->set_attribute('toc_new', $children);

        return $template->render();
    }

    /**
     * @param bool $numbering
     */
    public function setLeadingNumbers($numbering = true)
    {
        $this->leading_numbers = $numbering;
        if ($this->leading_numbers === true) {
            $this->numbering = " numberedchapters";
        }
    }

    /**
     * @return string
     */
    public function getPagesForBC()
    {
        $pages        = [];
        $current_page = WikiPage::findLatestPage(Context::getId(), Request::get('keyword') ?: $this->entry_page);

        array_push($pages, $current_page->keyword);

        // solange Keyword nicht WikiWikiWeb ist, sind wir nicht am obersten Ende ...
        if ($current_page->keyword != $this->entry_page) {
            array_unshift($pages, $current_page->ancestor);
            $ancestor_page = WikiPage::findLatestPage(Context::getId(), $current_page->ancestor);

            if ($ancestor_page->keyword != $this->entry_page) {
                array_unshift($pages, $ancestor_page->ancestor);
            }
        }

        ($pages[0] == 'WikiWikiWeb')
            ? $this->firstpage = _('Wiki-Startseite')
            : $this->firstpage = $pages[0];

        if ($current_page->keyword == $pages[0]) {
            $bc_links = htmlReady($this->firstpage);
        } else {
            $bc_links = '<div><a class="navigate" href="' . URLHelper::getLink('wiki.php',
                    ['keyword' => $pages[0]]) . '">' . htmlReady($this->firstpage) . '</a></div>';
        }

        if (isset($pages[1])) {
            if ($current_page->keyword == $pages[1]) {
                $bc_links .= '&nbsp;/&nbsp;' . htmlReady($pages[1]);
            } else {
                $bc_links .= '<div>&nbsp;/&nbsp;<a class="navigate" href="' . URLHelper::getLink('wiki.php',
                        ['keyword' => $pages[1]]) . '">' . htmlReady($pages[1]) . '</a></div>';
            }
        }

        if (isset($pages[2])) {
            if (isset($pages[1])) {
                $bc_links .= '<div>&nbsp;/&nbsp;' . htmlReady($pages[2]);
            } else {
                $bc_links .= '<div>' . htmlReady($pages[2]);
            }
        }

        return $bc_links;
    }

    /**
     * @return string
     */
    public function getInfosForBC()
    {
        $this->toc  = WikiPage::findLatestPage(Context::getId(), Request::get('keyword') ?: $this->entry_page);
        if ($this->toc) {
            $this->user = User::find($this->toc->user_id);
            if ($this->user) {
                $editor = sprintf('<a href="%s" id="bc_username">%s</a>',
                    URLHelper::getLink('dispatch.php/profile?username=' . $this->user->username),
                    htmlReady($this->user->getFullName()));
            } else {
                $editor = _('unbekannt');
            }
            $page_string = sprintf(_('<a %s id="bc_version"> Version %s</a>, geändert von %s'),
                ' href="' . URLHelper::getLink('', ['keyword' => $this->toc->keyword, 'version' => $this->toc->version]) . '"',
                $this->toc->version, $editor);
            $page_string .= '<br />';
            $page_string .= strftime(_('am %x, %X'), $this->toc->chdate);


            return $page_string;
        } else {
            return "";
        }
    }

    /**
     * @param $bcLinks
     * @param $bcdetails
     * @param $entries
     * @param $wiki_data
     * @return mixed
     * @throws Flexi_TemplateNotFoundException
     */
    public function createBreadcrumb($bcLinks, $bcdetails, $entries, $wiki_data)
    {
        $template = $GLOBALS['template_factory']->open('toc/toc_breadcrumb');
        $template->set_attribute('toc_breadcrumb_pages', $bcLinks);
        $template->set_attribute('toc_breadcrumb_info', $bcdetails);
        $template->set_attribute('count', $this->page_counter);
        $template->set_attribute('toc_entries', $entries);
        $template->set_attribute('wikipage', $wiki_data);
        return $template->render();
    }

    /**
     * @param string $range_id
     * @return int
     */
    protected function countWikiPages($range_id)
    {
        $query = "SELECT COUNT(DISTINCT `keyword`)
                  FROM `wiki`
                  WHERE `range_id` = ?
                    AND (
                        `ancestor` IS NOT NULL
                        OR `keyword` = ?
                    )";
        return (int) DBManager::get()->fetchColumn(
            $query,
            [Context::getId(), $this->entry_page]
        );
    }
}
