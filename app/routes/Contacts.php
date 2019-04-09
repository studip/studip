<?php
namespace RESTAPI\Routes;

/**
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @condition user_id ^[a-f0-9]{32}$
 * @condition friend_id ^[a-f0-9]{32}$
 * @condition group_id ^[a-f0-9]{32}$
 */
class Contacts extends \RESTAPI\RouteMap
{

    public static function before()
    {
        require_once 'User.php';
        require_once 'lib/statusgruppe.inc.php';
    }

    /**
     * Lists all contacts of a user
     *
     * @get /user/:user_id/contacts
     */
    public function getUserContacts($user_id)
    {
        if ($GLOBALS['user']->id !== $user_id) {
            $this->error(401);
        }

        // quite degenerated as long as we can only see our own contacts
        $user = $this->requireUser($user_id);

        $total = count($user->contacts);
        $contacts = $user->contacts->limit($this->offset, $this->limit);

        $contacts_json = $this->contactsToJSON($contacts);
        $this->etag(md5(serialize($contacts_json)));

        return $this->paginated($contacts_json,
                                $total, compact('user_id'));
    }

    /**
     * Adds/Updates a contact to user's list of contacts
     *
     * @put /user/:user_id/contacts/:friend_id
     */
    public function addUserContact($user_id, $buddy_user_id)
    {
        if ($GLOBALS['user']->id !== $user_id) {
            $this->error(401);
        }

        $user = $this->requireUser($user_id);
        $friend = $this->requireUser($buddy_user_id);

        // prevent duplicates
        if ($user->isFriendOf($friend)) {
            $this->error(409, sprintf('User "%s" is already a contact', htmlReady($friend->id)));
        }

        $user->contacts[] = $friend;
        $user->store();

        $this->status(201);
    }

    /**
     * Deletes a contact
     *
     * @delete /user/:user_id/contacts/:friend_id
     */
    public function removeUserContact($user_id, $buddy_user_id)
    {
        if ($GLOBALS['user']->id !== $user_id) {
            $this->error(401);
        }

        $user = $this->requireUser($user_id);
        $friend = $this->requireUser($buddy_user_id);

        if (!$user->isFriendOf($friend)) {
            $this->notFound("Contact not found");
        }

        $user->contacts->unsetByPK($friend->id);
        $user->store();

        $this->status(204);
    }


    /**
     * List all contact groups of a user
     *
     * @get /user/:user_id/contact_groups
     */
    public function getUserContactGroups($user_id)
    {
        if ($GLOBALS['user']->id !== $user_id) {
            $this->error(401);
        }

        $contact_groups = \SimpleCollection::createFromArray(
                \Statusgruppen::findByRange_id($GLOBALS['user']->id))
            ->orderBy('name ASC');

        $total = count($contact_groups);
        $contact_groups = $contact_groups->limit($this->offset, $this->limit);

        $contact_groups_json = $this->contactGroupsToJSON($contact_groups);
        $this->etag(md5(serialize($contact_groups_json)));

        return $this->paginated($contact_groups_json,
                                $total, compact('user_id'));
    }

    /**
     * Create a new contact group for a user.
     *
     * @post /user/:user_id/contact_groups
     */
    public function createContactGroup($user_id)
    {
        if ($GLOBALS['user']->id !== $user_id) {
            $this->error(401);
        }
        
        if (!isset($this->data['name']) || !mb_strlen($name = trim($this->data['name']))) {
            $this->error(400, 'Contact group name required.');
        }
        
        $group = new \Statusgruppen();
        $group->range_id       = $GLOBALS['user']->id;
        $group->name           = $name;
        $group->size           = 0;
        $group->selfassign     = 0;
        $group->calendar_group = 0;
        $group->store();
        $this->redirect('contact_group/' . $group->id, 201, 'ok');
    }

    /**
     * Show a single contact group
     *
     * @get /contact_group/:group_id
     */
    public function showContactGroup($group_id)
    {
        $group = $this->requireContactGroup($group_id);
        $contact_group_json = $this->contactGroupToJSON($group);
        $this->etag(md5(serialize($contact_group_json)));
        return $contact_group_json;
    }

    /**
     * Remove a contact group
     *
     * @delete /contact_group/:group_id
     */
    public function destroyContactGroup($group_id)
    {
        $group = $this->requireContactGroup($group_id);

        $group->remove();

        $this->status(204);
    }

    /**
     * List all members of a contact group
     *
     * @get /contact_group/:group_id/members
     */
    public function indexOfContactGroupMembers($group_id)
    {
        $group = $this->requireContactGroup($group_id);
        $contacts = $group->members->limit($this->offset, $this->limit);

        $json = [];
        foreach ($contacts as $contact) {
            $url = $this->urlf('/contact_group/%s/members/%s', [$group_id, $contact->user_id]);
            $json[$url] = User::getMiniUser($this, $contact->user);
        }

        $this->etag(md5(serialize($json)));

        return $this->paginated($json, count($group->members), compact('group_id'));
    }

    /**
     * Add a user to a contact group
     *
     * @put /contact_group/:group_id/members/:user_id
     */
    public function addToContactGroup($group_id, $user_id)
    {
        $group = $this->requireContactGroup($group_id);
        $user = $this->requireUser($user_id);

        // prevent duplicates
        $exists = $group->members->findBy('user_id', $user_id)->first();
        if ($exists) {
            $this->halt(204);
        }

        $new_contact = [
            'owner_id' => $GLOBALS['user']->id,
            'user_id'  => $user->id];

        $new_contact['group_assignments'][] = ['statusgruppe_id' => $group->id,
                                                    'user_id'         => $user->id];

        $success = (bool)\Contact::import($new_contact)->store();


        if (!$success) {
            $this->error(500);
        }

        $this->status(201);
    }

    /**
     * Remove a user from a contact group
     *
     * @delete /contact_group/:group_id/members/:user_id
     */
    public function removeFromContactGroup($group_id, $user_id)
    {
        $group = $this->requireContactGroup($group_id);
        $membership = $group->members->findBy('user_id', $user_id)->first();
        if (!$membership) {
            $this->notFound();
        }

        $membership->delete();

        $this->status(204);
    }


    /**************************************************/
    /* PRIVATE HELPER METHODS                         */
    /**************************************************/

    private function requireUser($user_id)
    {
        $user = \User::find($user_id);
        // TODO: checks visibility using the global perm object!
        if (!$user || !get_visibility_by_id($user_id)) {
            $this->notFound(sprintf("Could not find user with id: %s", htmlReady($user_id)));
        }

        return $user;
    }

    private function requireContactGroup($group_id)
    {
        $group = \Statusgruppen::find($group_id);
        if (!$group) {
            $this->notFound();
        }

        if ($group->range_id !== $GLOBALS['user']->id) {
            $this->error(401);
        }
        return $group;
    }

    private function contactsToJSON($contacts) {
        $result = [];
        foreach ($contacts as $contact) {
            $result[] = User::getMiniUser($this, $contact);
        }
        return $result;
    }

    private function contactGroupsToJSON($contact_groups)
    {
        $result = [];
        foreach ($contact_groups as $cg) {
            $url = $this->urlf('/contact_group/%s', [htmlReady($cg->id)]);
            $result[$url] = $this->contactGroupToJSON($cg);
        }
        return $result;
    }

    private function contactGroupToJSON($group)
    {
        $json = [
            'id'             => $group->id,
            'name'           => $group->name,
            'contacts'       => $this->urlf('/contact_group/%s/members', [htmlReady($group->id)]),
            'contacts_count' => sizeof($group->members)
        ];
        return $json;
    }
}
