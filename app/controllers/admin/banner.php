<?php
/**
 * banner.php - controller class for the banner administration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Nico Müller <nico.mueller@uni-oldenburg.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @package  admin
 * @since    2.4
 */
class Admin_BannerController extends AuthenticatedController
{
    protected $_autobind = true;

    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        URLHelper::removeLinkParam('cid');

        // user must have root permission
        $GLOBALS['perm']->check('root');

        // set navigation
        Navigation::activateItem('/admin/locations/banner');

        //pagelayout
        PageLayout::setTitle(_('Verwaltung der Banner'));

        // Define banner target types
        $this->target_types = [
            'url'     => _('URL'),
            'seminar' => _('Veranstaltung'),
            'inst'    => _('Einrichtung'),
            'user'    => _('Person'),
            'none'    => _('Kein Verweis'),
        ];

        // Define banner priorities
        $this->priorities = [
             0 => '0 (' . _('nicht anzeigen') . ')',
             1 => '1 (' . _('sehr niedrig') . ')',
             2 => '2',
             3 => '3',
             4 => '4',
             5 => '5',
             6 => '6',
             7 => '7',
             8 => '8',
             9 => '9',
            10 => '10 (' . _('sehr hoch') . ')',
        ];

        //Infobox
        $this->setSidebar();
    }

    /**
     * Administration view for banner
     */
    public function index_action()
    {
        $this->banners = Banner::getAllBanners();
    }

    public function info_action(Banner $banner)
    {
        if ($banner->isNew()) {
            throw new Exception(sprintf(_('Es existiert kein Banner mit der Id "%s"'), $banner->id));
        }
    }

    /**
     * Displays edit form and performs according actions upon submit
     *
     * @param Banner $banner Banner object
     */
    public function edit_action(Banner $banner)
    {
        if ($banner->isNew()) {
            throw new Exception(sprintf(_('Es existiert kein Banner mit der Id "%s"'), $banner->id));
        }

        // edit banner input
        if (Request::submitted('speichern')) {
            $banner_path = Request::get('banner_path');
            $description = Request::get('description');
            $alttext     = Request::get('alttext');
            $target_type = Request::get('target_type');
            $priority = Request::int('priority');

            //add the right target
            if ($target_type == 'url') {
                $target = Request::get('target');
            } else if ($target_type == 'inst') {
                $target = Request::option('institut');
            } else if ($target_type == 'user') {
                $target = Request::username('user');
            } else if ($target_type == 'seminar') {
                $target = Request::option('seminar');
            } else {
                $target = Request::get('target');
            }

            $errors = [];

            //upload file
            $upload = $_FILES['imgfile'];
            if (!empty($upload['name'])) {
                $banner_path = $this->bannerupload($upload['tmp_name'], $upload['size'], $upload['name'], $errors);
            }

            if (!$target && $target_type != 'none') {
                $errors[] = _('Es wurde kein Verweisziel angegeben.');
            }

            $startDate = explode('.',Request::get('start_date'));
            if (($x = $this->valid_date(Request::int('start_hour'), Request::int('start_minute'), $startDate[0],$startDate[1], $startDate[2])) == -1) {
                $errors[] = _('Bitte geben Sie einen gültiges Startdatum ein.');
            } else {
                $startdate = $x;
            }

            $endDate = explode('.',Request::get('end_date'));
            if (($x = $this->valid_date(Request::int('end_hour'), Request::int('end_minute'), $endDate[0], $endDate[1], $endDate[2])) == -1) {
                           $errors[] = _('Bitte geben Sie einen gültiges Enddatum ein.');
            } else {
                $enddate = $x;
            }

            switch ($target_type) {
                case 'url':
                    if (!preg_match('~^(https?|ftp)://~i', $target)) {
                        $errors[] = _('Das Verweisziel muss eine gültige URL sein (incl. http://).');
                    }
                break;
                case 'inst':
                    if (Institute::find($target) === null) {
                        $errors[] =  _('Die angegebene Einrichtung existiert nicht. '
                                      .'Bitte geben Sie eine gültige Einrichtungs-ID ein.');
                    }
                break;
                case 'user':
                    if (User::findByUsername($target) === null) {
                        $errors[] = _('Der angegebene Username existiert nicht.');
                    }
                break;
                case 'seminar':
                    try {
                        Seminar::getInstance($target);
                    } catch (Exception $e) {
                        $errors[] =  _('Die angegebene Veranstaltung existiert nicht. '
                                      .'Bitte geben Sie eine gültige Veranstaltungs-ID ein.');
                    }
                break;
                case 'none':
                    $target = '';
                break;
            }

            if (count($errors) > 0) {
                PageLayout::postError(_('Es sind folgende Fehler aufgetreten:'), $errors);
            } else {
                $banner->banner_path = $banner_path;
                $banner->description = $description;
                $banner->alttext     = $alttext;
                $banner->target_type = $target_type;
                $banner->target      = $target;
                $banner->startdate   = $startdate;
                $banner->enddate     = $enddate;
                $banner->priority    = $priority;
                $banner->store();

                PageLayout::postSuccess(_('Der Banner wurde erfolgreich gespeichert.'));
                $this->redirect('admin/banner');
            }
        }

        if ($banner['target_type'] == 'seminar') {
            $seminar_name = get_object_name($banner['target'], 'sem');
            $this->seminar = QuickSearch::get('seminar', new StandardSearch('Seminar_id'))
                        ->setInputStyle('width: 240px')
                        ->defaultValue($banner['target'],$seminar_name['name'])
                        ->render();
        }

        if ($banner['target_type'] == 'user') {
            $this->user = QuickSearch::get('user', new StandardSearch('username'))
                        ->setInputStyle('width: 240px')
                        ->defaultValue($banner['target'], $banner['target'])
                        ->render();
        }

        if ($banner['target_type'] == 'inst') {
            $institut_name = get_object_name($banner['target'], 'inst');
            $this->institut = QuickSearch::get('institut', new StandardSearch('Institut_id'))
                        ->setInputStyle('width: 240px')
                        ->defaultValue($banner['target'], $institut_name['name'])
                        ->render();
        }
    }

    /**
     * Create a new banner
     */
    public function new_action()
    {
        // add new banner input
        if (Request::submitted('anlegen')) {
            $description = Request::get('description');
            $alttext     = Request::get('alttext');
            $target_type = Request::option('target_type');

            //add the right target
            if ($target_type == 'url') {
                $target = Request::get('target');
            } else if ($target_type == 'inst') {
                $target = Request::option('institut');
            } else if ($target_type == 'user') {
                $target = Request::username('user');
            } else if ($target_type == 'seminar') {
                $target = Request::option('seminar');
            } else {
                $target = Request::get('target');
            }

            $priority = Request::int('priority');

            $errors = [];

            $upload = $_FILES['imgfile'];
            if (!$upload['name']) {
                $errors[] = _('Es wurde kein Bild ausgewählt.');
            } else {
               $banner_path = $this->bannerupload($upload['tmp_name'], $upload['size'], $upload['name'], $errors);
            }
            $startDate = explode('.',Request::get('start_date'));
            if (($x = $this->valid_date(Request::int('start_hour'), Request::int('start_minute'), $startDate[0],$startDate[1], $startDate[2])) == -1) {
                $errors[] = _('Bitte geben Sie einen gültiges Startdatum ein.');
            } else {
                $startdate = $x;
            }
            $endDate = explode('.',Request::get('end_date'));
            if (($x = $this->valid_date(Request::int('end_hour'), Request::int('end_minute'), $endDate[0], $endDate[1], $endDate[2])) == -1) {
                $errors[] = _('Bitte geben Sie einen gültiges Enddatum ein.');
            } else {
                $enddate = $x;
            }

            if (!$target && $target_type != 'none') {
                $errors[] = _('Es wurde kein Verweisziel angegeben.');
            } else {
                switch ($target_type) {
                    case 'url':
                        if (!preg_match('~^(https?|ftp)://~i', $target)) {
                            $errors[] = _('Das Verweisziel muss eine gültige URL sein (incl. http://).');
                        }
                    break;
                    case 'inst':
                        if (Institute::find($target) === null) {
                            $errors[] =  _('Die angegebene Einrichtung existiert nicht. '
                                          .'Bitte geben Sie eine gültige Einrichtungs-ID ein.');
                        }
                    break;
                    case 'user':
                        if (User::findByUsername($target) === null) {
                            $errors[] = _('Der angegebene Benutzername existiert nicht.') ;
                        }
                    break;
                    case 'seminar':
                        try {
                            Seminar::getInstance($target);
                        } catch (Exception $e) {
                            $errors[] =  _('Die angegebene Veranstaltung existiert nicht. '
                                          .'Bitte geben Sie eine gültige Veranstaltungs-ID ein.');
                        }
                    break;
                    case 'none':
                        $target = '';
                    break;
                }
            }

            if (count($errors) > 0) {
                PageLayout::postError(_('Es sind folgende Fehler aufgetreten:'), $errors);
            } else {
                $banner = new Banner();
                $banner->banner_path = $banner_path;
                $banner->description = $description;
                $banner->alttext     = $alttext;
                $banner->target_type = $target_type;
                $banner->target      = $target;
                $banner->startdate   = $startdate;
                $banner->enddate     = $enddate;
                $banner->priority    = $priority;
                $banner->store();

                PageLayout::postSuccess(_('Der Banner wurde erfolgreich gespeichert.'));
                $this->redirect('admin/banner');
            }
        }
    }

    /**
     * Resets the click and view counter for the given banner
     *
     * @param Banner $banner
     */
    public function reset_action(Banner $banner)
    {
        $banner->views  = 0;
        $banner->clicks = 0;
        $banner->store();

        $message = _('Die Klick- und Viewzahlen des Banners wurden zurückgesetzt');
        PageLayout::postSuccess($message);
        $this->redirect('admin/banner');
    }

    /**
     *
     * @param Banner $banner
     */
    public function delete_action(Banner $banner)
    {
        if (Request::int('delete') == 1) {
            $banner->delete();
            PageLayout::postSuccess(_('Das Banner wurde erfolgreich gelöscht!'));
        } elseif (!Request::get('back')) {
            $this->flash['delete'] = ['banner_id' => $banner->id];
        }

        $this->redirect('admin/banner');
    }

    /**
     * Upload a new picture
     *
     * @param String $img temporary upload path
     * @param Int $img_size size of a image
     * @param String $img_name name of the image
     * @todo Relocate this function into the model?
     */
    private function bannerupload($img, $img_size, $img_name, &$errors = [])
    {
        if (!$img_name) { //keine Datei ausgewählt!
            return false;
        }

        //Dateiendung bestimmen
        $dot = mb_strrpos($img_name, '.');
        if ($dot) {
            $l   = mb_strlen($img_name) - $dot;
            $ext = mb_strtolower(mb_substr($img_name, $dot + 1, $l));
        }

        //passende Endung ?
        if (!in_array($ext, words('gif jpeg jpg png'))) {
            $errors[] = sprintf(_('Der Dateityp der Bilddatei ist falsch (%s).<br>'
                                 .'Es sind nur die Dateiendungen .gif, .png und .jpg erlaubt!')
                                , htmlReady($ext));
            return false;
        }

        //na dann kopieren wir mal...
        $uploaddir   = $GLOBALS['DYNAMIC_CONTENT_PATH'] . '/banner';
        $md5hash     = md5($img_name . time());
        $banner_path = $md5hash . '.' . $ext;
        $newfile     = $uploaddir . '/' . $banner_path;
        if(!@move_uploaded_file($img, $newfile)) {
            $errors[] = _('Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!');
            return true;
        }
        chmod($newfile, 0666 & ~umask()); // set permissions for uploaded file

        return $banner_path;
    }

    /**
     * Validate the date
     *
     * @param Int $h hour
     * @param Int $m minute
     * @param Int $d day
     * @param Int $mo month
     * @param Int $y year
     *
     */
    protected function valid_date($h, $m, $d, $mo, $y)
    {
        if ((int)$h + (int)$m + (int)$d + (int)$mo + (int)$y === 0) {
            return 0; // 0= forever
        }

        // mktime returns -1 if date is invalid (and does some strange
        // conversion which might be considered as a bug...)
        return mktime($h ,$m, 0, $mo, $d, $y);
    }

    /**
     * Extends this controller with neccessary sidebar
     *
     * @param String $view Currently viewed group
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neues Banner anlegen'),
            $this->url_for('admin/banner/new'),
            Icon::create('add')
        )->asDialog('size=auto');

        $sidebar->addWidget($actions);
    }
}
