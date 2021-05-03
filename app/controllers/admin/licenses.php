<?php

class Admin_LicensesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $GLOBALS['perm']->check('root');
        PageLayout::setTitle(_('Lizenzverwaltung'));
        Navigation::activateItem('/admin/locations/licenses');
    }

    public function index_action()
    {
        $this->licenses = License::findBySQL("1 ORDER BY `default` DESC, `identifier` ASC");
    }

    public function edit_action()
    {
        $this->license = new License(Request::get("identifier"));
        PageLayout::setTitle(sprintf(_("Lizenz %s bearbeiten"), $this->license->getId()));
    }

    public function store_action()
    {
        $this->license = new License(Request::get("identifier"));
        if (Request::isPost()) {
            $this->license->setData(Request::getArray("data"));
            $this->license->store();
            if (Request::submitted("delete_avatar")) {
                $avatar = LicenseAvatar::getAvatar($this->license->getId());
                $avatar->reset();
            }
            if ($_FILES['avatar']['size'] > 0) {
                $avatar = LicenseAvatar::getAvatar($this->license->getId());
                $avatar->createFromUpload("avatar");
            }
            PageLayout::postSuccess(_("Lizenz wurde gespeichert."));
            if ($this->license['default']) {
                $other_licenses = License::findBySQL("`identifier` != ? AND `default` = '1'", [
                    $this->license->getId()
                ]);
                foreach ($other_licenses as $other_license) {
                    $other_license['default'] = 0;
                    $other_license->store();
                }
                if (count($other_licenses)) {
                    PageLayout::postInfo(_("Neue Standardlizenz wurde gesetzt."));
                }
            }
        }
        $this->redirect("admin/licenses/index");
    }

    public function delete_action()
    {
        $this->license = new License(Request::get("identifier"));
        if (Request::isPost()) {
            $this->license->delete();
            PageLayout::postSuccess(sprintf(
                _("Die Lizenz %s wurde gelöscht."), htmlReady(Request::get("identifier"))
            ));
        } else {
            throw new AccessDeniedException();
        }
        $this->redirect("admin/licenses/index");
    }
}
