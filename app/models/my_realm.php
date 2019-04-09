<?php
/**
 * my_courses.php - Model for user and seminar related
 * pages under "Meine Veranstaltungen"
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @author      David Siegfried <david@ds-labs.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @since       3.1
 */

require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';

class MyRealmModel
{
    /**
     * Checks for changes in folders for a course.
     * @param mixed $my_obj
     * @param string $user_id
     * @param string $object_id
     */
    public static function checkDocuments(&$my_obj, $user_id, $object_id)
    {
        if ($my_obj["modules"]["documents"]) {
            $db = DBManager::get();

            if (!$GLOBALS['perm']->have_studip_perm('tutor', $object_id, $user_id)) {
                $readable_folders = array_keys(
                    FileManager::getReadableFolders(
                        Folder::findTopFolder($object_id)->getTypedFolder(), $user_id)
                );

                if (empty($readable_folders)) {
                    return null;
                }
            }

            $query = "SELECT COUNT(fr.id) as count,
                    COUNT(IF((fr.chdate > IFNULL(ouv.visitdate, :threshold)
                    AND fr.user_id != :user_id), fr.id, NULL)) AS neue,
                    MAX(IF((fr.chdate > IFNULL(ouv.visitdate, :threshold)
                    AND fr.user_id != :user_id), fr.chdate, 0)) AS last_modified
                FROM folders a
                INNER JOIN file_refs fr ON (fr.folder_id=a.id)
                LEFT JOIN object_user_visits ouv ON (ouv.object_id = a.range_id AND ouv.user_id = :user_id AND ouv.type ='documents')
                WHERE a.range_id = :object_id " . ($readable_folders ? "AND a.id IN (:readable_folders)" : "");

            $result = $db->fetchOne($query, [
                ':user_id'            => $user_id,
                ':threshold'          => object_get_visit_threshold(),
                ':object_id'          => $object_id,
                ':readable_folders' => $readable_folders
            ]);

            if (!empty($result)) {
                if (!is_null($result['last_modified']) && (int)$result['last_modified'] != 0) {
                    if ($my_obj['last_modified'] < $result['last_modified']) {
                        $my_obj['last_modified'] = $result['last_modified'];
                    }
                }


                $navigation = new Navigation('files');
                if ($result['neue'] > 0) {
                    $navigation->setURL(
                        URLHelper::getURL(
                            'dispatch.php/' . ($my_obj["obj_type"] == 'sem' ? 'course' : 'institute') . '/files/flat',
                            [
                                'select' => 'new'
                            ]
                        )
                    );

                    $navigation->setImage(
                        Icon::create(
                            'files+new',
                            'attention',
                            [
                                'title' => sprintf(
                                    _('%s Datei(en), %s neue'),
                                    $result['count'],
                                    $result['neue']
                                )
                            ]
                        )
                    );

                    $navigation->setBadgeNumber($result['neue']);
                } elseif ($result['count']) {
                    $navigation->setURL(
                        URLHelper::getURL(
                            'dispatch.php/' . ($my_obj["obj_type"] == 'sem' ? 'course' : 'institute') . '/files/index'
                        )
                    );
                    $navigation->setImage(
                        Icon::create(
                            'files',
                            'inactive',
                            [
                                'title' => sprintf(
                                    _('%s Datei(en)'),
                                    $result['count']
                                )
                            ]
                        )
                    );
                }
                return $navigation;
            }
        }

        return null;
    }


