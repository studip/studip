<?php
namespace RESTAPI\Routes;

/**
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @condition course_id ^[a-f0-9]{32}$
 * @condition user_id ^[a-f0-9]{32}$
 */
class Course extends \RESTAPI\RouteMap
{

    public function before()
    {
        require_once 'User.php';
    }

    /**
     * Lists all courses of a user including the semesters in which
     * that course is active.
     * Optionally filtered by a URL parameter 'semester'.
     *
     * @get /user/:user_id/courses
     */
    public function getUserCourses($user_id)
    {
        if ($GLOBALS['user']->id !== $user_id) {
            $this->error(401);
        }

        // setting up semester to filter by
        $semester = null;
        $semester_id = \Request::get('semester');
        if ($semester_id) {
            $semester = \Semester::find($semester_id);
            if (!$semester) {
                $this->error(400, "Semester not found.");
            }
        }

        $memberships = $this->findMembershipsByUserId($user_id, $semester);

        $total = count($memberships);
        $memberships = $memberships->limit($this->offset, $this->limit);
        $memberships_json = $this->membershipsToJSON($memberships);
        $this->etag(md5(serialize($memberships_json)));
        return $this->paginated(
            $memberships_json,
            $total,
            compact('user_id'),
            ['semester' => $semester_id]
        );
    }

    /**
     * Show a single course
     *
     * @get /course/:course_id
     */
    public function getCourse($course_id)
    {
        if (!$course = \Course::find($course_id)) {
            $this->notFound("Course not found");
        }

        $course = $this->requireCourse($course_id);
        $this->lastmodified($course->chdate);
        $course_json = $this->courseToJSON($course);
        $this->etag(md5(serialize($course_json)));
        return $course_json;
    }

    /**
     * List all members of a course.
     * Optionally filtered by a URL parameter 'status'.
     *
     * @get /course/:course_id/members
     */
    public function getMembers($course_id)
    {
        $status_filter = \Request::get('status');
        if ($status_filter && !in_array($status_filter, words("user autor tutor dozent"))) {
            $this->error(400, "Status may be one of: user, autor, tutor, dozent");
        }

        $course = $this->requireCourse($course_id);
        $members = $course->members;
        if ($status_filter) {
            $members = $members->findBy('status', $status_filter);
        }

        $total = count($members);
        $members = $members->limit($this->offset, $this->limit);
        $members_json = $this->membersToJSON($course, $members);
        $this->etag(md5(serialize($members_json)));
        return $this->paginated(
            $members_json,
            $total,
            compact('course_id'),
            ['status' => $status_filter]
        );
    }

    /**
     * Get the root file folder of a course.
     *
     * @get /course/:course_id/top_folder
     */
    public function getTopFolder($course_id)
    {
        $top_folder = \Folder::findTopFolder(
            $this->requireCourse($course_id)->id,
            'course'
        );

        if (!$top_folder) {
            $this->notFound("No folder found for course with id {$course_id}!");
        }

        return (new FileSystem())->getFolder($top_folder->id);
    }

    /**************************************************/
    /* PRIVATE HELPER METHODS                         */
    /**************************************************/

    private function findMembershipsByUserId($user_id, $semester)
    {
        $memberships = \SimpleORMapCollection::createFromArray(
            \CourseMember::findBySQL('user_id = ? ORDER BY mkdate ASC', [$user_id])
        );

        // filter by semester
        if ($semester) {

            $memberships = $memberships->filter(function ($m) use ($semester) {
                    $course = $m->course;
                    return
                        $course->start_time == $semester->beginn
                        || ($course->start_time <= $semester->beginn
                            && ($course->duration_time == -1 || $semester->beginn <= $course->end_time));
                });
        }

        return $memberships;
    }

    private function membershipsToJSON($memberships)
    {
        $json = [];

        foreach ($memberships as $membership) {
            $course_json = $this->courseToJSON($course = $membership->course);

            $json[$this->urlf("/course/%s", [$course->id])] = $course_json;
        }
        return $json;
    }

    private function courseToJSON($course)
    {
        $json = [];

        $json['course_id']   = $course->id;
        $json['number']      = $course->VeranstaltungsNummer;
        $json['title']       = (string) $course->Name;
        $json['subtitle']    = (string) $course->Untertitel;
        $json['type']        = $course->status;
        $json['description'] = (string) $course->Beschreibung;
        $json['location']    = (string) $course->Ort;

        // lecturers
        foreach ($course->getMembersWithStatus('dozent') as $lecturer) {
            $url = $this->urlf('/user/%s', [htmlReady($lecturer->user_id)]);
            $json['lecturers'][$url] = User::getMiniUser($this, $lecturer->user);
        }

        // other members
        foreach (words("user autor tutor dozent") as $status) {
            $json['members'][$status] = $this->urlf('/course/%s/members?status=%s', [$course->id, $status]);
            $json['members'][$status . '_count'] = $course->countMembersWithStatus($status);
        }

        foreach (words("start_semester end_semester") as $key) {
            $json[$key] = $course->$key ? $this->urlf('/semester/%s', [htmlReady($course->$key->id)]) : null;
        }

        $modules = new \Modules();
        $activated = $modules->getLocalModules($course->id, 'sem');
        $json['modules'] = [];
        foreach (['forum'     => 'forum_categories',
                  'documents' => 'top_folder',
                  'wiki'      => 'wiki'] as $module => $uri)
        {

            if ($activated[$module]) {
                $json['modules'][$module] = $this->urlf('/course/%s/%s', [htmlReady($course->id), $uri]);
            }
        }

        // Add group if current user is member of the group
        $json['group'] = null;

        $member = \CourseMember::find([$course->id, $GLOBALS['user']->id]);
        if ($member) {
            $json['group'] = (int) $member->gruppe;
        }


        return $json;
    }

    private function requireCourse($id)
    {
        if (!$course = \Course::find($id)) {
            $this->notFound("Course not found");
        }

        if (!$GLOBALS['perm']->have_studip_perm('user', $id, $GLOBALS['user']->id)) {
            $this->error(401);
        }

        return $course;
    }

    private function membersToJSON($course, $members)
    {
        $json = [];

        foreach ($members as $member) {
            $url = $this->urlf('/user/%s', [$member->user_id]);
            $avatar = \Avatar::getAvatar($member->user_id);
            $json[$url] = [
                'member' => User::getMiniUser($this, $member->user),
                'status' => $member->status
            ];
        }
        return $json;
    }
}
