<?php
/**
 * Pagination abstraction
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 */
class Pagination
{
    /**
     * Creates a pagination object.
     *
     * @param  int $total        Total number of entries
     * @param  int $current_page Current page
     * @param  int $per_page     Displayed entries per page
     * @return Pagination object
     */
    public static function create($total, $current_page = 0, $per_page = null)
    {
        if ($per_page === null) {
            $per_page = Config::get()->ENTRIES_PER_PAGE;
        }
        return new self($total, $current_page, $per_page);
    }

    private $total;
    private $current_page;
    private $per_page;
    private $dialog = null;
    private $show_page = false;

    /**
     * Checks current page ranges for validity.
     *
     * @param  int $total        Total number of entries
     * @param  int $current_page Current page
     * @param  int $per_page     Displayed entries per page
     */
    private function __construct($total, $current_page, $per_page)
    {
        $this->total        = $total;
        $this->current_page = $current_page;
        $this->per_page     = $per_page;

        if ($this->current_page < 0) {
            $this->current_page = 0;
        } else {
            $this->current_page = min(
                $this->current_page,
                $this->getPageCount() - 1
            );
        }
    }

    /**
     * Add dialog options
     * @param  string $content Parameters for data-dialog attribute
     * @return Pagination instance to allow chaining
     */
    public function asDialog($content = '')
    {
        $this->dialog = $content;
        return $this;
    }

    /**
     * Returns a list of all pages to display.
     * @return array of pages
     */
    private function getPages()
    {
        $page_count = $this->getPageCount();

        $pages = array_unique([
            0,
            $this->current_page - 2,
            $this->current_page - 1,
            $this->current_page,
            $this->current_page + 1,
            $this->current_page + 2,
            $page_count - 1,
        ]);
        $pages = array_filter($pages, function ($page) use ($page_count) {
            return $page >= 0 && $page < $page_count;
        });
        sort($pages);

        return $pages;
    }

    /**
     * Returns the total number of entries.
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Returns the current page
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * Returns the current offset or skipped entries before the current page.
     * @return int
     */
    public function getOffset()
    {
        return $this->current_page * $this->per_page;
    }

    /**
     * Returns the maximum number of entries per page.
     * @return int
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * Get total number of pages.
     * @return int
     */
    public function getPageCount()
    {
        return ceil($this->total / $this->per_page);
    }

    /**
     * Renders the paginations with <button> elements.
     *
     * @param  string $name Value of the button's name attribute
     * @return string html
     */
    public function asButtons($name = 'page')
    {
        if ($this->getPageCount() <= 1) {
            return '&nbsp;';
        }

        return $this->render('pagination/buttons.php', compact('name'));
    }

    /**
     * Renders the paginations with <a> link elements.
     *
     * @param  Closure $link_for Optional generator for page links (defaults to
     *                           links to the current page with ?page=
     *                           parameters)
     * @return string html
     */
    public function asLinks(Closure $link_for = null)
    {
        if ($this->getPageCount() <= 1) {
            return '&nbsp;';
        }

        if ($link_for === null) {
            $link_for = function ($page) {
                return URLHelper::getLink('', compact('page'));
            };
        }

        return $this->render('pagination/links.php', compact('link_for'));
    }

    /**
     * Renders a template for the pagination with the provided variables.
     *
     * @param  string $template  Name of the template file
     * @param  array  $variables Additional variables
     * @return string html
     */
    private function render($template, array $variables = [])
    {
        return $GLOBALS['template_factory']->render($template, $variables + [
            'random_id' => md5(uniqid('pagination', true)),
            'pages'     => $this->getPages(),
            'count'     => $this->getPageCount(),
            'current'   => $this->current_page,
            'dialog'    => $this->dialog,
            'show_page' => $this->show_page,
        ]);
    }

    /**
     * Loads the slice of a sorm collection defined by this object.
     *
     * @param string $sorm_class Name of the SORM class
     * @param string $condition  Condition to load objects by
     * @param array  $parameters Additional parameters for the condition
     * @return SimpleORMapCollection
     * @throws
     */
    public function loadSORMCollection($sorm_class, $condition = '1', array $parameters = [])
    {
        if (!class_exists($sorm_class) || !is_a($sorm_class, 'SimpleORMap', true)) {
            throw new RuntimeException('No valid SORM class given');
        }

        $sql = sprintf(
            "{$condition} LIMIT %u, %u",
            $this->getOffset(),
            $this->getPerPage()
        );

        return SimpleORMapCollection::createFromArray(
            $sorm_class::findBySQL($sql, $parameters)
        );
    }
}
