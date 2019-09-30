<?php
/**
 * MultiPersonSearch.class.php
 *
 * This class provides a GUI-element for searching, adding and removing
 * multiple persons. If JavaScript is enabled the GUI-element is shown
 * as a dialog on the current page. Otherwise the GUI-element is shown
 * on a separate page.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * he License, or (at your option) any later version.
 *
 * @author      Sebastian Hobert <sebastian.hobert@uni-goettingen.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @link        http://docs.studip.de/develop/Entwickler/MultiPersonSearch
 */
class MultiPersonSearch {

    private $name;
    private $linkIconPath = "";
    private $linkText = "";
    private $title = "";
    private $description = "";
    private $executeURL;
    private $jsFunction = null;
    private $quickfilterIds = [];
    private $defaultSelectableUsersIDs = [];
    private $defaultSelectedUsersIDs = [];
    private $searchObject = null;
    private $additionalHMTL = "";
    private $navigationItem = "";
    private $dataDialogStatus = false;

    /**
     * restores a MultiPersonSearch object.
     *
     * @param string name of the object
     *
     * @return MultiPersonSearch
     */
    public static function load($name)
    {
        $mp = new MultiPersonSearch($name);
        $mp->restoreFromSession();
        return $mp;
    }

    /**
     * returns a MultiPersonSearch object.
     *
     * @param string name of the object
     *
     * @return MultiPersonSearch
     */
    public static function get($name)
    {
        $mp = new MultiPersonSearch($name);
        return $mp;
    }

    /**
     * contsructs a new MultiPersonSearch object.
     *
     * @param string name of the object and html ids
     */
    public function __construct($name)
    {
       $this->name = $name;
       $_SESSION['multipersonsearch'][$this->name]['lastUse'] = time();
       $this->collectGarbage();
       $this->setDefaultValues();

    }

    /**
     * returns the newly added persons. The array will contain all
     * persons which are selected (on the right side of the dialog) but
     * without the defaultSelectedUsers.
     *
     * @return array containing all new persons
     */
    public function getAddedUsers() {
        return $_SESSION['multipersonsearch'][$this->name]['added'] ? : [];
    }

    /**
     * saves the added persons to $_SESSION.
     */
    public function saveAddedUsersToSession() {
        $addedUsers = [];
        foreach (Request::optionArray($this->name . '_selectbox') as $selected) {
            if (!in_array($selected, $_SESSION['multipersonsearch'][$this->name]['defaultSelectedUsersIDs'])) {
                $addedUsers[] = $selected;
            }
        }
        $_SESSION['multipersonsearch'][$this->name]['added'] = $addedUsers;
        $_SESSION['multipersonsearch'][$this->name]['additional'] = Request::optionArray('additional');
    }

    /**
     * returns the removed persons. The array will contain all
     * persons which were selected by default (on the right side of the
     * dialog) and then removed by the user.
     *
     * @return array containing all removed persons
     */
    public function getRemovedUsers() {
        return $_SESSION['multipersonsearch'][$this->name]['removed'];
    }

    /**
     * saves the removed persons to $_SESSION.
     */
    public function saveRemovedUsersToSession() {
        $removedUsers = [];
        foreach ($this->defaultSelectedUsersIDs as $default) {
            if (!in_array($default, Request::optionArray($this->name . '_selectbox'))) {
                $removedUsers[] = $default;
            }
        }
        $_SESSION['multipersonsearch'][$this->name]['removed'] =  $removedUsers;
    }

    /**
     * renders a link to open the multipersonsearch dialog.
     */
    public function render() {
        $template = $GLOBALS['template_factory']->open('multipersonsearch/link.php');

        $template->set_attribute('linkIconPath', $this->linkIconPath);
        $template->set_attribute('linkText', $this->linkText);
        $template->set_attribute('title', $this->title);
        $template->set_attribute('name', $this->name);
        $template->set_attribute('description', $this->description);
        $template->set_attribute('executeURL', $this->executeURL);
        $template->set_attribute('jsFunction', $this->jsFunction);
        $this->storeToSession();
        return $template->render();
    }

    /**
     * sets the icon of the link to open the dialog. To hide the icon an
     * empty string can be set.
     *
     * @param string path ot the icon
     *
     * @return MultiPersonSearch
     */
    public function setLinkIconPath($path) {
        $this->linkIconPath = $path;

        return $this;
    }

    /**
     * returns the icon of the link to open the dialog.
     *
     * @return string path ot the icon.
     */
    public function getLinkIconPath() {
        return $this->linkIconPath;
    }

    /**
     * sets the link text of the link to open the dialog. To hide the
     * text an empty string can be set.
     *
     * @param string text of the link
     *
     * @return MultiPersonSearch
     *
     */
    public function setLinkText($text = "") {
        $this->linkText = $text;

        return $this;
    }

