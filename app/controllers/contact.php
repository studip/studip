<?php

/**
 * ContactController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.2
 */
class ContactController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Load statusgroups
        $this->groups = SimpleCollection::createFromArray(Statusgruppen::findByRange_id(User::findCurrent()->id));

        // Load requested group
        if ($args[0]) {
            $this->group = $this->groups->findOneBy('statusgruppe_id', $args[0]);

            //Check for cheaters
            if ($this->group->range_id != User::findCurrent()->id) {
                throw new AccessDeniedException;
            }
        }

        PageLayout::setTitle(_('Kontakte'));
    }

    /**
     * Main action to display contacts
     */
    public function index_action($filter = null)
    {
        // Check if we need to add contacts
        $mps      = MultiPersonSearch::load('contacts');
        $imported = 0;
        foreach ($mps->getAddedUsers() as $userId) {
            $user_to_add = User::find($userId);
            if ($user_to_add) {
                $new_contact = [
                    'owner_id' => User::findCurrent()->id,
                    'user_id'  => $user_to_add->id];
                if ($filter && $this->group) {
                    $new_contact['group_assignments'][] = [
                        'statusgruppe_id' => $this->group->id,
                        'user_id'         => $user_to_add->id
                    ];
                }
                $imported += (bool)Contact::import($new_contact)->store();
            }
        }
        if ($imported) {
            PageLayout::postSuccess(sprintf(_('%s Kontakte wurden hinzugefügt.'), $imported));
        }
        $mps->clearSession();

        // write filter to local
        $this->filter = $filter;

        // Deal with navigation
        Navigation::activateItem('community/contacts');

        // Edit CSS for quicknavigation
        PageLayout::addStyle('div.letterlist span {color: #c3c8cc;}');

        if ($filter) {
            $selected = $this->group;
            $contacts = SimpleCollection::createFromArray(User::findMany($selected->members->pluck('user_id')));
        } else {
            $contacts = User::findCurrent()->contacts;
        }
        $contacts = $contacts->filter(function($u) {
            $visible = get_visibility_by_state($u->visible, $u->id);
            if ($visible && ! get_local_visibility_by_id($u->id, 'email')) {
                $u->email = '';
            }
            return $visible;
        });
        $this->allContacts = $contacts;

        // Retrive first letter and store in that contactgroup
        $this->contacts = [];
        foreach ($contacts as $contact) {
            $this->contacts[mb_strtoupper(SimpleCollection::translitLatin1(mb_substr($contact->nachname,0,1)))][] = $contact;
        }

        ksort($this->contacts);
        $this->contacts = array_map(function ($g) {
            return SimpleCollection::createFromArray($g)->orderBy('nachname, vorname');
        }, $this->contacts);

        $this->initSidebar($filter);
        $mps = MultiPersonSearch::get('contacts')
            ->setTitle(_('Kontakte hinzufügen'))
            ->setDefaultSelectedUser($this->allContacts->pluck('user_id'))
            ->setExecuteURL($this->url_for('contact/index/' . $filter))
            ->setSearchObject(new StandardSearch('user_id'));
        $this->title = _('Alle Kontakte');

        if ($selected) {
            $this->title = $selected->name;
            $mps->setTitle(sprintf(_('Kontakte zu %s hinzufügen'), $selected->name));
            $mps->addQuickfilter(_('Kontakte'), User::findCurrent()->contacts->pluck('user_id'));
        }
        $this->multiPerson = $mps->render();
    }

    public function remove_action($group = null)
    {
        if (Request::get('action') == 'collection') {
            if ($this->flash['contacts']) {
                $removed_numbers      = 0;
                $removed_group_number = 0;
                foreach ($this->flash['contacts'] as $contact_username => $checked) {
                    $contact = Contact::find([User::findCurrent()->id, User::findByUsername($contact_username)->id]);
                    if ($contact) {
                        if ($group) {
                            $contact->group_assignments->unsetBy('statusgruppe_id', $group);
                            if ($contact->store()) {
                                $removed_group_number++;
                            }
                        } else {
                            if ($contact->delete()) {
                                $removed_numbers++;
                            }
                        }
                    }
                }
                $removed_numbers ? PageLayout::postSuccess("{$removed_numbers} " . _('Kontakt(e) wurde(n) entfernt.')) : '';
                $removed_group_number ? PageLayout::postSuccess("{$removed_group_number} " . _('Kontakt(e) wurde(n) aus der Gruppe entfernt.')) : '';
            }
        } else {
            CSRFProtection::verifyUnsafeRequest();
            $contact = Contact::find([User::findCurrent()->id, User::findByUsername(Request::username('user'))->id]);
            if ($contact) {
                if ($group) {
                    $contact->group_assignments->unsetBy('statusgruppe_id', $group);
                    if ($contact->store()) {
                        PageLayout::postSuccess(_('Der Kontakt wurde aus der Gruppe entfernt.'));
                    }
                } else {
                    if ($contact->delete()) {
                        PageLayout::postSuccess(_('Der Kontakt wurde entfernt.'));
                    }
                }
            }
        }
        $this->redirect('contact/index/' . $group);
    }

    public function editGroup_action()
    {
        if (!$this->group) {
            $this->group           = new Statusgruppen();
            $this->group->range_id = User::findCurrent()->id;
        }
        if (Request::submitted('store')) {
            CSRFProtection::verifyRequest();
            $this->group->name = Request::get('name');
            $this->group->store();
            $this->redirect('contact/index/' . $this->group->id);
        }
    }

    public function deleteGroup_action()
    {
        CSRFProtection::verifyRequest();
        $this->group->delete();
        $this->redirect('contact/index');
    }

    public function vcard_action($group = null)
    {
        $charset  = 'utf-8';
        $filename = _('Kontakte');
        $this->set_layout(null);
        if (Request::submitted('user')) {
            $user = User::findManyByUsername(Request::getArray('user'));
        }
        if ($group) {
            $user = User::findMany(Statusgruppen::find($group)->members->pluck('user_id'));
        }
        if (!$user) {
            $user = User::findCurrent()->contacts;
        }

        header("Content-type: text/x-vCard;charset=" . $charset);
        header("Content-disposition: attachment; " . encode_header_parameter('filename', $filename . '.vcf'));
        header("Pragma: private");

        $this->vCard = vCard::export($user);
    }

    private function initSidebar($active_id = null)
    {
        $sidebar = Sidebar::Get();
        $letterlist = new SidebarWidget();
        $html       = '';
        foreach (range('A', 'Z') as $letter) {
            if ($this->contacts[$letter]) {
                $html .= "<a href=\"#letter_{$letter}\">{$letter}</a>";
            } else {
                $html .= "<span>{$letter}</span>";
            }
        }
        $letterlist->setTitle(_('Schnellzugriff'));
        $letterlist->addElement(new WidgetElement("<div class=\"letterlist\">{$html}</div>"));
        $sidebar->addWidget($letterlist);

        // Groups
        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neue Gruppe anlegen'),
            $this->url_for('contact/editGroup'),
            Icon::create('group3+add')
        )->asDialog('size=auto');
        $actions->addLink(
            _('Nachricht an alle'),
            $this->url_for('messages/write', ['rec_uname' => $this->allContacts->pluck('username')]),
            Icon::create('mail')
        )->asDialog();
        $actions->addLink(
            _('E-Mail an alle'),
            URLHelper::getURL('mailto:' . join(',', $this->allContacts->pluck('email'))),
            Icon::create('mail')
        );
        $actions->addLink(
            _('Alle vCards herunterladen'),
            $this->url_for('contact/vcard/' . $this->filter),
            Icon::create('vcard')
        );
        $sidebar->addWidget($actions);

        // Groups navigation
        $groupsWidget = new ViewsWidget();
        $groupsWidget->setTitle(_('Gruppen'));
        $groupsWidget->addLink(
            _('Alle Kontakte'),
            URLHelper::getURL('dispatch.php/contact/index')
        )->setActive(!$active_id);
        foreach ($this->groups as $group) {
            $groupsWidget->addLink(
                $group->name,
                URLHelper::getURL('dispatch.php/contact/index/' . $group->id)
            )->setActive($group->id == $active_id);
        }
        $sidebar->addWidget($groupsWidget);
    }

    /**
     * Helper function to select the action
     */
    public function edit_contact_action($group = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['contacts'] = Request::getArray('contact');

        switch (Request::get('action_contact')) {
            case 'remove':
                $target = "contact/remove/{$group}?action=collection";
                break;
            default:
                $target = "contact/index/{$group}";
                break;
        }
        $this->relocate($target);
    }
}
