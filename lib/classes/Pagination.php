<?php
class Pagination
{
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

    private function __construct($total, $current_page, $per_page)
    {
        $this->total        = $total;
        $this->current_page = $current_page;
        $this->per_page     = $per_page;
    }

    public function asDialog($content = '')
    {
        $this->dialog = $content;
        return $this;
    }

    public function getPages()
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

    public function getPageCount()
    {
        return ceil($this->total / $this->per_page);
    }

    public function asButtons($name = 'page')
    {
        if ($this->getPageCount() <= 1) {
            return 'empty-button';
        }

        return $this->render('pagination/buttons.php', compact('name'));
    }

    public function asLinks(Closure $link_for)
    {
        if ($this->getPageCount() <= 1) {
            return '';
        }

        return $this->render('pagination/links.php', compact('link_for'));
    }

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
}
