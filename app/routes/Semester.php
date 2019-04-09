<?php
namespace RESTAPI\Routes;

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @condition semester_id ^[0-9a-f]{32}$
 */
class Semester extends \RESTAPI\RouteMap
{
    /**
     * Returns a list of all semesters.
     *
     * @get /semesters
     */
    public function getSemesters()
    {
        $semesters = $this->findAllSemesters();

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
        $semester = \SemesterData::getSemesterData($id);
        if (!$semester) {
            $this->notFound();
        }

        $this->etag(md5(serialize($semester)));

        return $this->semesterToJSON($semester);
    }

    private function findAllSemesters()
    {
        return $this->filterSemesters(\SemesterData::GetSemesterArray());
    }

    private function filterSemesters($semesters)
    {
        return array_filter($semesters, function ($semester) {
            return isset($semester['semester_id']);
        });
    }

    private function semesterToJSON($semester)
    {
        return [
            'id'             => $semester['semester_id'],
            'title'          => (string) $semester['name'],
            'token'          => (string) $semester['token'],
            'description'    => (string) $semester['description'],
            'begin'          => (int) $semester['beginn'],
            'end'            => (int) $semester['ende'],
            'seminars_begin' => (int) $semester['vorles_beginn'],
            'seminars_end'   => (int) $semester['vorles_ende'],
        ];
    }
}