    /**
     * returns the link text of the link.
     *
     * @return string text of the link.
     */
    public function getLinkText() {
        return $this->linkText;
    }

    /**
     * sets the action which will handle the added and removed persons after saving the dialog.
     *
     * @param string action
     *
     * @return MultiPersonSearch
     */
    public function setExecuteURL($action) {
        $this->executeURL = $action;

        return $this;
    }

    /**
     * returns the action which will handle the added and removed persons after saving the dialog.
     *
     * @return string action which will handle the form data.
     */
    public function getExecuteURL() {
        return $this->executeURL;
    }

    /**
     * sets a JavaScript-function to be fired when the user has pressed the submit-button.
     * Arguments are:
     * function fireme(id_of_item, text_of_item)
     * example setting: MPS->setJSFunctionOnSubmit('fireme');
     *
     * @param string $function_name the name of the javascript function
     *
     * @return MultiPersonSearch
     */
    public function setJSFunctionOnSubmit($function_name)
    {
        $this->jsFunction = $function_name;
        return $this;
    }

    /**
     * returns a JavaScript-function which should be fired when the user has pressed the submit button.
     *
     * @return string function name
     */
    public function getJSFunctionOnSubmit()
    {
        return $this->jsFunction;
    }

    /**
     * sets the search object.
     *
     * @param SearchType object of type SearchType (e.g. SQLSearch.class.php)
     *
     * @return MultiPersonSearch
     */
    public function setSearchObject($searchType) {
        $this->searchObject = $searchType;

        return $this;
    }

    /**
     * returns the search object.
     *
     * @return SearchType
     */
    public function getSearchObject() {
        return $this->searchObject;
    }

    /**
     * sets html code which will be shown inside the form element.
     *
     * @param string html code
     *
     * @return MultiPersonSearch
     */
    public function setAdditionalHTML($html) {
        $this->additionalHMTL = $html;

        return $this;
    }


    /**
     * enables or disabled data-dialog
     * @param boolean $status
     * @return $this
     */
    public function setDataDialogStatus($status) {
        $this->dataDialogStatus = $status;

        return $this;
    }

    /**
     * returns if data-dialog is enabled or disabled
     * @return bool
     */
    public function getDataDialogStatus() {
        return $this->dataDialogStatus;
    }
    /**
     * returns html code which will be shown inside the form element.
     *
     * @return string html code
     */
    public function getAdditionHTML() {
        return $this->additionalHMTL;
    }

    /**
     * returns an additional option array.
     *
     * @return string html code
     */
    public function getAdditionalOptionArray() {
        return $_SESSION['multipersonsearch'][$this->name]['additional'];
    }

    /**
     * sets the persons which will be shown as selectable by default on
     * the left side of the dialoag.
     *
     * @param array array containing user-ids
     */
    public function setDefaultSelectableUser($userArray) {
        $userArray = array_unique($userArray);
        $this->defaultSelectableUsersIDs = [];
        if (is_array($userArray)) {
            foreach ($userArray as $userId) {
                $this->defaultSelectableUsersIDs[] = $userId;
            }
        }
        return $this;
    }
    /**
     * returns the ids of defaultselectable users.
     *
     * @return array
     */
    public function getDefaultSelectableUsersIDs() {
        return $this->defaultSelectableUsersIDs;
    }

    /**
     * sets the persons which will be shown as selected by default on
     * the right side of the dialoag.
     *
     * @param array array containing user-ids
     */
    public function setDefaultSelectedUser($userArray) {
        $userArray = array_unique($userArray);
        $this->defaultSelectedUsersIDs = [];
        if (is_array($userArray)) {
            foreach ($userArray as $userId) {
                $this->defaultSelectedUsersIDs[]  = $userId;
            }
        }
        return $this;
    }

    /**
     * returns the ids of defaultselected users.
     *
     * @return array
     */
    public function getDefaultSelectedUsersIDs() {
        return $this->defaultSelectedUsersIDs;
    }


    /**
     * sets the title of the dialog.
     *
     * @param string  $title title of the dialog
     *
     * @return MultiPersonSearch
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * returns the title.
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }


    /**
     * sets the description of the dialog.
     *
     * @param string  $desc description of the dialog
     *
     * @return MultiPersonSearch
     */
    public function setDescription($desc) {
        $this->description = $desc;
        return $this;
    }

    /**
     * returns the description.
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }


    /**
     * returns the url of the page where the GUI element is added.
     *
     * @return string
     */
    public function getPageUrl() {
        return $this->pageURL;
    }