    /**
     * @param      $my_obj
     * @param      $user_id
     * @param null $modules
     */
    public static function checkLiterature(&$my_obj, $user_id, $object_id)
    {
        if ($my_obj["modules"]["literature"]) {
            $sql       = "SELECT a.range_id, COUNT(list_id) as count,
                COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.user_id !=:user_id), list_id, NULL)) AS neue,
                MAX(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.user_id != :user_id), chdate, 0)) AS last_modified
                FROM
                lit_list a
                LEFT JOIN object_user_visits b ON (b.object_id = a.range_id AND b.user_id = :user_id AND b.type ='literature')
                WHERE a.range_id = :course_id  AND a. visibility = 1
                GROUP BY a.range_id";
            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $object_id);
            $statement->bindValue(':threshold', object_get_visit_threshold());
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                if (!is_null($result['last_modified']) && (int)$result['last_modified'] != 0) {
                    if ($my_obj['last_modified'] < $result['last_modified']) {
                        $my_obj['last_modified'] = $result['last_modified'];
                    }
                }
                $nav = new Navigation('literature', 'dispatch.php/course/literature');
                if ((int)$result['neue']) {
                    $nav->setImage(
                        Icon::create(
                            'literature+new',
                            'attention',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%1$d Literaturliste, %2$d neue',
                                        '%1$d Literaturlisten, %2$d neue',
                                        $result['count']
                                    ),
                                    $result['count'],
                                    $result['neue']
                                )
                            ]
                        )
                    );
                    $nav->setBadgeNumber($result['neue']);
                } elseif ((int)$result['count']) {
                    $nav->setImage(
                        Icon::create(
                            'literature',
                            'inactive',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%d Literaturliste',
                                        '%d Literaturlisten',
                                        $result['count']
                                    ),
                                $result['count']
                                )
                            ]
                        )
                    );
                }
                return $nav;
            }
        }
        return null;
    }


    /**
     * Check for new news
     * @param      $my_obj
     * @param      $user_id
     * @param null $modules
     */
    public static function checkOverview(&$my_obj, $user_id, $object_id)
    {
        $sql = "SELECT
            COUNT(nw.news_id) as count,
            MAX(IF ((chdate > IFNULL(b.visitdate, :threshold) AND nw.user_id !=:user_id), chdate, 0)) AS last_modified,
            COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND nw.user_id !=:user_id), nw.news_id, NULL)) AS neue
            FROM news_range a
            LEFT JOIN news nw ON(a . news_id = nw . news_id AND UNIX_TIMESTAMP() BETWEEN date AND (date + expire))
            LEFT JOIN object_user_visits b ON(b . object_id = a . news_id AND b . user_id = :user_id AND b . type = 'news')
            WHERE a . range_id = :course_id
            GROUP BY a.range_id";

        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $object_id);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            if ($my_obj['last_modified'] < $result['last_modified']) {
                $my_obj['last_modified'] = $result['last_modified'];
            }
            $nav = new Navigation('news', '');
            if ($result['neue']) {
                $nav->setURL('?new_news=true');
                $nav->setImage(
                    Icon::create(
                        'news+new',
                        'attention',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%1$d Ankündigung, %2$d neue',
                                    '%1$d Ankündigungen, %2$d neue',
                                    $result['count']
                                ),
                                $result['count'],
                                $result['neue']
                            )
                        ]
                    )
                );
                $nav->setBadgeNumber($result['neue']);
            } elseif ($result['count']) {
                $nav->setImage(
                    Icon::create(
                        'news',
                        'inactive',
                        [
                            'title' => sprintf(
                                ngettext(
                                    '%d Ankündigung',
                                    '%d Ankündigungen',
                                    $result['count']
                                ),
                                $result['count']
                            )
                        ]
                    )
                );
            }
            return $nav;
        }
        return null;
    }


    /**
     * Check SCM for news
     * @param      $my_obj
     * @param      $user_id
     * @param null $modules
     */
    public static function checkScm(&$my_obj, $user_id, $object_id)
    {
        if ($my_obj["modules"]["scm"]) {
            $sql = "SELECT tab_name,  ouv.object_id,
                  COUNT(IF(content !='',1,0)) as count,
                  COUNT(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND scm.user_id !=:user_id), IF(content !='',1,0), NULL)) AS neue,
                  MAX(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND scm.user_id !=:user_id), chdate, 0)) AS last_modified
                FROM
                  scm
                LEFT JOIN
                  object_user_visits ouv ON(ouv.object_id = scm.range_id AND ouv.user_id = :user_id and ouv . type = 'scm')
                WHERE
                  scm.range_id = :course_id
                GROUP BY
                  scm.range_id";

            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $object_id);
            $statement->bindValue(':threshold', object_get_visit_threshold());
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);


            if (!empty($result)) {
                if ($my_obj['last_modified'] < $result['last_modified']) {
                    $my_obj['last_modified'] = $result['last_modified'];
                }

                $nav = new Navigation('scm', 'dispatch.php/course/scm');

                if ($result['count']) {
                    if ($result['neue']) {
                        $image = Icon::create('infopage+new', 'new');
                        $nav->setBadgeNumber($result['neue']);
                        if ($result['count'] == 1) {
                            $title = $result['tab_name'] . _(' (geändert)');
                        } else {
                            $title = sprintf(
                                _('%1$d Einträge insgesamt, %2$d neue'),
                                $result['count'],
                                $result['neue']
                            );
                        }
                    } else {
                        $image = Icon::create('infopage', 'inactive');
                        if ($result['count'] == 1) {
                            $title = $result['tab_name'] . _(' (geändert)');
                        } else {
                            $title = sprintf(
                                ngettext(
                                    '%d Eintrag',
                                    '%d Einträge',
                                    $result['count']
                                ),
                                $result['count']
                            );
                        }
                    }
                    $nav->setImage($image, ['title' => $title]);
                }

                return $nav;
            }
        }
        return null;
    }

    /**
     * Check for new dates
     * @param      $my_obj
     * @param      $user_id
     * @param null $modules
     */
    public static function checkSchedule(&$my_obj, $user_id, $object_id)
    {

        if ($my_obj["modules"]["schedule"]) {
            $count = 0;
            $neue  = 0;
            // check for extern dates
            $sql       = "SELECT  COUNT(term.termin_id) as count,
                  MAX(IF ((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id != :user_id), term.chdate, 0)) AS last_modified,
                  COUNT(IF((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id !=:user_id), term.termin_id, NULL)) AS neue
                FROM
                  ex_termine term
                LEFT JOIN
                  object_user_visits ouv ON(ouv . object_id = term . range_id AND ouv . user_id = :user_id AND ouv . type = 'schedule')
                WHERE term . range_id = :course_id
                GROUP BY term.range_id";
            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $object_id);
            $statement->bindValue(':threshold', object_get_visit_threshold());
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (!empty($result)) {
                $count = $result['count'];
                $neue  = $result['neue'];
                if ($my_obj['last_modified'] < $result['last_modified'] && (int)$result['last_modified'] != 0) {
                    $my_obj['last_modified'] = $result['last_modified'];
                }
            }


            // check for normal dates
            $sql = "SELECT  COUNT(term.termin_id) as count,
                  COUNT(term.termin_id) as count, COUNT(IF((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id !=:user_id), term.termin_id, NULL)) AS neue,
                  MAX(IF ((term.chdate > IFNULL(ouv.visitdate, :threshold) AND term.autor_id != :user_id), term . chdate, 0)) AS last_modified
                FROM
                  termine term
                LEFT JOIN
                  object_user_visits ouv ON(ouv . object_id = term . range_id AND ouv . user_id = :user_id AND ouv . type = 'schedule')
                WHERE term . range_id = :course_id
                GROUP BY term.range_id";

            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $object_id);
            $statement->bindValue(':threshold', object_get_visit_threshold());
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (!empty($result)) {
                $count += $result['count'];
                $neue += $result['neue'];
                if ($my_obj['last_modified'] < $result['last_modified'] && (int)$result['last_modified'] != 0) {
                    $my_obj['last_modified'] = $result['last_modified'];
                }
            }

            if ($neue || (int)$count > 0) {
                $nav = new Navigation('schedule', 'dispatch.php/course/dates');
                if ($neue) {
                    $nav->setImage(
                        Icon::create(
                            'schedule+new',
                            'attention',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%1$d Termin, %2$d neuer',
                                        '%1$d Termine, %2$d neue',
                                        $count
                                    ),
                                    $count,
                                    $neue
                                )
                            ]
                        )
                    );
                    $nav->setBadgeNumber($neue);
                } elseif ($count) {
                    $nav->setImage(
                        Icon::create(
                            'schedule',
                            'inactive',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%d Termin',
                                        '%d Termine',
                                        $count
                                    ),
                                    $count
                                )
                            ]
                        )
                    );
                }
                return $nav;
            }
        }
        return null;
    }

    /**
     * Check for new entries in wiki
     * @param $my_obj
     * @param $user_id
     * @param $modules
     */
    public static function checkWiki(&$my_obj, $user_id, $object_id)
    {
        $priviledged = $GLOBALS['perm']->have_studip_perm('tutor', $object_id, $user_id);

        if ($my_obj['modules']['wiki']) {
            if ($priviledged) {
                $sql = "SELECT COUNT(DISTINCT keyword) AS count_d,
                               COUNT(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND wiki.user_id != :user_id), keyword, NULL)) AS neue,
                               MAX(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND wiki.user_id !=:user_id), chdate, 0)) AS last_modified,
                               COUNT(keyword) AS count
                        FROM wiki
                        LEFT JOIN object_user_visits AS ouv ON (ouv.object_id = wiki.range_id AND ouv.user_id = :user_id and ouv.type = 'wiki')
                        WHERE wiki.range_id = :course_id
                        GROUP BY wiki.range_id";
            } else {
                $sql = "SELECT COUNT(DISTINCT keyword) AS count_d,
                               COUNT(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND wiki.user_id != :user_id), keyword, NULL)) AS neue,
                               MAX(IF((chdate > IFNULL(ouv.visitdate, :threshold) AND wiki.user_id !=:user_id), chdate, 0)) AS last_modified,
                               COUNT(keyword) AS count
                        FROM wiki
                        LEFT JOIN wiki_page_config USING (range_id, keyword)
                        LEFT JOIN object_user_visits AS ouv ON (ouv.object_id = wiki.range_id AND ouv.user_id = :user_id and ouv.type = 'wiki')
                        WHERE wiki.range_id = :course_id
                          AND (
                              wiki_page_config.range_id IS NULL
                              OR wiki_page_config.read_restricted = 0
                          )
                        GROUP BY wiki.range_id";
            }
            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $object_id);
            $statement->bindValue(':threshold', object_get_visit_threshold());
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (!empty($result)) {
                if ($result['last_modified'] && $my_obj['last_modified'] < $result['last_modified']) {
                    $my_obj['last_modified'] = $result['last_modified'];
                }
                $nav = new Navigation('wiki');
                if ($result['neue']) {
                    $nav->setURL('wiki.php?view=listnew');
                    $nav->setImage(Icon::create('wiki+new', Icon::ROLE_ATTENTION, [
                        'title' => sprintf(
                            ngettext(
                                '%1$d Wiki-Seite, %2$d Änderung(en)',
                                '%1$d Wiki-Seiten, %2$d Änderung(en)',
                                $result['count_d']
                            ),
                            $result['count_d'],
                            $result['neue']
                        )
                    ]));
                    $nav->setBadgeNumber($result['neue']);
                } elseif ($result['count']) {
                    $nav->setURL('wiki.php');
                    $nav->setImage(Icon::create('wiki', Icon::ROLE_INACTIVE, [
                        'title' => sprintf(
                            ngettext(
                                '%d Wiki-Seite',
                                '%d Wiki-Seiten',
                                $result['count_d']
                            ),
                            $result['count_d']
                        )
                    ]));
                }
                return $nav;
            }
        }

        return null;
    }

    /**
     * @param      $my_obj
     * @param      $user_id
     * @param null $modules
     */
    public static function checkElearning_interface(&$my_obj, $user_id, $object_id)
    {
        if ($my_obj["modules"]["elearning_interface"]) {
            $sql = "SELECT a.object_id, COUNT(module_id) as count,
                COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.module_type != 'crs'), module_id, NULL)) AS neue,
                MAX(IF((chdate > IFNULL(b.visitdate, :threshold) AND a.module_type != 'crs'), chdate, 0)) AS last_modified
                FROM
                object_contentmodules a
                LEFT JOIN object_user_visits b ON (b.object_id = a.object_id AND b.user_id = :user_id AND b.type ='elearning_interface')
                WHERE a.object_id = :course_id  AND a.module_type != 'crs'
                GROUP BY a.object_id";

            $statement = DBManager::get()->prepare($sql);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':course_id', $object_id);
            $statement->bindValue(':threshold', object_get_visit_threshold());
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                if (!is_null($result['last_modified']) && (int)$result['last_modified'] != 0) {
                    if ($my_obj['last_modified'] < $result['last_modified']) {
                        $my_obj['last_modified'] = $result['last_modified'];
                    }
                }
                $nav = new Navigation('elearning', 'dispatch.php/course/elearning/show');
                if ((int)$result['neue']) {
                    $nav->setImage(
                            Icon::create(
                                    'learnmodule+new',
                                    'attention',
                                    [
                                                    'title' => sprintf(
                                                            ngettext(
                                                                    '%1$d Lernmodul, %2$d neues',
                                                                    '%1$d Lernmodule, %2$d neue',
                                                                    $result['count']
                                                                    ),
                                                            $result['count'],
                                                            $result['neue']
                                                            )
                                    ]
                                    )
                            );
                } elseif ((int)$result['count']) {
                    $nav->setImage(
                            Icon::create(
                                    'learnmodule',
                                    'inactive',
                                    [
                                                    'title' => sprintf(
                                                            ngettext(
                                                                    '%d Lernmodul',
                                                                    '%d Lernmodule',
                                                                    $result['count']
                                                                    ),
                                                            $result['count']
                                                            )
                                    ]
                                    )
                            );
                }
                return $nav;
            }
        }
        return null;
    }

    /**
     * Check the voting system
     * @param      $my_obj
     * @param      $user_id
     * @param null $modules
     */
    public static function checkVote(&$my_obj, $user_id, $object_id)
    {
        $count = 0;
        $neue  = 0;

        $threshold = object_get_visit_threshold();
        $statement = DBManager::get()->prepare("
            SELECT COUNT(DISTINCT questionnaires.questionnaire_id) AS count,
                COUNT(IF((questionnaires.chdate > IFNULL(object_user_visits.visitdate, :threshold) AND questionnaires.user_id !=:user_id), questionnaires.questionnaire_id, NULL)) AS new,
                MAX(IF((questionnaires.chdate > IFNULL(object_user_visits.visitdate, :threshold) AND questionnaires.user_id !=:user_id), questionnaires.chdate, 0)) AS last_modified
            FROM questionnaire_assignments
                INNER JOIN questionnaires ON (questionnaires.questionnaire_id = questionnaire_assignments.questionnaire_id)
                LEFT JOIN object_user_visits ON(object_user_visits.object_id = questionnaires.questionnaire_id AND object_user_visits.user_id = :user_id AND object_user_visits.type = 'vote')
            WHERE questionnaire_assignments.range_id = :course_id
                AND questionnaire_assignments.range_type IN ('course', 'institute')
                AND questionnaires.startdate IS NOT NULL
                AND questionnaires.startdate <= UNIX_TIMESTAMP()
                AND (
                    questionnaires.stopdate IS NULL
                    OR questionnaires.stopdate > UNIX_TIMESTAMP()
               )
            GROUP BY questionnaire_assignments.range_id
        ");
        $statement->execute([
            'threshold' => $threshold,
            'user_id'   => $user_id,
            'course_id' => $object_id
        ]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            $count = $result['count'];
            $neue  = $result['new'];

            if (!is_null($result['last_modified']) && (int)$result['last_modified'] != 0) {
                if ($my_obj['last_modified'] < $result['last_modified']) {
                    $my_obj['last_modified'] = $result['last_modified'];
                }
            }
        }

        $sql = "SELECT COUNT(a.eval_id) as count,
                       COUNT(IF((chdate > IFNULL(b.visitdate, :threshold) AND d.author_id !=:user_id ), a.eval_id, NULL)) AS neue,
                       MAX(IF ((chdate > IFNULL(b.visitdate, :threshold) AND d.author_id != :user_id), chdate, 0)) AS last_modified
                FROM eval_range a
                INNER JOIN eval d
                  ON (a.eval_id = d.eval_id AND d.startdate < UNIX_TIMESTAMP() AND (d.stopdate > UNIX_TIMESTAMP() OR d.startdate + d.timespan > UNIX_TIMESTAMP() OR (d.stopdate IS NULL AND d.timespan IS NULL)))
                LEFT JOIN object_user_visits b
                  ON (b.object_id = a.eval_id AND b.user_id = :user_id AND b . type = 'eval')
                WHERE a.range_id = :course_id
                GROUP BY a.range_id";

        $statement = DBManager::get()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':course_id', $object_id);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            $count += $result['count'];
            $neue += $result['neue'];
            if (!is_null($result['last_modified']) && (int)$result['last_modified'] != 0) {
                if ($my_obj['last_modified'] < $result['last_modified']) {
                    $my_obj['last_modified'] = $result['last_modified'];
                }
            }
        }


        if ($neue || $count > 0) {
            $nav = new Navigation('vote', '#vote');
            if ($neue) {
                $nav->setImage(Icon::create('vote+new', 'attention', [
                    'title' => sprintf(
                        ngettext(
                            '%1$u Fragebogen, %2$u neuer',
                            '%1$u Fragebögen, %2$u neue',
                            $count
                        ),
                        $count,
                        $neue
                    )
                ]));
                $nav->setBadgeNumber($neue);
            } else if ($count) {
                $nav->setImage(Icon::create('vote', 'inactive', [
                    'title' => sprintf(
                        ngettext(
                            '%u Fragebogen',
                            '%u Fragebögen',
                            $count
                        ),
                        $count
                    )
                ]));
            }
            return $nav;
        }

        return null;
    }


    /**
     * Get the plugin icon navigation for a course
     * @param $seminar_id
     * @param $visitdate
     * @return mixed
     */
    public static function getPluginNavigationForSeminar($seminar_id, $sem_class, $user_id, $visitdate)
    {
        $plugin_navigation = [];
        $plugins = PluginEngine::getPlugins('StandardPlugin', $seminar_id);

        foreach ($plugins as $plugin) {
            if (!$sem_class->isSlotModule(get_class($plugin))) {
                $nav = $plugin->getIconNavigation($seminar_id, $visitdate, $user_id);
                if ($nav instanceof Navigation) $plugin_navigation[get_class($plugin)] = $nav;
            }
        }
        return $plugin_navigation;
    }


    /**
     * Get all courses vor given user in selected semesters
     */
    public static function getCourses($min_sem_key, $max_sem_key, $params = [])
    {
        // init
        $order_by          = $params['order_by'];
        $order             = $params['order'];
        $deputies_enabled  = $params['deputies_enabled'];

        $sem_data = Semester::getAllAsArray();

        $min_sem           = $sem_data[$min_sem_key];
        $max_sem           = $sem_data[$max_sem_key];
        $studygroup_filter = !$params['studygroups_enabled'] ? false : true;
        $ordering          = '';
        // create ordering
        if (!$order_by) {
            if (Config::get()->IMPORTANT_SEMNUMBER) {
                $ordering = 'veranstaltungsnummer asc, name asc';
            } else {
                $ordering .= 'name asc';
            }
        } else {
            $ordering .= $order_by . ' ' . $order;
        }

        // search for your own courses
        // Filtering by Semester
        $courses = Course::findThru($GLOBALS['user']->id, [
            'thru_table'        => 'seminar_user',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'seminar_id',
            'assoc_foreign_key' => 'seminar_id'
        ]);

        if ($deputies_enabled) {
            $datas = self::getDeputies($GLOBALS['user']->id);
            if (!empty($datas)) {
                foreach ($datas as $data) {
                    $deputies[] = Course::import($data);
                }
                $courses = array_merge($courses, $deputies);
            }
        }
        // create a new collection for more functionality
        $courses = new SimpleCollection($courses);
        if ($studygroup_filter) {
            $courses = $courses->filter(function ($a) {
                return (int)$a['status'] != 99;
            });
        }
        $courses = $courses->filter(function ($a) use ($min_sem, $max_sem) {
            return $a->start_time <= $max_sem['beginn'] &&
                   ($min_sem['beginn'] <= $a->start_time + $a->duration_time || $a->duration_time == -1);
        });
        $courses = self::sortCourses($courses, $ordering);

        return $courses;
    }


    public static function getDeputies($user_id)
    {
        $query = "SELECT DISTINCT range_id AS seminar_id
                  FROM deputies
                  JOIN seminare ON range_id = seminar_id
                  WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    public static function getDeputieGroup($range_id)
    {
        $query     = "SELECT gruppe FROM deputies WHERE range_id = ? AND user_id=?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id, $GLOBALS['user']->id]);
        return $statement->fetch(PDO::FETCH_COLUMN);
    }

    public static function getSelectedSemesters($sem = 'all')
    {
        $sem_data = Semester::getAllAsArray();
        $semesters = [];
        foreach ($sem_data as $sem_key => $one_sem) {
            $current_sem = $sem_key;
            if (!$one_sem['past']) break;
        }

        if (isset($sem_data[$current_sem + 1])) {
            $max_sem = $current_sem + 1;
        } else {
            $max_sem = $current_sem;
        }

        if (isset($sem_data[$current_sem + 2])) {
            $after_next_sem = $current_sem + 2;
        } else {
            $after_next_sem = $max_sem;
        }

        // Get the needed semester
        if (!in_array($sem, ['all', 'current', 'future', 'last', 'lastandnext'])) {
            $semesters[] = SemesterData::GetSemesterIndexById($sem);
        } else {
            switch ($sem) {
                case 'current':
                    $semesters[] = $current_sem;
                    break;
                case 'future':
                    $semesters[] = $current_sem;
                    $semesters[] = $max_sem;
                    break;
                case 'last':
                    $semesters[] = $current_sem - 1;
                    $semesters[] = $current_sem;
                    break;
                case 'lastandnext':
                    $semesters[] = $current_sem - 1;
                    $semesters[] = $current_sem;
                    $semesters[] = $max_sem;
                    break;
                default:
                    $semesters = array_keys($sem_data);
                    break;
            }
        }

        return $semesters;
    }

    public static function getPreparedCourses($sem = "all", $params = [])
    {
        $semesters   = self::getSelectedSemesters($sem);
        $current_semester_nr = SemesterData::GetSemesterIndexById(@Semester::findCurrent()->id);
        $min_sem_key = min($semesters);
        $max_sem_key = max($semesters);
        $group_field = $params['group_field'];
        $courses     = self::getCourses($min_sem_key, $max_sem_key, $params);
        $show_semester_name = UserConfig::get($GLOBALS['user']->id)->SHOWSEM_ENABLE;
        $sem_courses = [];

        $param_array = 'name seminar_id visible veranstaltungsnummer start_time duration_time status visible ';
        $param_array .= 'chdate admission_binding modules admission_prelim';

        if (!empty($courses)) {
            // filtering courses
            $modules = new Modules();
            $member_ships = User::findCurrent()->course_memberships->toGroupedArray('seminar_id', 'status gruppe');
            $children = [];
            $semester_assign = [];
            foreach ($courses as $index => $course) {
                // export object to array for simple handling
                $_course = $course->toArray($param_array);
                $_course['start_semester'] = $course->start_semester->name;
                $_course['end_semester']   = $course->end_semester->name;
                $_course['sem_class']      = $course->getSemClass();
                $_course['obj_type']       = 'sem';

                if ($group_field === 'sem_tree_id') {
                    $_course['sem_tree'] = $course->study_areas->toArray();
                }

                $user_status = @$member_ships[$course->id]['status'];
                if(!$user_status && Config::get()->DEPUTIES_ENABLE && isDeputy($GLOBALS['user']->id, $course->id)) {
                    $user_status = 'dozent';
                    $is_deputy = true;
                } else {
                    $is_deputy = false;
                }

                // get teachers only if grouping selected (for better performance)
                if ($group_field === 'dozent_id') {
                    $teachers = new SimpleCollection($course->getMembersWithStatus('dozent'));
                    $teachers->filter(function ($a) use (&$_course) {
                        return $_course['teachers'][] = $a->user->getFullName('no_title_rev');
                    });
                }

                $_course['last_visitdate'] = object_get_visit($course->id, 'sem', 'last');
                $_course['visitdate']      = object_get_visit($course->id, 'sem', '');
                $_course['user_status']    = $user_status;
                $_course['gruppe']         = !$is_deputy ? @$member_ships[$course->id]['gruppe'] : self::getDeputieGroup($course->id);
                $_course['sem_number_end'] = $course->duration_time == -1 ? $max_sem_key : SemesterData::GetSemesterIndexById($course->end_semester->id);
                $_course['sem_number']     = SemesterData::GetSemesterIndexById($course->start_semester->id);
                $_course['modules']        = $modules->getLocalModules($course->id, 'sem', $course->modules, $course->status);
                $_course['name']           = $course->name;
                $_course['temp_name']      = $course->name;
                $_course['number']         = $course->veranstaltungsnummer;
                $_course['is_deputy']      = $is_deputy;
                if ($show_semester_name && $course->duration_time != 0 && !$course->getSemClass()->offsetGet('studygroup_mode')) {
                    $_course['name'] .= ' (' . $course->getFullname('sem-duration-name') . ')';
                }
                if ($course->parent_course) {
                    $_course['parent_course'] = $course->parent_course;
                }
                $_course['is_group']       = $course->getSemClass()->isGroup();
                // add the the course to the correct semester
                self::getObjectValues($_course);

                if (!$_course['parent_course']) {
                    if ($course->duration_time == -1) {
                        if ($current_semester_nr >= $min_sem_key && $current_semester_nr <= $max_sem_key) {
                            $sem_courses[$current_semester_nr][$course->id] = $_course;
                            $semester_assign[$course->id] = $current_semester_nr;
                        } else {
                            $sem_courses[$max_sem_key][$course->id] = $_course;
                            $semester_assign[$course->id] = $max_sem_key;
                        }
                    } else {
                        for ($i = $min_sem_key; $i <= $max_sem_key; $i += 1) {
                            if ($i >= $_course['sem_number'] && $i <= $_course['sem_number_end']) {
                                $sem_courses[$i][$course->id] = $_course;
                                $semester_assign[$course->id] = $i;
                            }
                        }
                    }
                } else {
                    $children[$_course['parent_course']][] = $_course;
                }
            }

            // Now sort children directly under their parent.
            foreach ($children as $parent => $kids) {
                $sem_courses[$semester_assign[$parent]][$parent]['children'] = $kids;
            }

        } else {
            return null;
        }

        if (empty($sem_courses)) {
            return null;
        }

        if ($params['main_navigation']) {
            return $sem_courses;
        }

        krsort($sem_courses);

        // grouping
        if ($group_field == 'sem_number' && !$params['order_by']) {
            foreach ($sem_courses as $index => $courses) {
                uasort($courses, function ($a, $b) {
                    $extra_condition = 0;
                    if (Config::get()->IMPORTANT_SEMNUMBER) {
                        $extra_condition = strcmp($a['number'], $b['number']);
                    }

                    return ($a['gruppe'] - $b['gruppe'])
                        ?: $extra_condition
                        ?: strcmp($a['temp_name'], $b['temp_name']);
                });
                $sem_courses[$index] = $courses;
            }
        }
        // Group by teacher
        if ($group_field == 'dozent_id') {
            self::groupByTeacher($sem_courses);
        }

        // Group by Sem Status
        if ($group_field == 'sem_status') {
            self::groupBySemStatus($sem_courses);
        }

        // Group by colors
        if ($group_field == 'gruppe') {
            self::groupByGruppe($sem_courses);
        }

        // Group by sem_tree
        if ($group_field == 'sem_tree_id') {
            self::groupBySemTree($sem_courses);
        }

        return !empty($sem_courses) ? $sem_courses : false;
    }

    public static function  checkParticipants(&$my_obj, $user_id, $object_id, $is_admission)
    {
        if ($my_obj["modules"]["participants"]) {
            if (SeminarCategories::GetByTypeId($my_obj['status'])->studygroup_mode) {
                $nav = new Navigation('participants', 'dispatch.php/course/studygroup/members/?cid=' . $object_id);
            } else {
                $nav = new Navigation('participants', 'dispatch.php/course/members/index');
            }


            if ($GLOBALS['perm']->have_perm('admin', $user_id) || in_array($my_obj['user_status'], words('dozent tutor'))) {
                $count            = 0;
                $neue             = 0;
                $all_auto_inserts = AutoInsert::getAllSeminars(true);
                $auto_insert_perm = Config::get()->AUTO_INSERT_SEM_PARTICIPANTS_VIEW_PERM;

                $sql       = "SELECT
                        COUNT(a.user_id) as count,
                        COUNT(IF((mkdate > IFNULL(b.visitdate, :threshold) AND a.user_id !=:user_id), a.user_id, NULL)) AS neue,
                        MAX(IF((mkdate > IFNULL(b.visitdate, :threshold) AND a.user_id != :user_id), mkdate, 0)) AS last_modified
                    FROM admission_seminar_user a
                    LEFT JOIN object_user_visits b ON (b.object_id = a.seminar_id AND b.user_id = :user_id AND b.type ='participants')
                    WHERE a.seminar_id = :course_id";
                $statement = DBManager::get()->prepare($sql);
                $statement->bindValue(':user_id', $user_id);
                $statement->bindValue(':course_id', $object_id);
                $statement->bindValue(':threshold', object_get_visit_threshold());
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                if (!empty($result)) {
                    if ($GLOBALS['perm']->have_perm('admin', $user_id) || in_array($my_obj['user_status'], words('dozent tutor'))) {
                        $count = $result['count'];
                        $neue  = $result['neue'];
                        if ($my_obj['last_modified'] < $result['last_modified']) {
                            $my_obj['last_modified'] = $result['last_modified'];
                        }
                    }
                }

                $sql       = "SELECT
                    COUNT(a . user_id) as count,
                    COUNT(IF((mkdate > IFNULL(b.visitdate, :threshold) AND a.user_id !=:user_id), a.user_id, NULL)) AS neue,
                    MAX(IF ((mkdate > IFNULL(b.visitdate, :threshold) AND a.user_id != :user_id), mkdate, 0)) AS last_modified
                    FROM seminar_user a
                    LEFT JOIN object_user_visits b ON(b . object_id = a . seminar_id AND b . user_id = :user_id AND b . type = 'participants')
                    WHERE seminar_id = :course_id";
                $statement = DBManager::get()->prepare($sql);
                $statement->bindValue(':user_id', $user_id);
                $statement->bindValue(':course_id', $object_id);
                $statement->bindValue(':threshold', object_get_visit_threshold());
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);


                if (!empty($result)) {
                    // show the participants-icon only if the module is activated and it is not an auto-insert-sem
                    if (in_array($object_id, $all_auto_inserts)) {
                        if ($GLOBALS['perm']->have_perm('admin', $user_id) && !$GLOBALS['perm']->have_perm($auto_insert_perm, $user_id)) {
                            return null;
                        } else if ($GLOBALS['perm']->permissions[$auto_insert_perm] > $GLOBALS['perm']->permissions[$my_obj['user_status']]) {
                            return null;
                        }
                    }
                    $count += $result['count'];
                    $neue += $result['neue'];

                    if ($GLOBALS['perm']->have_perm('admin', $user_id) || in_array($my_obj['user_status'], words('dozent tutor'))) {
                        if ($my_obj['last_modified'] < $result['last_modified']) {
                            $my_obj['last_modified'] = $result['last_modified'];
                        }
                    }
                }

                if ($neue) {
                    $nav->setImage(
                        Icon::create(
                            'persons+new',
                            'attention',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%1$d Teilnehmende/r, %2$d neue/r',
                                        '%1$d Teilnehmende, %2$d neue',
                                        $count
                                    ),
                                    $count,
                                    $neue
                                )
                            ]
                        )
                    );
                    $nav->setBadgeNumber($neue);
                } else if ($count) {
                    $nav->setImage(
                        Icon::create(
                            'persons',
                            'inactive',
                            [
                                'title' => sprintf(
                                    ngettext(
                                        '%d Teilnehmende/r',
                                        '%d Teilnehmende',
                                        $count
                                    ),
                                    $count
                                )
                            ]
                        )
                    );
                }
            } else {
                $nav->setImage(
                    Icon::create('persons', 'inactive', ["title" => _('Teilnehmende')]));
            }
            return $nav;
        }
        return null;
    }


    /**
     * Get the whole icon-navigation for a given course
     * @param $object_id
     * @param $my_obj_values
     * @param null $sem_class
     * @param $user_id
     * @return array
     */
    public static function getAdditionalNavigations($object_id, &$my_obj_values, $sem_class, $user_id)
    {
        if ($threshold = object_get_visit_threshold()) {
            $my_obj_values['visitdate'] = max($my_obj_values['visitdate'], $threshold);
        }

        $plugin_navigation = MyRealmModel::getPluginNavigationForSeminar($object_id, $sem_class, $user_id, $my_obj_values['visitdate']);
        $available_modules = 'forum participants documents overview scm schedule wiki vote literature elearning_interface';

        foreach (words($available_modules) as $key) {

            // Go to next module if current module is not available and not voting-module
            if (!$my_obj_values['modules'][$key] && strcmp('vote', $key) !== 0) {
                $navigation[$key] = null;
                continue;
            }
            if (!Config::get()->VOTE_ENABLE && strcmp($key, 'vote') === 0) {
                continue;
            }

            if (!Config::get()->WIKI_ENABLE && strcmp($key, 'wiki') === 0) {
                continue;
            }

            if (!Config::get()->ELEARNING_INTERFACE_ENABLE && strcmp($key, 'elearning_interface') === 0) {
                continue;
            }

            if (!Config::get()->LITERATURE_ENABLE && strcmp($key, 'literature') === 0) {
                continue;
            }

            $function = 'check' . ucfirst($key);

            if (method_exists(__CLASS__, $function)) {
                $params = [&$my_obj_values,
                                $user_id,
                                $object_id];
                if (strcmp($key, 'participants') === 0) {
                    array_push($params, false);
                }
                $nav = call_user_func_array(['self', $function], $params);

            }

            if ($sem_class) {
                $module = $sem_class->getModule($key);
                if ($module instanceof StandardPlugin) {
                    $nav = $module->getIconNavigation($object_id, $my_obj_values['visitdate'], $user_id);
                }
            }

            // add the main navigation item to resultset
            $navigation[$key] = $nav;
            unset($nav);
        }

        $navigation = array_merge($navigation, $plugin_navigation);
        unset($plugin_navigation);
        return $navigation;
    }


    /**
     * @param $course_id
     * @return array
     */
    public static function getSemTree($course_id, $depth = false)
    {
        $the_tree        = TreeAbstract::GetInstance("StudipSemTree");
        $view            = DbView::getView('sem_tree');
        $ret             = null;
        $view->params[0] = $course_id;
        $rs              = $view->get_query("view:SEMINAR_SEM_TREE_GET_IDS");
        while ($rs->next_record()) {
            $ret[$rs->f('sem_tree_id')]['name'] = $the_tree->getShortPath($rs->f('sem_tree_id'), null, ">", $depth ? $depth - 1 : 0);
            $ret[$rs->f('sem_tree_id')]['info'] = $the_tree->getValue($rs->f('sem_tree_id'), 'info');
        }

        return $ret;
    }


    /**
     * Returns the id for the studygroup name
     * @return Interger
     */
    public static function getStudygroupId()
    {
        $statement = DBManager::get()->prepare(
            "SELECT id FROM sem_classes WHERE name = :name"
        );
        $statement->execute(['name' => 'Studiengruppen']);
        $result = $statement->fetch(PDO::FETCH_COLUMN);
        return $result;
    }


    /**
     * This function reset all visits on every available modules
     * @param $object
     * @param $object_id
     * @param $user_id
     * @return bool
     */
    public static function setObjectVisits(&$object, $object_id, $user_id, $timestamp = null)
    {
        // load plugins, so they have a chance to register themselves as observers
        PluginEngine::getPlugins('StandardPlugin');

        // Update news, votes and evaluations
        $query = "INSERT INTO object_user_visits
                    (object_id, user_id, type, visitdate, last_visitdate)
                  (
                    SELECT news_id, :user_id, 'news', :timestamp, 0
                    FROM news_range
                    WHERE range_id = :id
                  ) UNION (
                    SELECT questionnaire_id, :user_id, 'vote', :timestamp, 0
                    FROM questionnaire_assignments
                    WHERE range_id = :id
                  ) UNION (
                    SELECT eval_id, :user_id, 'eval', :timestamp, 0
                    FROM eval_range
                    WHERE range_id = :id
                  )
                  ON DUPLICATE KEY UPDATE last_visitdate = IFNULL(visitdate, 0), visitdate = :timestamp";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':timestamp', $timestamp ? : time());
        $statement->bindValue('id', $object_id);
        $statement->execute();

        // Update all activated modules
        foreach (words('forum documents schedule participants literature wiki scm elearning_interface') as $type) {
            if ($object['modules'][$type]) {
                object_set_visit($object_id, $type);
            }
        }

        // Update object itself
        object_set_visit($object_id, $object['obj_type']);

        return true;
    }

    /**
     * This functions check all modules for changes (new documents,...) and adds a new icon-navigation item to given course
     * This function will only add something if nothing exists to get better performance
     * @param $course
     * @param $call (debug)
     */
    public static function getObjectValues(&$course)
    {

        if (!isset($course['navigation'])) {
            // get additional navigation items
            $course['navigation'] = self::getAdditionalNavigations($course['seminar_id'], $course, $course['sem_class'], $GLOBALS['user']->id);
        }
    }


    public static function getWaitingList($user_id)
    {
        $sql = "SELECT set_id, priorities.seminar_id,'claiming' as status, seminare.Name, seminare.Ort,
                priorities.priority, coursesets.name AS cname
            FROM priorities
            INNER JOIN seminare USING(seminar_id)
            INNER JOIN coursesets USING (set_id)
            WHERE priorities.user_id = ?
            ORDER BY coursesets.name, priorities.priority";
        $claiming = DBManager::get()->fetchAll($sql, [$user_id]);
        $csets    = [];
        foreach ($claiming as $k => $claim) {
            if (!$csets[$claim['set_id']]) {
                $csets[$claim['set_id']] = new CourseSet($claim['set_id']);
            }
            $cs = $csets[$claim['set_id']];
            if (!$cs->hasAlgorithmRun()) {
                $claiming[$k]['admission_endtime'] = $cs->getSeatDistributionTime();
                $num_claiming                      = count(AdmissionPriority::getPrioritiesByCourse($claim['set_id'], $claim['seminar_id']));
                $free                              = Course::find($claim['seminar_id'])->getFreeSeats();
                if ($free <= 0) {
                    $claiming[$k]['admission_chance'] = 0;
                } else if ($free >= $num_claiming) {
                    $claiming[$k]['admission_chance'] = 100;
                } else {
                    $claiming[$k]['admission_chance'] = round(($free / $num_claiming) * 100);
                }

            } else {
                unset($claiming[$k]);
            }
        }

        $stmt = DBManager::get()->prepare(
            "SELECT admission_seminar_user.*, seminare.status as sem_status, " .
            "seminare.Name, seminare.Ort, seminare.admission_binding " .
            "FROM admission_seminar_user " .
            "INNER JOIN seminare USING(seminar_id) " .
            "WHERE user_id = ? " .
            "ORDER BY admission_seminar_user.status, name");
        $stmt->execute([$user_id]);

        $waitlists = array_merge($claiming, $stmt->fetchAll(PDO::FETCH_ASSOC));

        return $waitlists;
    }


    /**
     * Get all user assigned institutes based on simple or map
     * @return array
     */
    public static function getMyInstitutes()
    {
        $memberShips = InstituteMember::findByUser($GLOBALS['user']->id);

        if (empty($memberShips)) {
            return null;
        }
        $insts      = new SimpleCollection($memberShips);
        $institutes = [];
        $insts->filter(function ($a) use (&$institutes) {
            $array                   = $a->institute->toArray();
            $array['perms']          = $a->inst_perms;
            $array['visitdate']      = object_get_visit($a->institut_id, 'inst', '');
            $array['last_visitdate'] = object_get_visit($a->institut_id, 'inst', 'last');

            $institutes[] = $array;

            return true;
        });


        if (!empty($institutes)) {
            $Modules = new Modules();
            foreach ($institutes as $index => $inst) {
                $institutes[$index]['modules']    = $Modules->getLocalModules($inst['institut_id'], 'inst', $inst['modules'], $inst['type'] ? : 1);
                $institutes[$index]['obj_type']   = 'inst';
                $institutes[$index]['navigation'] = MyRealmModel::getAdditionalNavigations($inst['institut_id'], $institutes[$index], SemClass::getDefaultInstituteClass($inst['type']), $GLOBALS['user']->id);
            }
            unset($Modules);
        }

        return $institutes;
    }

    public static function groupBySemTree(&$sem_courses)
    {
        foreach ($sem_courses as $sem_key => $collection) {
            //We have to store the sem_tree names separately
            //since we first need the sem_tree IDs as array keys.
            //This makes it more easy to get the ordering
            //of the sem_tree objects.
            $sem_tree_names = [];
            foreach ($collection as $course) {
                if (!empty($course['sem_tree'])) {
                    foreach ($course['sem_tree'] as $tree) {
                        $sem_tree_names[$tree['sem_tree_id']] = $tree['name'];
                        $_tmp_courses[$sem_key][(string)$tree['sem_tree_id']][$course['seminar_id']] = $course;
                    }
                } else {
                    $_tmp_courses[$sem_key][""][$course['seminar_id']] = $course;
                }
            }
            uksort($_tmp_courses[$sem_key], function ($a, $b) use ($sem_tree_names) {
                $the_tree = TreeAbstract::GetInstance(
                    'StudipSemTree',
                    ['build_index' => true]
                );
                return (int)($the_tree->tree_data[$a]['index']
                    - $the_tree->tree_data[$b]['index']);
            });
            //At this point the $_tmp_courses array is sorted by the ordering
            //of the sem_tree.
            //Now we have to replace the sem_tree IDs in the second layer
            //of the $_tmp_courses array with the sem_tree names:
            foreach ($_tmp_courses[$sem_key] as $sem_tree_id => $courses) {
                foreach ($courses as $course) {
                    $_tmp_courses[$sem_key][(string)$sem_tree_names[$sem_tree_id]][$course['seminar_id']] = $course;
                }
                if ($sem_tree_names[$sem_tree_id]) {
                    unset($_tmp_courses[$sem_key][$sem_tree_id]);
                }
            }
        }

        //After the $_tmp_courses array has been built we must sort the
        //third layer (course collection) by group (color),
        //by number (at your option) and by name:
        foreach ($_tmp_courses as $sem_key => $sem_tree) {
            foreach ($sem_tree as $sem_tree_name => $collection) {
                //We must sort all courses by their group and their name:
                uasort($collection, function ($a, $b) {
                    if (Config::get()->IMPORTANT_SEMNUMBER) {
                        if ($a['gruppe'] == $b['gruppe']) {
                            if ($a['number'] == $b['number']) {
                                if ($a['temp_name'] == $b['temp_name']) {
                                    return 0;
                                }
                                return ($a['temp_name'] < $b['temp_name']) ? -1 : 1;
                            }
                            return ($a['number'] < $b['number']) ? -1 : 1;
                        }
                        return ($a['gruppe'] < $b['gruppe']) ? -1 : 1;
                    } else {
                        if ($a['gruppe'] == $b['gruppe']) {
                            if ($a['temp_name'] == $b['temp_name']) {
                                return 0;
                            }
                            return ($a['temp_name'] < $b['temp_name']) ? -1 : 1;
                        }
                        return ($a['gruppe'] < $b['gruppe']) ? -1 : 1;
                    }
                });
                $_tmp_courses[$sem_key][$sem_tree_name] = $collection;
            }
        }

        $sem_courses = $_tmp_courses;
    }

    public static function groupByGruppe(&$sem_courses)
    {
        foreach ($sem_courses as $sem_key => $collection) {
            foreach ($collection as $course) {
                $_tmp_courses[$sem_key][$course['gruppe']][$course['seminar_id']] = $course;
                ksort($_tmp_courses[$sem_key]);
            }
        }
        $sem_courses = $_tmp_courses;
    }

    public static function groupBySemStatus(&$sem_courses)
    {
        foreach ($sem_courses as $sem_key => $collection) {
            foreach ($collection as $course) {

                $sem_status = $GLOBALS['SEM_TYPE'][$course['status']]["name"]
                    . " (" . $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$course['status']]["class"]]["name"] . ")";

                $_tmp_courses[$sem_key][$sem_status][$course['seminar_id']] = $course;
            }
            // reorder array
            uksort($_tmp_courses[$sem_key], function ($a, $b) {
                if (ucfirst($a) == ucfirst($b)) return 0;
                return ucfirst($a) < ucfirst($b) ? -1 : 1;
            });
        }
        $sem_courses = $_tmp_courses;
    }

    public static function groupByTeacher(&$sem_courses)
    {
        foreach ($sem_courses as $sem_key => $collection) {
            foreach ($collection as $course) {
                if (!empty($course['teachers'])) {
                    foreach ($course['teachers'] as $fullname) {
                        $_tmp_courses[$sem_key][$fullname][$course['seminar_id']] = $course;
                    }
                } else {
                    $_tmp_courses[$sem_key][""][$course['seminar_id']] = $course;
                }
                ksort($_tmp_courses[$sem_key]);
            }
        }
        $sem_courses = $_tmp_courses;
    }


    public static function getStudygroups()
    {
        $courses = [];
        $modules = new Modules();

        $studygroups = User::findCurrent()
            ->course_memberships
            ->filter(function ($c) {
                return $c->course->getSemClass()->offsetGet('studygroup_mode');
            })->toGroupedArray('seminar_id');


        $param_array = 'name seminar_id visible veranstaltungsnummer start_time duration_time status visible ';
        $param_array .= 'chdate admission_binding modules admission_prelim';
        $courses = Course::findAndMapMany(function ($course) use ($param_array, $studygroups, $modules) {
            $ret                   = $course->toArray($param_array);
            $ret['sem_class']      = $course->getSemClass();
            $ret['start_semester'] = $course->start_semester->name;
            $ret['end_semester']   = $course->end_semester->name;
            $ret['obj_type']       = 'sem';
            $ret['last_visitdate'] = object_get_visit($course->id, 'sem', 'last');
            $ret['visitdate']      = object_get_visit($course->id, 'sem', '');
            $ret['user_status']    = $studygroups[$course->id]['status'];
            $ret['gruppe']         = $studygroups[$course->id]['gruppe'];
            $ret['modules']        = $modules->getLocalModules($course->id, 'sem', $course->modules, $course->status);
            MyRealmModel::getObjectValues($ret);

            return $ret;
        }, array_keys($studygroups));

        return $courses;
    }


    public static function checkAdmissionParticipation($course_id)
    {
        $query     = "SELECT 1 FROM admission_seminar_user WHERE user_id = ? AND seminar_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$GLOBALS['user']->id,
                                  $course_id]);
        $present = $statement->fetchColumn();
        return $present;
    }

    /**
     * Calc nav elements to get the table-column-width
     * @param $my_obj
     * @param string $group_field
     * @return int
     */
    public static function calc_nav_elements($my_obj, $group_field = 'sem_number')
    {
        $nav_elements = 0;
        if (empty($my_obj)) {
            return $nav_elements;
        }

        foreach ($my_obj as $courses) {
            if(!empty($courses)) {
                if ($group_field !== 'sem_number') {
                    // tlx: If array is 2-dimensional, merge it into a 1-dimensional
                    $courses = call_user_func_array('array_merge', $courses);
                }

                foreach ($courses as $course) {
                    $nav_elements = max($nav_elements, count(self::array_rtrim($course['navigation'])));
                }
            }
        }

        return $nav_elements;
    }

    public static function calc_single_navigation($collection)
    {
        $nav_elements = 0;
        if (!empty($collection)) {
            foreach ($collection as $course) {
                $nav_elements = max($nav_elements, count(self::array_rtrim($course['navigation'])));
            }
        }
        return $nav_elements;
    }

    /**
     * Trims an array from it's null value from the right.
     *
     * @param Array $array The array to trim
     * @return array The trimmed array
     * @author tlx
     */
    public static function array_rtrim($array)
    {
        $temp  = array_reverse($array);
        $empty = true;

        while ($empty && !empty($temp)) {
            $item = reset($temp);
            if ($empty = ($item === null)) {
                $temp = array_slice($temp, 1);
            }
        }
        return array_reverse($temp);
    }

    private static function sortCourses($courses, $order)
    {
        $sorted = $courses->orderBy($order);

        // First get all courses that can act as parent and have child courses.
        $parents = $courses->filter(function ($c) {
            return $c->getSemClass()->isGroup()
                && count($c->children) > 0;
        });

        // Sort children directly after parents. Only necessary if parents exist.
        if (count($parents) > 0) {
            $withChildren = new SimpleCollection();

            foreach ($sorted as $c) {
                if ($c->parent_course === null) {
                    $withChildren->append($c);
                    if (count($c->children) > 0) {
                        foreach ($sorted->findBy('parent_course', $c->id) as $child) {
                            $withChildren->append($child);
                        }
                    }
                }
            }

            $sorted = $withChildren;
        }

        return $sorted;
    }
}
