<?php

namespace Widgets\FilesDashboard;

use Widgets\Element;
use Widgets\Response;

require_once 'BaseWidget.php';

/**
 * This widget shows the latest files a user may see.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class LatestFilesWidget extends BaseWidget
{
    // how many latest files should be shown by default
    const DEFAULT_LIMIT = 3;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return _('Neue Dateien');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return _('Zeigt die neuesten Dateien aus dem eigenen Dateibereich und den Dateibereichen der eigenen Veranstaltungen und Einrichtungen an.');
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getFilesAndFolders(\Range $range, $scope)
    {
        $userId = $GLOBALS['user']->id;

        if ($GLOBALS['perm']->have_perm('root')) {
            return $this->findLatestFilesForRoot($userId, 10);
        }

        return $this->findAllFolders($userId, 10);
    }

    /**
     * This is a singleton widget - use at most once per container.
     *
     * {@inheritdoc}
     */
    public function mayBeDuplicated()
    {
        return false;
    }

    /**
    * This is a singleton widget - use at most once per container.
    *
    * {@inheritdoc}
     */
    public function mayBeRemoved()
    {
        return false;
    }

    // ***** CONFIGURATION *****

    /**
     * {@inheritdoc}
     */
    protected function hasConfiguration()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfigurationTemplate(Element $element, Response $response)
    {
        $response->addHeader('X-Title', _('"Neue Dateien" konfigurieren'));

        return $this->getTemplate('latest-config.php', []);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveConfiguration(Element $element, Response $response)
    {
        $limit = \Request::int('limit', self::DEFAULT_LIMIT);
        $this->setOptions(array_merge($this->getOptions(), compact('limit')));

        $response->addHeader('X-Dialog-Close', 1);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions()
    {
        return [
            'limit' => self::DEFAULT_LIMIT,
        ];
    }

    // ***** HELPERS *****

    private function findAllFolders($userId, $limit)
    {
        $rangeIds = [$userId];
        foreach ([['findMyCourses', 'id'],['findMyInstitutes', 'Institut_id']] as list($fn, $key)) {
            foreach ($this->$fn($userId) as $range) {
                $rangeIds[] = $range[$key];
            }
        }

        $folders = $this->findFoldersForRangeIds($rangeIds, $userId);
        $files = $this->findSomeFilesInFolders($folders, $userId, $limit);

        return compact('files', 'folders');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function findMyCourses($userId)
    {
        $courses = [];
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $query = 'SELECT seminare.*
                      FROM seminare
                      INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                      WHERE seminar_user.user_id = :user_id';
            if (\Config::get()->DEPUTIES_ENABLE) {
                $query .= ' UNION
                    SELECT `seminare`.*
                    FROM `seminare`
                    INNER JOIN `deputies` ON (`deputies`.`range_id` = `seminare`.`Seminar_id`)
                    WHERE `deputies`.`user_id` = :user_id';
            }
            $query .= ' ORDER BY duration_time = -1, start_time DESC, Name ASC';
            $statement = \DBManager::get()->prepare($query);
            $statement->execute([':user_id' => $userId]);

            foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $coursedata) {
                $courses[] = \Course::buildExisting($coursedata);
            }
        }

        return $courses;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function findMyInstitutes($userId)
    {
        return \Institute::getMyInstitutes($userId);
    }

    private function findFoldersForRangeIds($rangeIds, $userId)
    {
        return array_reduce(
            \Folder::findBySQL('range_id IN (?)', [$rangeIds]),
            function ($result, $folder) use ($userId) {
                if ($folder->getTypedFolder()->isReadable($userId)) {
                    $result[$folder->id] = $folder->getTypedFolder();
                }

                return $result;
            },
            []
        );

    }

    private function findSomeFilesInFolders($folders, $userId, $limit)
    {
        $files = [];
        foreach (\FileRef::findBySQL('folder_id IN (?) ORDER BY chdate DESC', [array_keys($folders)]) as $fileRef) {
            if (count($files) >= $limit) {
                break;
            }

            if (isset($folders[$fileRef->folder_id])) {
                $folder = $folders[$fileRef->folder_id];
                if ($folder->isFileDownloadable($fileRef->id, $userId)) {
                    $files[$fileRef->id] = $fileRef;
                }
            }
        }
        return $files;
    }

    private function findLatestFilesForRoot($userId, $limit)
    {
        $files = [];
        $folders = [];
        foreach (\FileRef::findBySQL('1 ORDER BY chdate DESC LIMIT ?', [$limit]) as $fileRef) {
            $folder = $fileRef->folder->getTypedFolder();
            if ($folder->isFileDownloadable($fileRef->id, $userId)) {
                $files[$fileRef->id] = $fileRef;
                $folders[$folder->id] = $folder;
            }
        }
        return compact('files', 'folders');
    }
}