    /**
     * adds a new quickfilter.
     *
     * @param string $title title of the new quickfilter
     * @param array $userArray containing all user-ids belonging to the quickfilter
     *
     * @return MultiPersonSearch
     */
    public function addQuickfilter($title, $userArray) {
        $users = [];
        $usersIds = [];
        if (is_array($userArray)) {
            foreach ($userArray as $userId) {
                $usersIds[]  = $userId;
            }
        }
        $this->quickfilterIds[(string) $title] = $usersIds;

        return $this;
    }

    /**
     * returns the ids of quickfilters.
     *
     * @return array
     */
    public function getQuickfilterIds()
    {
        return $this->quickfilterIds ?: [];
    }

    /**
     * clears all quickfilters.
     *
     * @return MultiPersonSearch
     */
    public function clearQuickfilters() {
        $this->quickfilterIds = [];

        return $this;
    }

    /**
     * sets the navigation item.
     *
     * @param string  $navigationItem navigation item
     *
     * @return MultiPersonSearch
     */
    public function setNavigationItem($navigationItem) {
        $this->navigationItem = $navigationItem;

        return $this;
    }

    /**
     * returns the navigation item.
     *
     * @return string
     */
    public function getNavigationItem() {
        return $this->navigationItem;
    }

    /**
     * stores the internal data to a session.
     */
    public function storeToSession() {
        $_SESSION['multipersonsearch'][$this->name]['title'] = $this->title;
        $_SESSION['multipersonsearch'][$this->name]['description'] = $this->description;
        $_SESSION['multipersonsearch'][$this->name]['additionalHMTL'] = $this->additionalHMTL;
        $_SESSION['multipersonsearch'][$this->name]['executeURL'] = $this->executeURL;
        $_SESSION['multipersonsearch'][$this->name]['jsFunction'] = $this->jsFunction;
        $_SESSION['multipersonsearch'][$this->name]['pageURL'] = Request::url();
        $_SESSION['multipersonsearch'][$this->name]['defaultSelectableUsersIDs'] = $this->defaultSelectableUsersIDs;
        $_SESSION['multipersonsearch'][$this->name]['defaultSelectedUsersIDs'] = $this->defaultSelectedUsersIDs;
        $_SESSION['multipersonsearch'][$this->name]['quickfilterIds'] = $this->quickfilterIds;
        $_SESSION['multipersonsearch'][$this->name]['searchObject'] = serialize($this->searchObject);
        $_SESSION['multipersonsearch'][$this->name]['navigationItem'] = $this->navigationItem;
        $_SESSION['multipersonsearch'][$this->name]['dataDialogStatus'] = $this->dataDialogStatus;
    }

    /**
     * restores the internal data from a session.
     */
    public function restoreFromSession() {
        $this->title = $_SESSION['multipersonsearch'][$this->name]['title'];
        $this->description = $_SESSION['multipersonsearch'][$this->name]['description'];
        $this->quickfilterIds = $_SESSION['multipersonsearch'][$this->name]['quickfilterIds'];
        $this->additionalHMTL = $_SESSION['multipersonsearch'][$this->name]['additionalHMTL'];
        $this->executeURL = html_entity_decode($_SESSION['multipersonsearch'][$this->name]['executeURL']);
        $this->jsFunction = $_SESSION['multipersonsearch'][$this->name]['jsFunction'];
        $this->pageURL = $_SESSION['multipersonsearch'][$this->name]['pageURL'];
        $this->defaultSelectableUsersIDs = $_SESSION['multipersonsearch'][$this->name]['defaultSelectableUsersIDs'];
        $this->defaultSelectedUsersIDs = $_SESSION['multipersonsearch'][$this->name]['defaultSelectedUsersIDs'];
        $this->searchObject = unserialize($_SESSION['multipersonsearch'][$this->name]['searchObject']);
        $this->navigationItem = $_SESSION['multipersonsearch'][$this->name]['navigationItem'];
        $this->dataDialogStatus = $_SESSION['multipersonsearch'][$this->name]['dataDialogStatus'];
    }

    /**
     * clears the session data.
     */
    public function clearSession() {
        unset($_SESSION['multipersonsearch'][$this->name]);
    }

    /**
     * sets default values of the internal variables.
     */
    private function setDefaultValues() {
        $this->title = _('Personen hinzufügen');
        $this->description = _('Bitte wählen Sie aus, wen Sie hinzufügen möchten.');
        $this->linkIconPath = Icon::create("community+add", "clickable", ['title' => _('Personen hinzufügen')]);
    }

    /**
     * clear unused sessions.
     */
    private function collectGarbage() {
        $maxLifeTime = 30; // minutes
        foreach ($_SESSION['multipersonsearch'] as $key=>$value) {
            if (time() - $value['lastUse'] > $maxLifeTime * 60) {
                unset($_SESSION['multipersonsearch'][$key]);
            }
        }
    }

}
