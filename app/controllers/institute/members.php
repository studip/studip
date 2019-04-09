<?php
/**
 * @author Arne Schröder <schroeder@data-quest.de>
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 *
 * @todo test datafields!
 */

require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionen für den Export
require_once 'lib/export/export_linking_func.inc.php';

class Institute_MembersController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        $this->type = 'function';
        if (in_array($action, ['function', 'status', 'list'])) {
            $this->type = $action;
            $action = 'index';
        }

        PageLayout::setTitle(_('Liste der Mitarbeiter/-innen'));

        if (Request::option('auswahl')) {
            Request::set('cid', Request::option('auswahl'));
        }

        parent::before_filter($action, $args);

        if (Navigation::hasItem('/admin/institute/faculty')) {
            Navigation::activateItem('/admin/institute/faculty');
        } elseif (Navigation::hasItem('/course/faculty/view')) {
            Navigation::activateItem('/course/faculty/view');
        }

        if (!Institute::findCurrent()) {
            require_once 'lib/admin_search.inc.php';

            // TODO: We don't seem to need this since admin_search will stop the script
            PageLayout::postInfo(_('Sie müssen zunächst eine Einrichtung auswählen'));
            $this->redirect('institute/basicdata/index?list=TRUE');
            return;
        }

        $this->institute = Institute::findCurrent();

        // this page is used for administration (if the user has the proper rights)
        // or for just displaying the workers and their roles
        $this->admin_view = $GLOBALS['perm']->have_perm('admin')
                            && $GLOBALS['perm']->have_studip_perm('admin', $this->institute->id)
                            && Request::option('admin_view') !== null;

        if ($this->admin_view) {
            PageLayout::setTitle(_('Verwaltung von Mitarbeiter/-innen'));
            $GLOBALS['perm']->check('admin');
        } else {
            checkObject();
            checkObjectModule('personal');

            $GLOBALS['perm']->check('autor');
        }

        //Change header_line if open object
        if ($header_line = Context::getHeaderLine()) {
            PageLayout::setTitle($header_line." - ".PageLayout::getTitle());
        }

        // Bind parameters
        if ($this->admin_view) {
            $accepted_columns = ['Nachname', 'inst_perms'];
        } else {
            $accepted_columns = ['Nachname'];
        }

        $this->sortby = Request::option('sortby');
        $this->extend = Request::option('extend');
        if (!in_array($this->sortby, $accepted_columns)) {
            $this->sortby = 'Nachname';
            $this->statusgruppe_user_sortby = 'position';
        } else {
            $this->statusgruppe_user_sortby = $this->sortby;
        }

        $this->direction = Request::option('direction');
        if (!in_array($this->direction, ['ASC', 'DESC'])) {
            $this->direction === 'ASC';
        }

        URLHelper::addLinkParam('admin_view', $this->admin_view);
        URLHelper::addLinkParam('sortby', $this->sortby);
        URLHelper::addLinkParam('direction', $this->direction);
        URLHelper::addLinkParam('extend', $this->extend);

        $this->setupSidebar();
    }

    /**
     * show institute members page
     */
    public function index_action()
    {
        // Collect groups
        $this->groups = $this->institute->all_status_groups;

        // Show lock rule information
        $lockrule = LockRules::getObjectRule($this->institute->id);
        if ($this->admin_view && $lockrule->description
                && LockRules::Check($this->institute->id, 'participants')) {
            PageLayout::postInfo(formatLinks($lockrule->description));
        }

        // Create structure chunks from defaults datafields
        $default_fields = [
            'raum'         => _('Raum'),
            'sprechzeiten' => _('Sprechzeiten'),
            'telefon'      => _('Telefon'),
            'email'        => _('E-Mail'),
            'homepage'     => _('Homepage')
        ];

        $this->datafields_list = DataField::getDataFields('userinstrole');

        if ($this->extend === 'yes') {
            $dview = $GLOBALS['INST_ADMIN_DATAFIELDS_VIEW']['extended'];
        } else {
            $dview = $GLOBALS['INST_ADMIN_DATAFIELDS_VIEW']['default'];
        }

        if (empty($dview)) {
            $dview = ['raum', 'sprechzeiten', 'telefon', 'email'];
            if ($this->extend === 'yes') {
                $dview[] = 'homepage';
            }
        }

        foreach ($default_fields as $key => $name) {
            if (in_array($key, $dview)) {
                $this->struct[$key] = ['name' => $name, 'width' => '10%'];
            }
        }
        foreach ($this->datafields_list as $entry) {
            if (in_array($entry->id, $dview) === TRUE) {
                $this->struct[$entry->id] =  [
                    'name' => $entry->name,
                    'width' => '10%'
                ];
            }
        }

        $this->structure = $this->getTableStructure($this->struct ?: []);

        // Actual display routines
        $this->display_tables = [];

        if ($this->type == 'function') {
            $this->display_recursive($this->institute->status_groups, $dview);

            if ($GLOBALS['perm']->have_perm('admin')) {
                // Collect all assigned users and then collect all
                // institute members that have not been assigned
                $assigned = array_unique(array_flatten($this->groups->map(function ($group) {
                    return $group->members->pluck('user_id');
                })));
                $institut_members = $this->institute->members->filter(function ($member) use ($assigned) {
                    if (!$GLOBALS['perm']->have_perm('admin')
                            && !($member->visible && $member->user->visible !== 'never')) {
                        return false;
                    }
                    if ($member->inst_perms === 'user') {
                        return false;
                    }
                    return !in_array($member->user_id, $assigned);
                })->orderBy($this->sortby . ' ' . $this->direction);

                $this->display_tables[] = [
                    'members'     => $institut_members,
                    'dview'       => $dview,
                    'th_title'    => _('keiner Funktion zugeordnet'),
                ];
            }
        } elseif ($this->type == 'status') {
            $inst_permissions = [
                'admin'  => _('Admin'),
                'dozent' => _('Lehrende'),
                'tutor'  => _('Tutor/-in'),
                'autor'  => _('Studierende')
            ];

            foreach ($inst_permissions as $key => $permission) {
                $institut_members = $this->institute->members->filter(function ($member) use ($key) {
                    if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                        return false;
                    }

                    return $member->inst_perms === $key;
                })->orderBy($this->sortby . ' ' . $this->direction);

                $this->display_tables[] = [
                    'members'     => $institut_members,
                    'dview'       => $dview,
                    'mail_status' => true,
                    'key'         => $key,
                    'th_title'    => $permission,
                ];
            }
        } else {
            $institut_members = $this->institute->members->filter(function ($member) {
                if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                    return false;
                }

                if ($GLOBALS['perm']->have_perm('admin')) {
                    return $member->inst_perms !== 'user';
                }

                foreach ($this->groups as $group) {
                    if ($group->isMember($member->user_id)) {
                        return true;
                    }
                }

                return false;
            })->orderBy($this->sortby . ' ' . $this->direction);

            $this->display_tables[] = [
                'members' => $institut_members,
                'dview'   => $dview,
            ];
        }

        $this->display_tables = array_filter($this->display_tables, function ($table) {
            return count($table['members']) > 0;
        });
    }

    private function display_recursive($groups, $dview = [], $all_title = '')
    {
        foreach ($groups as $group) {
            if ($all_title == '') {
                $title = $group->name;
            } else {
                $title = $all_title . ' > '. $group->name;
            }

            // Find members
            $institut_members = $this->institute->members->filter(function ($member) use ($group) {
                if ($member->inst_perms === 'user') {
                    return false;
                }
                if (!$GLOBALS['perm']->have_perm('admin') && !($member->visible && $member->user->visible !== 'never')) {
                    return false;
                }
                return $group->isMember($member->user_id);
            });

            // Sort
            if ($this->statusgruppe_user_sortby === 'position') {
                $ordered = [];
                $group->members->each(function ($member) use (&$ordered, $institut_members) {
                    $inst_member = $institut_members->findOneBy('user_id', $member->user_id);
                    if ($inst_member) {
                        $ordered[] = $inst_member;
                    }
                });
                $institut_members->exchangeArray($ordered);
            } else {
                $institut_members = $institut_members->orderBy($this->statusgruppe_user_sortby . ' ' . $this->direction);
            };

            // output
            $this->display_tables[] = [
                'members'     => $institut_members,
                'group'       => $group,
                'th_title'    => $title,
                'dview'       => $dview,

                // StEP 154: Nachricht an alle Mitglieder der Gruppe
                'mail_gruppe' => $GLOBALS['ENABLE_EMAIL_TO_STATUSGROUP']
                              && $GLOBALS['perm']->have_studip_perm('autor', $this->institute->id),
            ];

            if ($group->children) {
                $this->display_recursive($group->children, $dview, $title);
            }
        }
    }

    // Jemand soll ans Institut...
    public function add_action($type)
    {
        $mp = MultiPersonSearch::load("inst_member_add" . $this->institute->id);
        $additionalCheckboxes = $mp->getAdditionalOptionArray() ?: [];

        $enable_mail_admin  = in_array('admins', $additionalCheckboxes);
        $enable_mail_dozent = in_array('dozenten', $additionalCheckboxes);

        foreach ($mp->getAddedUsers() as $u_id) {
            $member = new InstituteMember([$u_id, $this->institute->id]);

            if (!$member->isNew() && $member->inst_perms !== 'user') {
                // der Admin hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Institut
                PageLayout::postError(
                    _('Die Person ist bereits in der Einrichtung eingetragen.') . ' ' .
                    _('Um Rechte etc. zu ändern folgen Sie dem Link zu den persönlichen Angaben der Person!')
                );
            } else {
                // mal nach dem globalen Status sehen
                $Fullname = $member->getUserFullName('full');
                $perms    = $member->user->perms;

                if ($perms === 'root') {
                    PageLayout::postError(_('ROOTs können nicht berufen werden!'));
                } elseif ($perms == 'admin') {
                    if ($GLOBALS['perm']->have_perm('root')
                            || (!Context::get()->is_fak && $GLOBALS['perm']->have_studip_perm('admin', Context::get()->fakultaets_id))) {
                        // Emails schreiben...
                        if ($enable_mail_dozent || $enable_mail_admin) {
                            if ($enable_mail_admin && $enable_mail_dozent) {
                                $in  = ['admin', 'dozent'];
                                $wem = _('Admins und Dozenten');
                            } elseif ($enable_mail_admin){
                                $in  = ['admin'];
                                $wem = _('Admins');
                            } elseif ($enable_mail_dozent) {
                                $in  = ['dozent'];
                                $wem = _('Dozenten');
                            }

                            $notin = [];
                            $mails_sent = 0;

                            $relevant_users = $this->institute->members->findBy('inst_perms', $in);
                            foreach ($relevant_users as $user) {
                                $user_language = getUserLanguagePath($user->id);
                                include("locale/$user_language/LC_MAILS/new_admin_mail.inc.php");
                                StudipMail::sendMessage($user->email, $subject, $mailbody);
                                $notin[] = $user->id;

                                $mails_sent += 1;
                            }
                            if ($enable_mail_admin && !$this->institute->is_fak) {
                                $notin[] = $u_id;

                                $relevant_users = $this->institute->faculty->members->findBy('inst_perms', 'admin');
                                foreach ($relevant_users as $user) {
                                    if (in_array($user->id, $notin)) {
                                        continue;
                                    }

                                    $user_language = getUserLanguagePath($user->id);
                                    include("locale/$user_language/LC_MAILS/new_admin_mail.inc.php");
                                    StudipMail::sendMessage($user->email, $subject, $mailbody);
                                    $notin[] = $user->id;

                                    $mails_sent += 1;
                                }
                            }
                            PageLayout::postInfo(sprintf(
                                _('Es wurden ingesamt %u Mails an die %s der Einrichtung geschickt.'),
                                $mails_sent, $wem
                            ));
                        }

                        StudipLog::log('INST_USER_ADD', $this->institute->id, $u_id, 'admin');

                        // als admin aufnehmen
                        $member->inst_perms = 'admin';
                        $member->store();

                        PageLayout::postInfo(sprintf(_('%s wurde als "admin" in die Einrichtung aufgenommen.'), htmlReady($Fullname)));
                        NotificationCenter::postNotification('UserInstitutionDidCreate', $this->institute->id, $u_id);
                    } else {
                        PageLayout::postError(_('Sie haben keine Berechtigung einen Admin zu berufen!'));
                    }
                } else {
                    //ok, aber nur hochstufen auf Maximal-Status (hat sich selbst schonmal gemeldet als Student an dem Inst)
                    $was_new = $member->isNew();

                    $member->inst_perms = $perms;
                    if ($member->store()) {

                        if ($was_new) {
                            StudipLog::log('INST_USER_ADD', $this->institute->id ,$u_id, $perms);
                            NotificationCenter::postNotification('UserInstitutionDidCreate', $this->institute->id, $u_id);
                        } else {
                            StudipLog::log('INST_USER_STATUS', $this->institute->id ,$u_id, $perms);
                            NotificationCenter::postNotification('UserInstitutionPermDidUpdate', $this->institute->id, $u_id);
                        }

                        PageLayout::postInfo(
                            sprintf(_('%s wurde als "%s" in die Einrichtung aufgenommen.'), htmlReady($Fullname), $perms) . ' ' .
                            _('Um Rechte etc. zu ändern folgen Sie dem Link zu den persönlichen Angaben der Person!')
                        );
                    } else {
                        PageLayout::postError(sprintf(_('%s konnte nicht in die Einrichtung aufgenommen werden!'), htmlReady($Fullname)));
                    }
                }
            }
            InstituteMember::ensureDefaultInstituteForUser($u_id);
        }
        $mp->clearSession();

        $this->redirect('institute/members/' . $type);
    }

    public function remove_from_group_action($group_id, $type)
    {
        if (!$GLOBALS['perm']->have_studip_perm('admin', $this->institute->id)) {
            throw new AccessDeniedException();
        }

        $username = Request::username('username');
        $user     = User::findByUsername($username);
        $group    = Statusgruppen::find($group_id);
        $result   = $group->removeUser($user->id);

        if ($result) {
            PageLayout::postInfo(sprintf(
                _('%s wurde aus der Gruppe %s ausgetragen.'),
                htmlReady($user->getFullName()),
                htmlReady($group->name)
            ));
        }

        $this->redirect('institute/members/' . $type);
    }

    public function remove_from_institute_action($type)
    {
        $username = Request::username('username');
        $user     = User::findByUsername($username);

        if (!$GLOBALS['perm']->have_studip_perm('admin', $this->institute->id)
            || $GLOBALS['perm']->get_profile_perm($user->id) !== 'admin')
        {
            throw new AccessDeniedException();
        }

        if ($user->id === $GLOBALS['user']->id) {
            throw new Exception(_('Sie können sich nicht selbst aus der Einrichtung austragen.'));
        }

        $member = InstituteMember::find([$user->id, $this->institute->id]);
        if ($member && $member->delete()) {
            PageLayout::postInfo(sprintf(
                _('%s wurde von der Liste des Personals gelöscht.'),
                htmlReady($user->getFullName())
            ));

            StudipLog::log('INST_USER_DEL', $this->institute->id, $user->id);
            NotificationCenter::postNotification('UserInstitutionDidDelete', $this->institute->id, $user->id);
            InstituteMember::ensureDefaultInstituteForUser($user->id);
        }

        $this->redirect('institute/members/'. $type);
    }

    private function getTableStructure($additional_structure = [])
    {
        $table_structure = [
            'name' => [
                'name'    => _('Name'),
                'link'    => '?sortby=Nachname&direction=' . ($this->direction === 'ASC' ? 'DESC' : 'ASC'),
                'colspan' => 2,
            ],
            'status' => [
                'name' => _('Status'),
                'link' => '?sortby=inst_perms&direction=' . ($this->direction === 'ASC' ? 'DESC' : 'ASC'),
                'width' => 70,
            ],
            'statusgruppe' => [
                'name'  => _('Funktion'),
                'width' => '20%',
            ],
        ];

        if ($this->extend === 'yes') {
            $table_structure['statusgruppe']['width'] = '15%';
        }

        if ($this->type === 'status' || !$this->admin_view) {
            unset($table_structure['status']);
        }
        if (!in_array($this->type, ['list', 'status'])) {
            unset($table_structure['statusgruppe']);
        }

        $table_structure = array_merge($table_structure, (array)$additional_structure);

        if ($this->admin_view || $GLOBALS['perm']->have_studip_perm('autor', $this->institute->id)) {
            $table_structure['actions'] = [
                'name' => _('Aktionen'),
                'width' => '5%'
            ];
        }

        return $table_structure;
    }

    private function setupSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/person-sidebar.png');

        $widget = new ViewsWidget();
        $widget->addLink(
            _('Standard'),
            URLHelper::getURL('?extend=no')
        )->setActive($this->extend !== 'yes');
        $widget->addLink(
            _('Erweitert'),
            URLHelper::getURL('?extend=yes')
        )->setActive($this->extend === 'yes');
        $sidebar->addWidget($widget);

        if ($this->admin_view) {
            $actions = new ActionsWidget();

            if (!LockRules::Check($this->institute->id, 'participants')) {
                $search = $this->getMultipersonSearch();
                $icon   = $search->getLinkIconPath();
                $search->setLinkIconPath(false);
                $actions->addElement(LinkElement::fromHTML($search->render(), $icon));
            }

            // Mitglieder zählen und E-Mail-Adressen zusammenstellen
            $valid_mail_members = $this->institute->members->filter(function ($member) {
                return $member->inst_perms !== 'user'
                    && (bool)$member->email;
            });
            if (count($valid_mail_members) > 0) {
                $actions->addLink(
                    _('Stud.IP Rundmail'),
                    $this->url_for('messages/write', ['inst_id' => $this->institute->id, 'emailrequest' => 1]),
                    Icon::create('mail', 'clickable'),
                    ['data-dialog' => 'size=50%']
                );
            }

            $sidebar->addWidget($actions);
        }


        $widget = new OptionsWidget(_('Gruppierung'));
        // Admins can choose between different grouping functions
        if ($this->admin_view) {
            $widget->addRadioButton(
                _('Funktion'),
                $this->link_for('institute/members/function'),
                $this->type === 'function'
            );
            $widget->addRadioButton(
                _('Status'),
                $this->link_for('institute/members/status'),
                $this->type === 'status'
            );
            $widget->addRadioButton(
                _('keine'),
                $this->link_for('institute/members/list'),
                $this->type === 'list');
        } else {
            $widget->addRadioButton(
                _('Nach Funktion gruppiert'),
                $this->link_for('institute/members/function'),
                $this->type === 'function'
            );
            $widget->addRadioButton(
                _('Alphabetische Liste'),
                $this->link_for('institute/members/list'),
                $this->type === 'list'
            );
        }
        $sidebar->addWidget($widget);

        if (Config::get()->EXPORT_ENABLE && $GLOBALS['perm']->have_perm('tutor')) {
            $widget = new ExportWidget();
            $widget->addElement(new WidgetElement(export_form_sidebar($this->institute->id,
                'person', $this->institute->Name)));
            $sidebar->addWidget($widget);
        }
    }

    private function getMultipersonSearch()
    {
        $query = "SELECT auth_user_md5.user_id,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname,
                         username, perms
                  FROM auth_user_md5
                  LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id)
                  WHERE visible != 'never'
                    AND (username LIKE :input OR
                         CONCAT(Vorname, ' ', Nachname) LIKE :input OR
                         CONCAT(Nachname, ' ', Vorname) LIKE :input OR
                         {$GLOBALS['_fullname_sql']['full_rev']} LIKE :input)
                  ORDER BY fullname ASC";
        $search_obj = new SQLSearch($query, _('Nutzer suchen'), 'user_id');

        $defaultSelectedUser = $this->institute->members->findBy('inst_perms', words('autor tutor dozent admin'));

        return MultiPersonSearch::get('inst_member_add' . $this->institute->id)
            ->setLinkText(_('Personen in die Einrichtung eintragen'))
            ->setDefaultSelectedUser($defaultSelectedUser->pluck('user_id'))
            ->setTitle(_('Personen in die Einrichtung eintragen'))
            ->setExecuteURL($this->link_for('institute/members/add', $this->type, ['admin_view' => 1]))
            ->setSearchObject($search_obj)
            ->setAdditionalHTML('<p><strong>' . _('Nur bei Zuordnung eines Admins:') .' </strong>
                            <label><input name="additional[]" value="admins" type="checkbox">' . _('Benachrichtigung der Admins') . '</label>
                            <label><input name="additional[]" value="dozenten" type="checkbox">' . _('Benachrichtigung der Dozenten') . '</label>
                            </p>');
    }
}
