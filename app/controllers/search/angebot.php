<?php
/**
 * angebot.php - Search_AngebotController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class Search_AngebotController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        parent::before_filter($action, $args);

        // set navigation
        Navigation::activateItem('/search/courses/module');

        PageLayout::setTitle(_('Modulverzeichnis - Studienangebot von A bis Z'));

        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        $views = new ViewsWidget();
        $views->addLink(_('Modulsuche'), $this->url_for('search/module'));
        $views->addLink(_('Studienangebot'), $this->url_for('search/angebot'))
                ->setActive();
        $views->addLink(_('Studiengänge'), $this->url_for('search/studiengaenge'));
        $views->addLink(_('Fach-Abschluss-Kombinationen'), $this->url_for('search/stgtable'));

        $sidebar->addWidget($views);

        $this->breadcrumb = new BreadCrumb();
        $this->action = $action;
    }

    protected static function IsVisible()
    {
        return MVV::isVisibleSearch();
    }

    public function index_action()
    {
        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }
        $filter = ['mvv_studiengang.stat' => $status_filter];
        $faecher = Fach::getAllEnriched('name', 'ASC', null, null, $filter);

        // sort by display name
        $faecher_sort = [];
        foreach ($faecher as $key => $fach) {
            $faecher_sort[$fach->getDisplayName() . $key] = $fach;
        }
        ksort($faecher_sort, SORT_LOCALE_STRING);

        $result = [];
        $chars = [];
        foreach ($faecher_sort as $fach) {
            $char = mb_substr($fach->name, 0, 1);

            foreach ($fach->abschluesse as $abschluss) {
                if (count(Studiengang::findByFachAbschluss($fach->getId(), $abschluss->getId(), $filter))) {
                    $result[$char][] = [
                        'abschluss_id' => $abschluss->getId(),
                        'fach_id' => $fach->getId(),
                        'name' => sprintf('%s - %s', $fach->name,
                                $abschluss->getDisplayName())
                    ];
                }
            }

            $chars[$char] = $char;
        }

        function link_chars(&$char,
                $key,
                $pattern) {
            $char = sprintf($pattern, $key, ucfirst($key));
        }

        array_walk($chars,
                function (&$char) {
                    $char = sprintf('<a href="#%s">%s</a>', $char, ucfirst($char));
                });

        $this->breadcrumb->init();
        $this->breadcrumb->append(_('Studienangebot von A bis Z'), 'index');
        $this->faecher = $result;
        $this->chars = $chars;
        $this->url = 'search/angebot/detail';
    }

    public function matrix_action($kategorie_id)
    {
        $abschluss_kategorie = AbschlussKategorie::find($kategorie_id);
        if (!$abschluss_kategorie) {
            PageLayout::postError(_('Unbekannte Abschluss-Kategorie'));
            $this->redirect('search/angebot/index');
            return null;
        }
        $name = $abschluss_kategorie->name;
        $faecher = [];

        $result = [];
        foreach ($faecher as $fach) {
            foreach ($fach->abschluesse as $abschluss) {
                $kategorie = AbschlussKategorie::findByAbschluss($abschluss->getId());
                $result [$fach->name][$kategorie->getId()] = [
                    'abschluss_id' => $abschluss->getId(),
                    'fach_id' => $fach->getId(),
                    'name' => sprintf('%s - %s (%s)', $fach->name, $abschluss->name, $abschluss->name_kurz)
                ];
            }
        }

        $this->abschluesse = $abschluss_kategorie;
        $this->faecher = $result;
        $this->name = $name;
        $this->url = $this->url_for('fach/detail/');
    }

    public function detail_action($fach_id, $abschluss_id, $studiengang_id = null)
    {
        $status_filter = [];
        foreach ($GLOBALS['MVV_STUDIENGANG']['STATUS']['values'] as $key => $status) {
            if ($status['public']) {
                $status_filter[] = $key;
            }
        }
        $this->studiengaenge = Studiengang::findByFachAbschluss($fach_id, $abschluss_id);
        $this->studiengaenge = $this->studiengaenge->filter(function ($stg) use ($status_filter) {
            return in_array($stg->stat, $status_filter);
        });
        $this->fach = Fach::find($fach_id);
        $this->abschluss = Abschluss::find($abschluss_id);
        $this->breadcrumb->append([$this->fach, $this->abschluss], 'detail');
        $this->url = 'search/angebot/studiengang';

        if ($this->abschluss && $studiengang_id) {
            $studiengang = Studiengang::get($studiengang_id);
            $this->studiengangName = $studiengang->getDisplayName();
            $this->studiengang_id = $studiengang->getId();
            $this->info = $studiengang->beschreibung;
        }
        if (count($this->studiengaenge) == 1) {
            if ($this->studiengaenge->first()->typ == 'einfach') {
                // dismiss Fach in breadcrumb navigation
                $this->breadcrumb->pop();
            }
            $this->redirect($this->url . '/' . $this->studiengaenge->first()->id);
            return;
        }
    }

    public function studiengang_action($studiengang_id)
    {
        $this->verlauf_url = 'search/angebot/verlauf';
        $response = $this->relay('search/studiengaenge/studiengang', $studiengang_id);
        $this->content = $response->body;
        $this->render_template('shared/content', $this->layout);
    }

    public function info_action($studiengang_id)
    {
        $this->studiengang = Studiengang::find($studiengang_id);
        if (!$this->studiengang || !$this->studiengang->getPublicStatus()) {
            throw new Exception('Unbekannter Studiengang');
        }
    }

    public function verlauf_action($stgteil_id, $stgteil_bez_id = null,
            $studiengang_id = null)
    {
        ModuleManagementModel::setLanguage($_SESSION['_language']);

        $response = $this->relay('search/studiengaenge/verlauf', $stgteil_id,
                $stgteil_bez_id, $studiengang_id);
        $this->content = $response->body;
        $this->render_template('shared/content', $this->layout);
    }
}
