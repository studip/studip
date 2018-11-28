<?php
/**
 * loginstyle.php - controller class for administration of login background pics
 *
 * @author    Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @package   admin
 * @since     4.0
 */

class Admin_LoginStyleController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     *
     * @param String $action Action that has been called
     * @param Array  $args   List of arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        //setting title and navigation
        PageLayout::setTitle(_('Hintergrundbilder für den Startbildschirm'));
        Navigation::activateItem('/admin/locations/loginstyle');

        // Setup sidebar
        $this->setSidebar();
    }

    /**
     * Display all available background pictures
     */
    public function index_action()
    {
        $this->pictures = LoginBackground::findBySQL("1 ORDER BY `background_id`");
    }

    /**
     * Provides a form for uploading a new picture.
     */
    public function newpic_action()
    {
    }

    /**
     * Adds a new picture ass possible login background.
     */
    public function add_action()
    {
        CSRFProtection::verifyRequest();
        $success = 0;
        $fail = 0;
        foreach ($_FILES['pictures']['name'] as $index => $filename) {
            if ($_FILES['pictures']['error'][$index] === UPLOAD_ERR_OK) {
                $entry = new LoginBackground();
                $entry->filename = $filename;
                $entry->desktop = Request::int('desktop', 0);
                $entry->mobile = Request::int('mobile', 0);
                if ($entry->store()) {
                    $destination = LoginBackground::getPictureDirectory() . DIRECTORY_SEPARATOR
                                 . $entry->id . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                    if (move_uploaded_file($_FILES['pictures']['tmp_name'][$index], $destination)) {
                        $success++;
                    } else {
                        $entry->delete();
                        $fail++;
                    }
                } else {
                    $fail++;
                }
            }
        }
        if ($success > 0) {
            PageLayout::postSuccess(sprintf(ngettext(
                'Ein Bild wurde hochgeladen.',
                '%u Bilder wurden hochgeladen',
                $success
            ), $success));
        }
        if ($fail > 0) {
            PageLayout::postError(sprintf(ngettext(
                'Ein Bild konnte nicht hochgeladen werden.',
                '%u Bilder konnten nicht hochgeladen werden.',
                $fail
            ), $fail));
        }
        $this->relocate('admin/loginstyle');
    }

    /**
     * Deletes the given picture.
     * @param $id the picture to delete
     */
    public function delete_action($id)
    {
        $pic = LoginBackground::find($id);
        if ($pic->in_release) {
            PageLayout::postError(_('Dieses Bild wird vom System mitgeliefert und kann daher nicht gelöscht werden.'));
        } elseif ($pic->delete()) {
            PageLayout::postSuccess(_('Das Bild wurde gelöscht.'));
        } else {
            PageLayout::postError(_('Das Bild konnte nicht gelöscht werden.'));
        }

        $this->relocate('admin/loginstyle');
    }

    /**
     * (De-)activate the given picture for given view.
     * @param $id the picture to change activation for
     * @param $view one of 'desktop', 'mobile', view to (de-) activate picture for
     * @param $newStatus new activation status for given view.
     */
    public function activation_action($id, $view, $newStatus)
    {
        $pic = LoginBackground::find($id);
        $pic->$view = $newStatus;
        if ($pic->store()) {
            PageLayout::postSuccess(_('Der Aktivierungsstatus wurde gespeichert.'));
        } else {
            PageLayout::postSuccess(_('Der Aktivierungsstatus konnte nicht gespeichert werden.'));
        }
        $this->relocate('admin/loginstyle');
    }

    /**
     * Adds the content to sidebar
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setTitle(_('Semester'));
        $sidebar->setImage('sidebar/admin-sidebar.png');

        $links = new ActionsWidget();
        $links->addLink(
            _('Bild hinzufügen'),
            $this->url_for('admin/loginstyle/newpic'),
            Icon::create('add', 'clickable')
        )->asDialog('size=auto');
        $sidebar->addWidget($links);
    }
}
