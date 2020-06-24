<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @author      
 * @license     GPL2 or any later version
 * @since       4.6
 */

class Studiengaenge_InformationenController extends MVVController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        
        if (!($GLOBALS['perm']->have_perm('root') || RolePersistence::isAssignedRole(
                        $GLOBALS['user']->id, 'MVVAdmin'))) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Verwaltung der Studiengänge'));
        if (Navigation::hasItem('mvv/studiengaenge/informationen')) {
            Navigation::activateItem('mvv/studiengaenge/informationen');
        }
    }
    
    public function index_action()
    {
        $this->createSidebar();
        if ($GLOBALS['perm']->have_perm('root', $GLOBALS['user']->id)) {
            $this->studycourses = Fach::findBySQL('fach_id IN (SELECT DISTINCT(fach_id) FROM user_studiengang) ORDER BY name');
        } else {
            $inst_ids = SimpleCollection::createFromArray(Institute::findBySQL('Institut_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)
                OR fakultaets_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)',
                    [':user_id' => $GLOBALS['user']->user_id]))->pluck('institut_id');

            $this->studycourses = Fach::findBySQL('JOIN mvv_fach_inst as fach_inst ON (fach.fach_id = fach_inst.fach_id)
                WHERE fach_inst.institut_id IN (:inst_ids)
                GROUP BY fach.fach_id ORDER BY fach.name',
            [':inst_ids' => $inst_ids]);
        }
    }
    
    public function degree_action ()
    {
        $this->createSidebar('degrees');
        $this->degree = Degree::findBySQL('abschluss_id IN (SELECT DISTINCT(abschluss_id) FROM user_studiengang) ORDER BY name');
    }
    
    public function showdegree_action($studycourse_id, $nr = 0)
    {
        $this->studycourse = Fach::find($studycourse_id);
        $this->nr = $nr;
        $this->degree = Degree::findBySQL('abschluss_id IN (SELECT DISTINCT(abschluss_id) FROM user_studiengang '
                . 'WHERE fach_id = :studycourse_id AND abschluss_id IN (:abschluss_ids)) '
                . 'ORDER BY name',
                ['studycourse_id' => $studycourse_id,
                 'abschluss_ids' => $this->studycourse->degrees->pluck('abschluss_id')]);
    }
    
    public function showstudycourse_action($degree_id, $nr = 0)
    {
        $this->nr = $nr;
        $this->degree = Degree::find($degree_id);
        if ($GLOBALS['perm']->have_perm("root",$GLOBALS['user']->id)) {
            $this->studycourses = StudyCourse::findBySQL('fach_id IN (SELECT DISTINCT(fach_id) FROM user_studiengang '
                    . 'WHERE abschluss_id = :abschluss_id AND fach_id IN (:studycourse_ids)) ORDER BY name',
                    [':abschluss_id' => $degree_id, ':studycourse_ids' => $this->degree->professions->pluck('fach_id')]);
        } else {
            $inst_ids = SimpleCollection::createFromArray(Institute::findBySQL('Institut_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)
                OR fakultaets_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)',
                    [':user_id' => $GLOBALS['user']->user_id]))->pluck('institut_id');

            $this->studycourses = Fach::findBySQL('JOIN mvv_fach_inst as fach_inst ON (fach.fach_id = fach_inst.fach_id)
                WHERE fach_inst.institut_id IN (:inst_ids)
                GROUP BY fach.fach_id ORDER BY fach.name',
            [':inst_ids' => $inst_ids]);
        }
    }
    
    public function messagehelper_action()
    {
        $fach_id = Request::get('fach_id');
        $degree_id = Request::get('abschluss_id');

        $fach = new Fach($fach_id);
        $degree = new Degree($degree_id);

        $user = array();
        if (is_null($degree_id) && !is_null($fach_id)) {
            $user = UserStudyCourse::findBySql('fach_id = :fach_id',
                [':fach_id' => $fach->fach_id]);
        } else if (!is_null($degree_id) && !is_null($fach_id)) {
            $user = UserStudyCourse::findBySql('fach_id = :fach_id AND abschluss_id = :abschluss_id',
                [':fach_id' => $fach_id, ':abschluss_id' => $degree_id]);
        } else if (!is_null($degree_id) && is_null($fach_id)) {
            $user = UserStudyCourse::findBySql('abschluss_id = :abschluss_id',
                [':abschluss_id' => $degree_id]);
        }
        if (empty($user)) {
            Pagelayout::postError(_('Keine Studierenden zu den gewählten Angaben gefunden'));
            $this->redirect('index');
            return;
        }

        foreach ($user as $u) {
            $send_to[] = $u->user->username;
        }

        $_SESSION['sms_data']['p_rec'] = $send_to;

        $subject = sprintf(_('Information zum Studiengang: %s %s'),
            !$fach->isNew() ? $fach->name: '' , !$degree->isNew() ? $degree->name : '');

        $this->redirect(URLHelper::getURL('dispatch.php/messages/write',
            ['default_subject' => $subject, 'emailrequest' => 1]
        ));
    }
    
    private function createSidebar($view = 'subject' )
    {
        $widget = new ViewsWidget();
        $widget->addLink(_('Gruppieren nach Fächern'), $this->url_for('/index'))
                ->setActive($view == 'subject');
        $widget->addLink(_('Gruppieren nach Abschlüssen'), $this->url_for('/degree'))
                ->setActive($view == 'degrees');
        Sidebar::Get()->addWidget($widget);
    }
    
    public static function getStudyCount($degree_id)
    {
        if ($GLOBALS['perm']->have_perm('root', $GLOBALS['user']->id)) {
            return UserStudyCourse::countBySql('abschluss_id = :abschluss_id',
            [':abschluss_id' => $degree_id]);
        } else {
            $inst_ids = SimpleCollection::createFromArray(Institute::findBySQL('Institut_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)
                OR fakultaets_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)',
                    [':user_id' => $GLOBALS['user']->user_id]))->pluck('institut_id');

            return UserStudyCourse::countBySql('JOIN mvv_fach_inst as fach_inst ON (user_studiengang.fach_id = fach_inst.fach_id)
                WHERE user_studiengang.abschluss_id = :abschluss_id AND fach_inst.institut_id IN (:inst_ids)',
            [':abschluss_id' => $degree_id, ':inst_ids' => $inst_ids]);


        }
    }
    
}