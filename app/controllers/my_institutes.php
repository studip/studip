<?php
require_once 'lib/meine_seminare_func.inc.php';

class MyInstitutesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!$GLOBALS['perm']->have_perm("root")) {
            Navigation::activateItem('/browse/my_institutes');
        }
        $this->user_id = $GLOBALS['auth']->auth['uid'];
        PageLayout::setHelpKeyword("Basis.MeineEinrichtungen");
        PageLayout::setTitle(_("Meine Einrichtungen"));
    }

    public function index_action()
    {

        $this->institutes = MyRealmModel::getMyInstitutes();

        if ($this->check_for_new($this->institutes)) {
            $this->reset = true;
        }

        $this->nav_elements = MyRealmModel::calc_single_navigation($this->institutes);
    }

    public function decline_inst_action($inst_id)
    {
        $institut     = Institute::find($inst_id);
        $ticket_check = Seminar_Session::check_ticket(Request::option('studipticket'));

        if (Request::option('cmd') != 'kill' && Request::get('cmd') != 'back') {
            $this->flash['decline_inst'] = true;
            $this->flash['inst_id']      = $inst_id;
            $this->flash['name']         = $institut->name;
            $this->flash['studipticket'] = Seminar_Session::get_ticket();
        } else {
            if (Request::get('cmd') == 'kill' && $ticket_check && Request::get('cmd') != 'back') {
                $query     = "DELETE FROM user_inst WHERE user_id = ? AND Institut_id = ? AND inst_perms = 'user'";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$GLOBALS['user']->id, $inst_id]);

                if ($statement->rowCount() > 0) {
                    PageLayout::postMessage(MessageBox::success(sprintf(_("Die Zuordnung zur Einrichtung %s wurde aufgehoben."), "<b>" . htmlReady($institut->name) . "</b>")));
                } else {
                    PageLayout::postMessage(MessageBox::error(_('Datenbankfehler')));
                }
            }
        }
        $this->redirect('my_institutes/index');
    }

    public function tabularasa_action($timestamp = null)
    {
        $institutes = MyRealmModel::getMyInstitutes();
        foreach ($institutes as $index => $institut) {
            MyRealmModel::setObjectVisits($institutes[$index], $institut['institut_id'], $GLOBALS['user']->id, $timestamp);
        }

        PageLayout::postMessage(MessageBox::success(_('Alles als gelesen markiert!')));
        $this->redirect('my_institutes/index');
    }

    function check_for_new($my_obj)
    {
        if(!empty($my_obj)) {
            foreach ($my_obj as $inst) {
                if ($this->check_institute($inst)) {
                    return true;
                }
            }
        }
        return false;
    }


    function check_institute($institute)
    {
        if ($institute['visitdate'] || $institute['last_modified']) {
            if ($institute['visitdate'] <= $institute["chdate"] || $institute['last_modified'] > 0) {
                $last_modified = ($institute['visitdate'] <= $institute["chdate"]
                && $institute["chdate"] > $institute['last_modified'] ? $institute["chdate"] : $institute['last_modified']);
                if ($last_modified) {
                    return true;
                }
            }
        }

        $plugins = $institute['navigation'];

        foreach ($plugins as $navigation) {
            if ($navigation && $navigation->isVisible(true) && $navigation->hasBadgeNumber()) {
                return true;
            }
        }

        return false;
    }
}
