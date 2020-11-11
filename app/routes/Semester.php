<?php
namespace RESTAPI\Routes;

/**
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 *
 * @condition semester_id ^[0-9a-f]{1,32}$
 */
class Semester extends \RESTAPI\RouteMap
{
    public function __construct()
    {
        parent::__construct();
        if (!\Request::int('limit')) {
            $this->limit = count(\Semester::getAll());
        }
    }

    /**
     * Returns a list of all semesters.
     *
     * @get /semesters
     * @allow_nobody
     */
    public function getSemesters()
    {
        $semesters = \Semester::getAll();

        // paginate
        $total = count($semesters);
        $semesters = array_slice($semesters, $this->offset, $this->limit);

        $json = [];
        foreach ($semesters as $semester) {
            $url = $this->urlf('/semester/%s', $semester['semester_id']);
            $json[$url] = $this->semesterToJSON($semester);
        }

        return $this->paginated($json, $total);
    }

    /**
     * Returns a single semester.
     *
     * @get /semester/:semester_id
     */
    public function getSemester($id)
    {
        $semester = \Semester::find($id);
        if (!$semester) {
            $this->notFound();
        }

        $this->etag(md5(serialize($semester)));

        return $this->semesterToJSON($semester);
    }

    private function semesterToJSON($semester)
    {
        return [
            'id'             => $semester['semester_id'],
            'title'          => (string) $semester['name'],
            'token'          => (string) $semester['semester_token'],
            'description'    => (string) $semester['description'],
            'begin'          => (int) $semester['beginn'],
            'end'            => (int) $semester['ende'],
            'seminars_begin' => (int) $semester['vorles_beginn'],
            'seminars_end'   => (int) $semester['vorles_ende'],
            'visible'        => (int) $semester['visible'],
        ];
    }
}
