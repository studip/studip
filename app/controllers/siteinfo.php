<?php
# Lifter007: TEST

/**
 * siteinfo - display information about Stud.IP
 *
 * @author    Ansgar Bockstiegel
 * @copyright 2008 Ansgar Bockstiegel
 * @license   GPL2 or any later version
 */

require_once 'app/models/siteinfo.php';

class SiteinfoController extends StudipController
{
    protected $with_session = true;

    private $si;

    /**
     * common tasks for all actions
     */
    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Siteinfo-Class is defined in models/siteinfo.php
        $this->si = new Siteinfo();

        $this->populate_ids($args);
        $this->add_navigation($action);

        if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')) {
            $this->setupSidebar();
        } else {
            $action = 'show';
        }

        PageLayout::setTitle(_('Impressum'));
        PageLayout::setTabNavigation('/footer/siteinfo');
    }

    //the first element of the unconsumed trails-path determines the rubric
    //the second element defines the page(detail)
    //if they are missing the first detail/rubric is the fallback
    protected function populate_ids($args)
    {
        if (isset($args[0]) && is_numeric($args[0])) {
            $this->currentrubric = $args[0];
            if (isset($args[1]) && is_numeric($args[1])) {
                $this->currentdetail = $args[1];
            } else {
                $this->currentdetail = $this->si->first_detail_id($args[0]);
            }
        } else {
            $this->currentrubric = $this->si->first_rubric_id();
            $this->currentdetail = $this->si->first_detail_id();
        }
    }

    protected function add_navigation($action)
    {
        foreach ($this->si->get_all_rubrics() as $rubric) {
            $rubric[1] = language_filter($rubric[1]);
            if ($rubric[1] == '') {
                $rubric[1] = _('unbenannt');
            }
            Navigation::addItem('/footer/siteinfo/'.$rubric[0],
                new Navigation($rubric[1], $this->url_for('siteinfo/show/'.$rubric[0])));
        }

        foreach ($this->si->get_all_details() as $detail) {
            $detail[2] = language_filter($detail[2]);
            if ($detail[2] == '') {
                $detail[2] = _('unbenannt');
            }
            Navigation::addItem('/footer/siteinfo/'.$detail[1].'/'.$detail[0],
                new Navigation($detail[2], $this->url_for('siteinfo/show/'.$detail[1].'/'.$detail[0])));
        }

        if ($action != 'new') {
            if ($this->currentdetail > 0) {
                Navigation::activateItem('/footer/siteinfo/'.$this->currentrubric.'/'.$this->currentdetail);
            } else {
                Navigation::activateItem('/footer/siteinfo/'.$this->currentrubric);
            }
        }
    }

    protected function setupSidebar()
    {
        $sidebar = Sidebar::get();

        if (!$GLOBALS['rubrics_empty']) {
            $actions = new ActionsWidget();
            $actions->setTitle(_('Seiten-Aktionen'));

            if ($this->currentrubric) {
                $actions->addLink(_('Neue Seite anlegen'),
                                  $this->url_for('siteinfo/new/' . $this->currentrubric), Icon::create('add', 'clickable'));
            }
            if ($this->currentdetail) {
                $actions->addLink(_('Seite bearbeiten'),
                                  $this->url_for('siteinfo/edit/' . $this->currentrubric . '/' . $this->currentdetail), Icon::create('edit', 'clickable'));
                $actions->addLink(_('Seite löschen'),
                                  $this->url_for('siteinfo/delete/' . $this->currentrubric . '/' . $this->currentdetail), Icon::create('trash', 'clickable'));
            }

            $sidebar->addWidget($actions);
        }


        $actions = new ActionsWidget();
        $actions->setTitle(_('Rubrik-Aktionen'));

        $actions->addLink(_('Neue Rubrik anlegen'),
                          $this->url_for('siteinfo/new'), Icon::create('add', 'clickable'));
        if ($this->currentrubric) {
            $actions->addLink(_('Rubrik bearbeiten'),
                              $this->url_for('siteinfo/edit/' . $this->currentrubric), Icon::create('edit', 'clickable'));
            $actions->addLink(_('Rubrik löschen'),
                              $this->url_for('siteinfo/delete/' . $this->currentrubric), Icon::create('trash', 'clickable'));
        }

        $sidebar->addWidget($actions);
    }

    /**
     * Display the siteinfo
     */
    public function show_action()
    {
        $this->output = $this->si->get_detail_content_processed($this->currentdetail);
    }

    public function new_action($givenrubric = null)
    {
        if ($givenrubric === null) {
            Navigation::addItem('/footer/siteinfo/rubric_new',
                                new AutoNavigation(_('Neue Rubrik'),
                                                   $this->url_for('siteinfo/new')));
            $this->edit_rubric = true;
        } else {
            Navigation::addItem('/footer/siteinfo/' . $this->currentrubric . '/detail_new',
                                new AutoNavigation(_('Neue Seite'),
                                                   $this->url_for('siteinfo/new/' . $this->currentrubric)));
            $this->rubrics = $this->si->get_all_rubrics();
        }
    }

    public function edit_action($givenrubric = null, $givendetail = null)
    {
        if (is_numeric($givendetail)) {
            $this->rubrics     = $this->si->get_all_rubrics();
            $this->rubric_id   = $this->si->rubric_for_detail($this->currentdetail);
            $this->detail_name = $this->si->get_detail_name($this->currentdetail);
            $this->content     = $this->si->get_detail_content($this->currentdetail);
        } else {
            $this->edit_rubric = true;
            $this->rubric_id = $this->currentrubric;
       }
        $this->rubric_name = $this->si->rubric_name($this->currentrubric);
    }

    public function save_action()
    {
        $detail_name = Request::get('detail_name');
        $rubric_name = Request::get('rubric_name');
        $content     = Request::get('content');
        $rubric_id   = Request::int('rubric_id');
        $detail_id   = Request::int('detail_id');
        if ($rubric_id) {
            if ($detail_id) {
                list($rubric, $detail) = $this->si->save('update_detail', compact('rubric_id', 'detail_name', 'content', 'detail_id'));
            } else {
                if ($content) {
                    list($rubric, $detail) = $this->si->save('insert_detail', compact('rubric_id', 'detail_name','content'));
                } else {
                    list($rubric, $detail) = $this->si->save('update_rubric', compact('rubric_id', 'rubric_name'));
                }
            }
        } else {
            list($rubric, $detail) = $this->si->save('insert_rubric', compact('rubric_name'));
        }
        $this->redirect('siteinfo/show/' . $rubric . '/' . $detail);
    }

    public function delete_action($givenrubric = null, $givendetail = null, $execute = false)
    {
        if ($execute) {
            if ($givendetail === 'all') {
                $this->si->delete('rubric', $this->currentrubric);
                $this->redirect('siteinfo/show/');
            } else {
                $this->si->delete('detail', $this->currentdetail);
                $this->redirect('siteinfo/show/' . $this->currentrubric);
            }
        } else {
            if (is_numeric($givendetail)) {
                $this->detail = true;
            }
            $this->output = $this->si->get_detail_content_processed($this->currentdetail);
        }
    }
}
