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

        $json = array();
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
        return $this->filterSemesters(
            \SemesterData::GetSemesterArray());
    }

    private function filterSemesters($semesters)
    {
        $result = array();

        foreach ($semesters as $semester) {
            if (isset($semester['semester_id'])) {
                $result[] = $semester;
            }
        }
        return $result;
    }

    private function semesterToJSON($semester)
    {
        return array(
            'id'             => $semester['semester_id'],
            'title'          => $semester['name'],
            'description'    => $semester['description'],
            'begin'          => intval($semester['beginn']),
            'end'            => intval($semester['ende']),
            'seminars_begin' => intval($semester['vorles_beginn']),
            'seminars_end'   => intval($semester['vorles_ende']),
        );
    }
}
