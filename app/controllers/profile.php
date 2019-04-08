<?php
/**
 * ProfileController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'lib/messaging.inc.php';
require_once 'lib/object.inc.php';
require_once 'lib/statusgruppe.inc.php';
require_once 'lib/user_visible.inc.php';

class ProfileController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Remove cid
        Context::close();

        Navigation::activateItem('/profile/index');
        URLHelper::addLinkParam('username', Request::username('username'));
        PageLayout::setHelpKeyword('Basis.Homepage');
        SkipLinks::addIndex(_('Benutzerprofil'), 'user_profile', 100);

        $this->user         = User::findCurrent(); // current logged in user
        $this->perm         = $GLOBALS['perm']; // perms of current logged in user
        $this->current_user = User::findByUsername(Request::username('username', $this->user->username)); // current selected user
        // get additional informations to selected user
        $this->profile = new ProfileModel($this->current_user->user_id, $this->user->user_id);

        // set the page title depending on user selection
        if ($this->current_user['user_id'] == $this->user->id && !$this->current_user['locked']) {
            PageLayout::setTitle(_('Mein Profil'));
            UserConfig::get($this->user->id)->store('PROFILE_LAST_VISIT', time());
        } elseif ($this->current_user['user_id'] && ($this->perm->have_perm('root') || (!$this->current_user['locked'] && get_visibility_by_id($this->current_user['user_id'])))) {
            PageLayout::setTitle(_('Profil von') . ' ' . $this->current_user->getFullname());
            object_add_view($this->current_user->user_id);
        } else {
            PageLayout::setTitle(_('Profil'));
            $action = 'not_available';
        }
    }

    /**
     * Entry point of the controller that displays all the information of the selected or current user
     * @return void
     */
    public function index_action()
    {
        // Template Index_Box for render-partials
        $layout           = $GLOBALS['template_factory']->open('shared/content_box');
        $this->shared_box = $layout;

        // if he has not yet stored into user_info, he comes in with no values
        if ($this->current_user->mkdate === null) {
            $this->current_user->store();
        }

        if (Config::get()->NEWS_RSS_EXPORT_ENABLE) {
            $news_author_id = StudipNews::GetRssIdFromUserId($this->current_user->user_id);
            if ($news_author_id) {
                PageLayout::addHeadElement('link', [
                    'rel'   => 'alternate',
                    'type'  => 'application/rss+xml',
                    'title' => 'RSS',
                    'href'  => 'rss.php?id=' . $news_author_id,
                ]);
            }
        }

        // GetScroreList
        if (Config::get()->SCORE_ENABLE) {
            if ($this->current_user->user_id === $GLOBALS['user']->id || $this->current_user->score) {
                $this->score       = Score::GetMyScore($this->current_user);
                $this->score_title = Score::getTitel($this->score, $this->current_user->geschlecht);
            }
        }

        // Additional user information
        $this->public_email = get_visible_email($this->current_user->user_id);
        $this->motto        = $this->profile->getVisibilityValue('motto');
        $this->private_nr   = $this->profile->getVisibilityValue('privatnr', 'private_phone');
        $this->private_cell = $this->profile->getVisibilityValue('privatcell', 'private_cell');
        $this->privadr      = $this->profile->getVisibilityValue('privadr', 'privadr');
        $this->homepage     = $this->profile->getVisibilityValue('Home', 'homepage');

        // skype informations
        if (Config::get()->ENABLE_SKYPE_INFO && Visibility::verify('skype_name', $this->current_user->user_id)) {
            $this->skype_name = UserConfig::get($this->current_user->user_id)->SKYPE_NAME;
        }

        // get generic datafield entries
        $this->shortDatafields = $this->profile->getShortDatafields();
        $this->longDatafields  = $this->profile->getLongDatafields();

        // get working station of an user (institutes)
        $this->institutes = $this->getInstitutInformation();

        // get studying informations of an user
        $this->study_institutes = [];
        if ($this->current_user->perms !== 'dozent') {
            if (count($this->current_user->institute_memberships) > 0 && Visibility::verify('studying', $this->current_user->user_id)) {
                $study_institutes = $this->current_user->institute_memberships->filter(function ($a) {
                    return $a->inst_perms === 'user';
                });
                $this->study_institutes = $study_institutes;
            }
        }

        $show_admin = ($this->perm->have_perm('autor') && $this->user->user_id == $this->current_user->user_id)
                   || (isDeputyEditAboutActivated() && isDeputy($this->user->user_id, $this->current_user->user_id, true));
        if (Visibility::verify('news', $this->current_user->user_id) || $show_admin) {
            $response   = $this->relay('news/display/' . $this->current_user->user_id);
            $this->news = $response->body;
        }

        // calendar
        if (Config::get()->CALENDAR_ENABLE) {
            if (!in_array($this->current_user->perms, ['admin', 'root'])) {
                if (Visibility::verify('termine', $this->current_user->user_id)) {
                    $response    = $this->relay('calendar/contentbox/display/' . $this->current_user->user_id);
                    $this->dates = $response->body;
                }
            }
        }

        // include and show votes and tests
        if (Config::get()->VOTE_ENABLE && Visibility::verify('votes', $this->current_user->user_id)) {
            $response          = $this->relay('evaluation/display/' . $this->current_user->user_id);
            $this->evaluations = $response->body;

            $response             = $this->relay('questionnaire/widget/' . $this->current_user->user_id . "/user");
            $this->questionnaires = $response->body;
        }

        // Hier werden Lebenslauf, Hobbys, Publikationen und Arbeitsschwerpunkte ausgegeben:
        $ausgabe_felder = [
            'lebenslauf' => _('Lebenslauf'),
            'hobby'      => _('Hobbys'),
            'publi'      => _('Publikationen'),
            'schwerp'    => _('Arbeitsschwerpunkte'),
        ];

        $ausgabe_inhalt = [];
        foreach ($ausgabe_felder as $key => $value) {
            if (Visibility::verify($key, $this->current_user->user_id)) {
                $ausgabe_inhalt[$value] = $this->current_user[$key];
            }
        }
        $this->ausgabe_inhalt = array_filter($ausgabe_inhalt, function ($item) {
            return (string)$item;
        });

        //public folders
        $folders = Folder::findBySQL("range_type='user' AND range_id = ? AND folder_type = 'PublicFolder'", [$this->current_user->user_id]);
        $public_files = [];
        $public_folders =[];
        foreach ($folders as $folder) {
            $one_public_folder = $folder->getTypedFolder();
            if ($one_public_folder->viewable) {
                $all_files = FileManager::getFolderFilesRecursive($one_public_folder, $GLOBALS['user']->id);
                $public_files = array_merge($public_files, $all_files['files']);
                $public_folders = array_merge($public_folders, $all_files['folders']);
            }
        }
        if (count($public_files)) {
            $this->public_files = $public_files;
            $this->public_folders = $public_folders;
        }

        // Anzeige der Seminare, falls User = dozent
        if ($this->current_user['perms'] == 'dozent') {
            $this->seminare = array_filter($this->profile->getDozentSeminars());
        }

        // Hompageplugins
        $homepageplugins = PluginEngine::getPlugins('HomepagePlugin');

        $render = '';
        $layout = $GLOBALS['template_factory']->open('shared/content_box');
        foreach ($homepageplugins as $homepageplugin) {
            if ($homepageplugin->isActivated($this->current_user->user_id, 'user')) {
                // get homepageplugin tempaltes
                $template = $homepageplugin->getHomepageTemplate($this->current_user->user_id);
                // create output of the plugins
                if (!empty($template)) {
                    $render .= $template->render(null, $layout);
                }
                $layout->clear_attributes();
            }
        }

        $this->hompage_plugin = $render;

        // show literature info
        if (Config::get()->LITERATURE_ENABLE) {
            $lit_list = StudipLitList::GetFormattedListsByRange($this->current_user->user_id);
            if ($this->current_user->user_id == $this->user->user_id) {
                $this->admin_url   = 'dispatch.php/literature/edit_list.php?_range_id=self';
                $this->admin_title = _('Literaturlisten bearbeiten');
            }

            if (Visibility::verify('literature', $this->current_user->user_id)) {
                $this->show_lit = true;
                $this->lit_list = $lit_list;
            }
        }

        // get categories
        $category = Kategorie::findByUserId($this->current_user->user_id);

        foreach ($category as $cat) {
            $head = $cat->name;
            $body = $cat->content;
            unset($vis_text);

            if ($this->user->user_id == $this->current_user->user_id) {
                $vis_text .= ' ( ' . Visibility::getStateDescription('kat_' . $cat->kategorie_id) . ' )';
            }

            if (Visibility::verify('kat_' . $cat->kategorie_id, $this->current_user->user_id)) {
                $categories[$cat->kategorie_id]['head']    = $head;
                $categories[$cat->kategorie_id]['zusatz']  = $vis_text;
                $categories[$cat->kategorie_id]['content'] = $body;
            }
        }

        if (!empty($categories)) {
            $this->categories = array_filter($categories, function ($item) {
                return !empty($item['content']);
            });
        }


        $sidebar = Sidebar::Get();

        //The profile avatar, profile visits and profile score
        //shall be visible in the sidebar. Therefore we must construct
        //a generic WidgetElement object and its HTML in here.

        if (Config::Get()->SCORE_ENABLE) {
            if ($this->current_user->user_id === $GLOBALS['user']->id || $this->current_user->score) {
                $kings = $this->current_user->getStudipKingIcon();
            }
        }

        $avatar_widget = new TemplateWidget(
            $this->current_user->getFullName(),
            $this->get_template_factory()->open('profile/widget-avatar.php'),
            [
                'avatar'      => Avatar::getAvatar($this->current_user->user_id),
                'kings'       => $kings,
                'views'       => object_return_views($this->current_user->user_id),
                'score'       => $this->score,
                'score_title' => $this->score_title,
                'current_user' => $this->current_user->user_id
            ]
        );

        $avatar_widget->setTitle($this->current_user->getFullName());
        $sidebar->addWidget($avatar_widget);

        $actions = new ActionsWidget();
        //If a user visits the profile of another user
        //we add a few more actions to the sidebar:
        if ($this->current_user->username != $this->user->username) {
            if ($GLOBALS['perm']->have_perm('root')) {
                $actions->addLink(
                    _('Dieses Konto bearbeiten'),
                    $this->url_for('admin/user/edit/' . $this->current_user->user_id),
                    Icon::create('edit', Icon::ROLE_CLICKABLE, tooltip2(_('Dieses Konto bearbeiten')))
                );
            }

            if (!$this->user->isFriendOf($this->current_user)) {
                $actions->addLink(
                    _('Zu den Kontakten hinzufügen'),
                    $this->url_for('profile/add_buddy?username=' . $this->current_user->username),
                    Icon::create('person+add', Icon::ROLE_CLICKABLE, tooltip2(_('Zu den Kontakten hinzufügen'))),
                    ['data-confirm' => _('Wollen Sie die Person wirklich als Kontakt hinzufügen?')]
                )->asButton();
            } else {
                $actions->addLink(
                    _('Von den Kontakten entfernen'),
                    $this->url_for('profile/remove_buddy', ['username' => $this->current_user->username]),
                    Icon::create('person+remove', Icon::ROLE_CLICKABLE, tooltip2(_('Zu den Kontakten hinzufügen'))),
                    ['data-confirm' => _('Wollen Sie die Person wirklich von den Kontakten entfernen?')]
                )->asButton();
            }

            $actions->addLink(
                _('Nachricht schreiben'),
                $this->url_for('messages/write', ['rec_uname' => $this->current_user->username]),
                Icon::create('mail', Icon::ROLE_CLICKABLE, tooltip2(_('Nachricht an Nutzer verschicken')))
            )->asDialog('size="50%"');

            if (class_exists('Blubber')) {
                $actions->addLink(
                    _('Anblubbern'),
                    URLHelper::getURL('plugins.php/blubber/streams/global', ['mention' => $this->current_user->username]),
                    Icon::create('blubber', Icon::ROLE_CLICKABLE, tooltip2(_('Blubber diesen Nutzer an')))
                );
            }

        }
        $actions->addLink(
            _('vCard herunterladen'),
            $this->url_for('contact/vcard', ['user[]' => $this->current_user->username]),
            Icon::create('vcard', Icon::ROLE_CLICKABLE, tooltip2(_('vCard herunterladen')))
        );

        $sidebar->addWidget($actions);

        $privacy = new LinksWidget();
        $privacy->setTitle(_('Datenschutz'));

        if (Privacy::isVisible($this->current_user->user_id)) {
            $privacy->addLink(
                _('Anzeige Personendaten'),
                $this->url_for('privacy/landing/' . $this->current_user->user_id),
                Icon::create('log')
            )->asDialog('size=medium');

            $privacy->addLink(
                _('Personendaten drucken'),
                $this->url_for('privacy/print/' . $this->current_user->user_id),
                Icon::create('print'),
                ['class' => 'print_action', 'target' => '_blank']
            );

            $privacy->addLink(
                _('Export Personendaten als CSV'),
                $this->url_for('privacy/export/' . $this->current_user->user_id),
                Icon::create('file-text')
            );

            $privacy->addLink(
                _('Export Personendaten als XML'),
                $this->url_for('privacy/xml/' . $this->current_user->user_id),
                Icon::create('file-text')
            );

            $privacy->addLink(
                _('Export persönlicher Dateien als ZIP'),
                $this->url_for('privacy/filesexport/' . $this->current_user->user_id),
                Icon::create('file-archive')
            );
        } elseif ($this->current_user->username === $this->user->username && Config::get()->PRIVACY_CONTACT) {
            $privacy->addLink(
                _('Datenschutzauskunft anfordern'),
                $this->url_for('privacy/askfor/' . $this->current_user->user_id),
                Icon::create('mail')
            )->asDialog('size=auto');
        }
        $sidebar->addWidget($privacy);

        $info_widget = new SidebarWidget();
        $info_widget->setTitle(_('Informationen'));

        if (!get_visibility_by_id($this->current_user->user_id)) {
            if ($this->current_user->user_id !== $this->user->user_id) {
                $string = _('(Dieser Nutzer ist unsichtbar.)');
            } else {
                $string = _('(Sie sind unsichtbar. Deshalb können nur Sie diese Seite sehen.)');
            }
            $info_widget->addElement(
                new WidgetElement('<span style="color:red;">' . $string . '</span>')
            );
        }

        if ($GLOBALS['perm']->have_perm('root') && $this->current_user['locked']) {
            $info_widget->addElement(
                new WidgetElement('<span style="color:red;">' . _('BENUTZER IST GESPERRT!') . '</span>')
            );
        }
        if ($this->current_user->auth_plugin === null) {
            $info_widget->addElement(
                new WidgetElement('<span style="color:red;">' . _('vorläufiger Benutzer') . '</span>')
            );
        }

        if (count($info_widget->getElements())) {
            $sidebar->addWidget($info_widget);
        }

        if ($this->motto) {
            $motto_widget = new SidebarWidget();
            $motto_widget->setTitle(_('Motto'));
            $motto_widget->addElement(new WidgetElement(htmlReady($this->motto)));
            $sidebar->addWidget($motto_widget);
        }
    }

    /**
     * Action for a selection, where the user not exists
     *
     * @return void
     */
    public function not_available_action()
    {
        Navigation::getItem('/profile')->setActive(false);
    }

    /**
     * Adds the user identified by the variable username to the current user's
     * contacts.
     */
    public function add_buddy_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $username            = Request::username('username');
        $user                = User::findByUsername($username);
        $current             = User::findCurrent();
        $current->contacts[] = $user;
        $current->store();

        PageLayout::postSuccess(_('Der Nutzer wurde zu Ihren Kontakten hinzugefügt.'));
        $this->redirect('profile/index?username=' . $username);
    }


    /**
     * Removes the user identified by the variable username from the current
     * user's contacts.
     */
    public function remove_buddy_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $username = Request::username('username');
        $current  = User::findCurrent();

        $current->contacts = $current->contacts->filter(function ($contact) use ($username) {
            return $contact->username !== $username;
        });

        $current->store();

        PageLayout::postSuccess(_('Der Kontakt wurde entfernt.'));
        $this->redirect('profile/index?username=' . $username);
    }
    /**
     * Returns user-institutes
     * @return mixed
     */
    private function getInstitutInformation()
    {
        $institutes = $this->current_user->institute_memberships->filter(function ($member) {
            return $member->inst_perms !== 'user'
                && $member->visible;
        });

        $institutes = $institutes->orderBy('priority asc');
        $institutes = $institutes->toArray();

        foreach ($institutes as $id => $institute) {
            $entries = DataFieldEntry::getDataFieldEntries([
                $this->current_user->user_id,
                $institute['institut_id'],
            ]);

            foreach ($entries as $entry) {
                $view      = $entry->isVisible(null, false);
                $show_star = false;

                if (!$view && $entry->isVisible()) {
                    $view      = true;
                    $show_star = true;
                }

                if (trim($entry->getValue()) && $view) {
                    $institutes[$id]['datafield'][] = [
                        'name'      => $entry->getName(),
                        'value'     => $entry->getDisplayValue(),
                        'show_star' => $show_star,
                    ];
                }
            }

            $groups = GetAllStatusgruppen(
                $institute['institut_id'],
                $this->current_user->user_id
            );

            $default_entries = $entries;
            $data = get_role_data_recursive(
                $groups,
                $this->current_user->user_id,
                $default_entries
            );

            $institutes[$id]['role'] = $data;
        }

        return $institutes;
    }
}
