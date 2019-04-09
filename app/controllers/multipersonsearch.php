<?php
/**
 * multipersonsearch.php - trails-controller for MultiPersonSearch
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * he License, or (at your option) any later version.
 *
 * @author      Sebastian Hobert <sebastian.hobert@uni-goettingen.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class MultipersonsearchController extends AuthenticatedController
{
    /**
     * Ajax action used for searching persons.
     *
     * @param $name string name of MultiPersonSearch object
     */
    public function ajax_search_action($name)
    {
        $searchterm = Request::get('s');
        $searchterm = str_replace(',', ' ', $searchterm);
        $searchterm = preg_replace('/\s+/u', ' ', $searchterm);

        $result = [];
        // execute searchobject if searchterm is at least 3 chars long
        if (mb_strlen($searchterm) >= 3) {
            $mp = MultiPersonSearch::load($name);
            $searchObject = $mp->getSearchObject();
            $result = array_map(function ($r) {
                return $r['user_id'];
            }, $searchObject->getResults($searchterm, [], 50));
            $result = User::findFullMany($result, 'ORDER BY Nachname ASC, Vorname ASC');
            $alreadyMember = $mp->getDefaultSelectedUsersIDs();
        }

        $output = [];
        foreach ($result as $user) {
            $output[] = [
                'user_id' => $user->id,
                'avatar'  => Avatar::getAvatar($user->id)->getURL(Avatar::SMALL),
                'text'    => "{$user->nachname}, {$user->vorname} -- {$user->perms} ({$user->username})",
                'member'  => in_array($user->id, $alreadyMember),
            ];
        }
        $this->render_json($output);
    }

    /**
     * Action which handles dialog form inputs.
     *
     * This action checks for CSRF and redirects to the action which
     * handles adding/removing users.
     */
    public function js_form_exec_action() {
        CSRFProtection::verifyUnsafeRequest();
        $this->name = Request::get("name");
        $mp = MultiPersonSearch::load($this->name);
        $mp->saveAddedUsersToSession();
        $this->redirect($mp->getExecuteURL());
    }


    /**
     * Action which shows a js-enabled dialog form.
     */
    public function js_form_action($name)
    {
        $mp = MultiPersonSearch::load($name);
        $this->name = $name;
        $this->description = $mp->getDescription();
        $this->quickfilter = $mp->getQuickfilterIds();
        $this->additionHTML = $mp->getAdditionHTML();
        $this->data_dialog_status = $mp->getDataDialogStatus();
        foreach ($this->quickfilter as $title => $users) {
            $tmp = User::findFullMany($users, 'ORDER BY Nachname ASC, Vorname ASC');
            $this->quickfilter[$title] = $tmp;
        }
        $this->executeURL = $mp->getExecuteURL();
        $this->jsFunction = $mp->getJSFunctionOnSubmit();
        $tmp = User::findFullMany($mp->getDefaultSelectableUsersIDs(), 'ORDER BY Nachname asc, Vorname asc');
        $this->defaultSelectableUsers = $tmp;
        $tmp = User::findFullMany($mp->getDefaultSelectedUsersIDs(), 'ORDER BY Nachname ASC, Vorname ASC');
        $this->defaultSelectedUsers = $tmp;
        $this->ajax = Request::isXhr();

        if ($this->ajax) {
            $this->set_layout(null);
        } else {
            $this->title = $mp->getTitle();
            if ($mp->getNavigationItem() != "") {
                Navigation::activateItem($mp->getNavigationItem());
            }
        }
    }

    /**
     * Action which is used for handling all submits for no-JavaScript
     * users:
     * * searching,
     * * adding a person,
     * * removing a person,
     * * selcting a quickfilter,
     * * aborting,
     * * saving.
     *
     * This needs to be done in one single action to provider a similar
     * usability for no-JavaScript users as for JavaScript users.
     */
    public function no_js_form_action() {

        if (!empty($_POST)) {
            CSRFProtection::verifyUnsafeRequest();
        }

        $this->name = Request::get("name");
        $mp = MultiPersonSearch::load($this->name);

        $this->selectableUsers = [];
        $this->selectedUsers = [];
        $this->search = Request::get("freesearch");
        $this->additionHTML = $mp->getAdditionHTML();
        $previousSelectableUsers = studip_json_decode(Request::get('search_persons_selectable_hidden'));
        $previousSelectedUsers = studip_json_decode(Request::get('search_persons_selected_hidden'));

        // restore quickfilter
        $this->quickfilterIDs = $mp->getQuickfilterIds();
        foreach($this->quickfilterIDs as $title=>$array) {
            $this->quickfilter[] = $title;
        }

        // abort
        if (Request::submitted('abort')) {
            $this->redirect($_SESSION['multipersonsearch'][$this->name]['pageURL']);
        }
        // search
        elseif (Request::submitted('submit_search')) {
            // evaluate search
            $this->selectedUsers = User::findMany($previousSelectedUsers);
            $searchterm = Request::get('freesearch');
            $searchObject = $mp->getSearchObject();
            $result = array_map(function($r) {return $r['user_id'];}, $searchObject->getResults($searchterm, [], 50));
            $this->selectableUsers = User::findMany($result);

            // remove already selected users
            foreach ($this->selectableUsers as $key=>$user) {
                if (in_array($user->id, $previousSelectedUsers) || in_array($user->id, $mp->getDefaultSelectedUsersIDs())) {
                    unset($this->selectableUsers[$key]);
                    $this->alreadyMemberUsers[$key] = $user;
                }
            }
        }
        // quickfilter
        elseif (Request::submitted('submit_search_preset')) {
            $this->selectedUsers = User::findMany($previousSelectedUsers);
            $this->selectableUsers = User::findMany($this->quickfilterIDs[Request::get('search_preset')]);
            // remove already selected users
            foreach ($this->selectableUsers as $key=>$user) {
                if (in_array($user->id, $previousSelectedUsers) || in_array($user->id, $mp->getDefaultSelectedUsersIDs()) ) {
                    unset($this->selectableUsers[$key]);
                }
            }
        }
        // add user
        elseif (Request::submitted('search_persons_add')) {
            // add users
            foreach (Request::optionArray('search_persons_selectable') as $userID) {
                if (($key = array_search($userID, $previousSelectableUsers)) !== false) {
                    unset($previousSelectableUsers[$key]);
                }
                $previousSelectedUsers[] = $userID;
            }

            $this->selectedUsers = User::findMany($previousSelectedUsers);
            $this->selectableUsers = User::findMany($previousSelectableUsers);
        }
        // remove user
        elseif (Request::submitted('search_persons_remove')) {
            // remove users
            foreach (Request::optionArray('search_persons_selected') as $userID) {
                if (($key = array_search($userID, $previousSelectedUsers)) !== false) {
                    unset($previousSelectedUsers[$key]);
                }
                $previousSelectableUsers[] = $userID;
            }

            $this->selectedUsers = User::findMany($previousSelectedUsers);
            $this->selectableUsers = User::findMany($previousSelectableUsers);
        }
        // save
        elseif (Request::submitted('save')) {
            // find added users
            $addedUsers = [];
            $defaultSelectedUsersIDs = $searchObject = $mp->getDefaultSelectedUsersIDs();
            foreach ($previousSelectedUsers as $selected) {
                if (!in_array($selected, $defaultSelectedUsersIDs)) {
                    $addedUsers[] = $selected;
                }
            }
            // find removed users
            $removedUsers = [];
            foreach ($defaultSelectedUsersIDs as $default) {
                if (!in_array($default, $previousSelectedUsers)) {
                    $removedUsers[] = $default;

                }
            }
            $_SESSION['multipersonsearch'][$this->name]['selected'] = $previousSelectedUsers;
            $_SESSION['multipersonsearch'][$this->name]['added'] = $addedUsers;
            $_SESSION['multipersonsearch'][$this->name]['removed'] = $removedUsers;
            // redirect to action which handles the form data
            $this->redirect($mp->getExecuteURL());
        }
        // default
        else {
            // get selected and selectable users from SESSION
            $this->defaultSelectableUsersIDs = $mp->getDefaultSelectableUsersIDs();
            $this->defaultSelectedUsersIDs = $mp->getDefaultSelectedUsersIDs();
            $this->selectableUsers = User::findMany($this->defaultSelectableUsersIDs);
            $this->selectedUsers = [];
        }

        // save selected/selectable users in hidden form fields
        $this->selectableUsers = new SimpleCollection($this->selectableUsers);
        $this->selectableUsers->orderBy("nachname asc, vorname asc");
        $this->selectableUsersHidden =  $this->selectableUsers->pluck('id');
        $this->selectedUsers = new SimpleCollection($this->selectedUsers);
        $this->selectedUsers->orderBy("nachname asc, vorname asc");
        $this->selectedUsersHidden =  $this->selectedUsers->pluck('id');
        $this->selectableUsers->orderBy('nachname, vorname');
        $this->selectedUsers->orderBy('nachname, vorname');

        // set layout data
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        $this->title = $mp->getTitle();
        $this->description = $mp->getDescription();
        $this->pageURL = $mp->getPageURL();

        if ($mp->getNavigationItem() != "") {
            Navigation::activateItem($mp->getNavigationItem());
        }

    }

}
