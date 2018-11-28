<?php

namespace FilesSearch;

/**
 * This class is responsible for fetching and representing the results
 * of a search using a Query instance.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class Result
{
    // how many search items are prefetched to decrease number of
    // database selects
    const FETCH_BUFFER_SIZE = 30;

    private $query;

    private $count = 0;
    private $rawCount;
    private $resultPage;
    private $rowsSeen = 0;

    private $statement;

    private $fetchBuffer = [];
    private $fetchBufferDone = false;
    private $typedFoldersBuffer = [];

    /**
     * Creates a new Result object using a Query and fetching results
     * from the database.
     *
     * @param Query $query the search query to use
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
        $this->search();
    }

    /**
     * Returns the current result page. The Query object associated
     * with this instance is used to determince the slice of the
     * results to be used as a page.
     *
     * @return array an array of search results
     */
    public function getResultPage()
    {
        if (!isset($this->resultPage)) {
            $this->resultPage = $this->fetchResultPage();
        }

        return $this->resultPage;
    }

    private function fetchResultPage()
    {
        if (!$this->statement) {
            throw new \RuntimeException();
        }

        $this->drop($this->query->getOffset());
        $results = $this->take($this->query->getResultsPerPage());

        $fileRefs = $this->findFileRefs($results);

        return array_map(
            function ($result) use ($fileRefs) {
                if (isset($fileRefs[$result['id']])) {
                    $result['fileRef'] = $fileRefs[$result['id']];
                }

                return $result;
            },
            $results
        );
    }

    /**
     * Returns whether there COULD be more results.
     *
     * @return bool true if there could be more results; false if
     *              all possible matches were already evaluated
     */
    public function hasMore()
    {
        return $this->rowsSeen < $this->rawCount;
    }

    /**
     * If we already evaluated all possible matches, returns the total
     * number of all actual matches that the user may see.
     *
     * @return int the number of all actual matches that the user may
     *             see
     *
     * @throws RuntimeException if there could be more results
     *
     * @see hasMore
     */
    public function getTotal()
    {
        if ($this->hasMore()) {
            throw new \LogicException('You must exhaust the search before calling `Result::getTotal`.');
        }

        return $this->count;
    }

    private function search()
    {
        $this->statement = $this->getStatement();
        $this->rawCount = $this->statement->rowCount();
    }

    private function findFileRefs(array $results)
    {
        return array_reduce(
            \FileRef::findMany(
                array_map(
                    function ($result) {
                        return $result['id'];
                    },
                    $results
                )
            ),
            function ($memo, $fileRef) {
                $memo[$fileRef->id] = $fileRef;

                return $memo;
            },
            []
        );
    }

    private function drop($num)
    {
        for ($i = 0; $i < $num; ++$i) {
            if (false === $this->next()) {
                break;
            }
        }
    }

    private function take($num)
    {
        $result = [];
        for ($i = 0; $i < $num; ++$i) {
            if (($object = $this->next()) === false) {
                break;
            }
            $result[] = $object;
        }

        return $result;
    }

    private function next()
    {
        while ($object = $this->fetch()) {
            if (isset($this->typedFoldersBuffer[$object['folder_id']])) {
                $folder = $this->typedFoldersBuffer[$object['folder_id']];
            } elseif (!$folder = $this->getTypedFolder($object['folder_id'])) {
                continue;
            }

            if ($this->checkPermission($folder)) {
                ++$this->count;

                $object['folder'] = $folder;

                return $object;
            }
        }

        return false;
    }

    private function fetch()
    {
        if (!$this->fetchBufferDone && !count($this->fetchBuffer)) {
            $this->fetchBuffer = [];
            for ($i = 0; $i < self::FETCH_BUFFER_SIZE; ++$i) {
                $next = $this->statement->fetch();
                if ($next) {
                    $this->fetchBuffer[] = $next;
                } else {
                    $this->fetchBufferDone = true;
                    break;
                }
            }

            $this->typedFoldersBuffer = $this->prefetchTypedFolders();
        }

        if ($result = array_shift($this->fetchBuffer)) {
            ++$this->rowsSeen;
        }

        return $result;
    }

    private function prefetchTypedFolders()
    {
        return array_reduce(
            $this->getFoldersFromFetchBuffer(),
            function ($memo, $folder) {
                if ($folder) {
                    $memo[$folder->id] = $folder->getTypedFolder();
                }

                return $memo;
            },
            []
        );
    }

    private function getFoldersFromFetchBuffer()
    {
        return \Folder::findMany(
            array_unique(
                array_map(
                    function ($row) {
                        return $row['folder_id'];
                    },
                        $this->fetchBuffer
                )
            )
        );
    }

    private function getTypedFolder($folderId)
    {
        $sormFolder = \Folder::find($folderId);

        return $sormFolder ? $sormFolder->getTypedFolder() : null;
    }

    private function checkPermission($folder)
    {
        $userId = $this->getUserId();

        return $folder->isVisible($userId) && $folder->isReadable($userId);
    }

    private function getStatement()
    {
        list($query, $params) = $this->getSqlQueryAndParams();
        $statement = \DBManager::get()->prepare($query);
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        return $statement;
    }

    private function getSqlQueryAndParams()
    {
        list($whereSql, $whereParams) = $this->getWhereCondition();

        return [
            implode(' ', [$this->getBaseSqlQuery(), $whereSql, $this->getOrderBy()]),
            array_merge(
                [
                    ':query' => $this->getSearchPhrase(),
                ],
                $whereParams
            ),
        ];
    }

    private function getBaseSqlQuery()
    {
        $ftQuery = '
            SELECT
                file_ref_id,
                text,
                relevance,
                SUM(MATCH (text) AGAINST (:query IN BOOLEAN MODE) * relevance) as ranking
            FROM files_search_index
            WHERE MATCH (text) AGAINST (:query IN BOOLEAN MODE)
            GROUP BY file_ref_id';

        return '
            SELECT sr.text, sr.ranking, fsa.id, fsa.folder_id, fsa.semester_start, fsa.semester_end, fsa.file_ref_mkdate
            FROM ( '.$ftQuery.' ) as sr
            JOIN files_search_attributes fsa
            ON (file_ref_id = fsa.id)';
    }

    private function getSearchPhrase()
    {
        $words = explode(' ', $this->query->getQuery());

        return implode('* ', array_merge($words, ['\''.$this->query->getQuery().'\'']));
    }

    private function getWhereCondition()
    {
        $filter = $this->query->getFilter();
        $withCategory = $filter->getCategory() !== null
                      && !empty(trim($filter->getCategory()));
        $withSemester = $filter->getSemester() !== null;

        $sql = [];
        $params = [];

        if ($withCategory) {
            $sql[] = 'fsa.folder_range_type = :category';
            $params[':category'] = $filter->getCategory();
        }

        if ($withSemester) {
            $sql[] = '
              IF(
                semester_start,
                IF(
                  semester_end,
                  :semesterstart BETWEEN semester_start AND semester_end,
                  semester_start <= :semesterstart
                ),
                file_ref_mkdate BETWEEN :semesterstart AND :semesterend
              )';
            $semester = $filter->getSemester();
            $params[':semesterstart'] = $semester->beginn;
            $params[':semesterend'] = $semester->ende;
        }

        if (count($sql)) {
            $sql = 'WHERE '.join(' AND ', $sql).' ';
        } else {
            $sql = '';
        }

        return [$sql, $params];
    }

    private function getOrderBy()
    {
        switch ($this->query->getSort()) {
            case QUERY::SORT_CHDATE:
                $orderBy = 'ORDER BY fsa.file_ref_mkdate DESC';
                break;

            case QUERY::SORT_RELEVANCE:
                $orderBy = 'ORDER BY sr.ranking DESC';
                break;

            default:
                $orderBy = '';
        }

        return $orderBy;
    }

    private function getUserId()
    {
        return $this->query->getUser()->id;
    }
}
