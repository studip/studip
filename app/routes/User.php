<?php
namespace RESTAPI\Routes;

/**
 * @author  André Klaßen <andre.klassen@elan-ev.de>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 * @condition user_id ^[0-9a-f]{1,32}$
 */
class User extends \RESTAPI\RouteMap
{
    /**************************************************/
    /* PUBLIC STATIC HELPER METHODS                   */
    /**************************************************/

    public static function getMiniUser($routemap, $user)
    {
        $avatar = \Avatar::getAvatar($user->id);

        return [
            'id'              => $user->id,
            'href'            => $routemap->urlf('/user/%s', [htmlReady($user->id)]),
            'name'            => self::getNamesOfUser($user),
            'avatar_small'    => $avatar->getURL(\Avatar::SMALL),
            'avatar_medium'   => $avatar->getURL(\Avatar::MEDIUM),
            'avatar_normal'   => $avatar->getURL(\Avatar::NORMAL),
            'avatar_original' => $avatar->getURL(\Avatar::ORIGINAL)
        ];
    }

    public static function getNamesOfUser($user)
    {
        $name = [
            'username'  => $user->username,
            'formatted' => $user->getFullName(),
            'family'    => $user->nachname,
            'given'     => $user->vorname,
            'prefix'    => $user->title_front,
            'suffix'    => $user->title_rear
        ];
        return $name;
    }


    /**************************************************/
    /* ROUTES                                         */
    /**************************************************/


    /**
     * getUser - retrieves data of a user
     *
     * @get /user/:user_id
     * @get /user
     */
    public function getUser($user_id = '')
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;

        $user = \User::findFull($user_id);
        if (!$user) {
            $this->halt(404, sprintf('User %s not found', $user_id));
        }

        $visibilities = get_local_visibility_by_id($user_id, 'homepage');
        if (is_array(json_decode($visibilities, true))) {
            $visibilities = json_decode($visibilities, true);
        } else {
            $visibilities = [];
        }

        $get_field = function ($field, $visibility) use ($user_id, $user, $visibilities) {
            if (!$user[$field]
                || !is_element_visible_for_user($GLOBALS['user']->id, $user_id, $visibilities[$visibility]))
            {
                return '';
            }
            return $user[$field];
        };

        $avatar = \Avatar::getAvatar($user_id);

        $user = [
            'user_id'         => $user_id,
            'username'        => $user['username'],
            'name'            => self::getNamesOfUser($user),
            'perms'           => $user['perms'],
            'email'           => get_visible_email($user_id),
            'avatar_small'    => $avatar->getURL(\Avatar::SMALL),
            'avatar_medium'   => $avatar->getURL(\Avatar::MEDIUM),
            'avatar_normal'   => $avatar->getURL(\Avatar::NORMAL),
            'avatar_original' => $avatar->getURL(\Avatar::ORIGINAL),
            'phone'           => $get_field('privatnr', 'private_phone'),
            'homepage'        => $get_field('Home', 'homepage'),
            'privadr'         => strip_tags($get_field('privadr', 'privadr')),
        ];

        // Data fields
        $datafields = [];
        foreach (\DataFieldEntry::getDataFieldEntries($user_id, 'user') as $entry) {
            if (!$entry->isVisible()) {
                continue;
            }
            if (!\Visibility::verify($entry->getID(), $user_id)) {
                continue;
            }
            $datafields[] = [
                'type'  => $entry->getType(),
                'id'    => $entry->getId(),
                'name'  => $entry->getName(),
                'value' => $entry->getValue(),
            ];
        }
        $user['datafields'] = $datafields;

        $this->etag(md5(serialize($user)));

        return $user;

    }


    /**
     * deleteUser - deletes a user
     *
     * @delete /user/:user_id
     */
    public function deleteUser($user_id)
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            $this->error(401);
        }

        if (!$GLOBALS['user']->id === $user_id) {
            $this->error(400, 'Must not delete yourself');
        }

        $user = \User::find($user_id);
        $user->delete();

        $this->status(204);
    }


    /**
     * returns institutes for a given user
     *
     * @get /user/:user_id/institutes
     */
    public function getInstitutes($user_id)
    {
        $user = \User::find($user_id);
        if (!$user) {
            $this->notFound(sprintf('User %s not found', $user_id));
        }

        $query = "SELECT i0.Institut_id AS institute_id, i0.Name AS name,
                         inst_perms AS perms, sprechzeiten AS consultation,
                         raum AS room, ui.telefon AS phone, ui.fax,
                         i0.Strasse AS street, i0.Plz AS city,
                         i1.Name AS faculty_name, i1.Strasse AS faculty_street,
                         i1.Plz AS faculty_city
                  FROM user_inst AS ui
                  JOIN Institute AS i0 USING (Institut_id)
                  LEFT JOIN Institute AS i1 ON (i0.fakultaets_id = i1.Institut_id)
                  WHERE visible = 1 AND user_id = :user_id
                  ORDER BY priority ASC";
        $statement = \DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();

        $institutes = [
            'work'  => [],
            'study' => [],
        ];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if ($row['perms'] === 'user') {
                $institutes['study'][] = $row;
            } else {
                $institutes['work'][] = $row;
            }
        }

        $this->etag(md5(serialize($institutes)));

        $result = array_slice($institutes, $this->offset, $this->limit);
        return $this->paginated(
            $result,
            count($institutes['study']) + count($institutes['work']),
            compact('user_id')
        );
    }


    /**
     * Get the root file folder of a user's file area.
     *
     * @get /user/:user_id/top_folder
     */
    public function getTopFolder($user_id)
    {
        $user = \User::find($user_id);
        if (!$user) {
            $this->notFound("User with id {$user_id} not found!");
        }

        if ($user->id !== \User::findCurrent()->id) {
            $this->error(403, 'You are not allowed to see another user\'s personal file area!');
        }

        $top_folder = \Folder::findTopFolder($user->id, 'user');

        if (!$top_folder) {
            $this->notFound("No folder found for user with id {$user_id}!");
        }

        return (new FileSystem())->getFolder($top_folder->id);
    }

    /**
     * Patches the course member data of a user and course. Pass data to be
     * patched via a valid json object in the body. Fields that my be patched:
     *
     * - group - the associated group in the overview of the users's courses
     * - visibility - visible state of the course
     *
     * @patch /user/:user_id/courses/:course_id
     *
     * @todo more patchable fields?
     */
    public function patchCourseGroup($user_id, $course_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            $this->notFound('User not found');
        }

        if ($user->id !== $GLOBALS['user']->id) {
            $this->halt(403, "You may not alter this user's data");
        }

        $member = \CourseMember::find([$course_id, $user->id]);
        if (!$member) {
            $this->notFound('You are not a member of the course');
        }

        if (isset($this->data['group'])) {
            if (!is_numeric($this->data['group']) || $this->data['group'] < 0 || $this->data['group'] > 8) {
                $this->halt(400, 'Given group is not inside the valid range 0..8');
            }
            $member->gruppe = $this->data['group'];
        }

        if (isset($this->data['visibility'])) {
            if (in_array($member->status, ['tutor', 'dozent'])) {
                $this->halt(400, 'You may not change the visibility status for this course since you are a teacher.');
            }
            if (!in_array($this->data['visibility'], ['yes', 'no'])) {
                $this->halt(400, 'Visibility may only be "yes" or "no".');
            }
            $member->visible = $this->data['visibility'];
        }

        if ($member->isDirty()) {
            $member->store();
        }

        $this->halt(204);
    }
}
