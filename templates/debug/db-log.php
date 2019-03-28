<? if ($GLOBALS['DEBUG_ALL_DB_QUERIES']) : ?>
    <style>
    #all_db_queries td:first-child {
        border-left: 4px solid transparent;
        border-right: 4px solid red;
    }
    #all_db_queries .sorm td:first-child {
        border-left-color: blue;
    }
    #all_db_queries .prepared td:first-child {
        border-right-color: green;
    }
    #all_db_queries .query {
        white-space: pre-wrap;
    }
    #all_db_queries ul {
        counter-reset: queries -1;
    }
    #all_db_queries li:hover {
        text-decoration: underline;
    }
    #all_db_queries li::before {
        content: "#" counter(queries);
        counter-increment: queries;
    }
    #all_db_queries li::before,
    #all_db_queries span {
        font-weight: lighter;
    }
    </style>
    <div style="display: none;" id="all_db_queries">
        <table class="default">
            <tbody>
            <? foreach ((array) DBManager::get()->queries as $query) : ?>
                <tr class="<?= $query['classes'] ?>">
                    <td>
                        <code class="query"><?= htmlReady($query['query']) ?></code>
                    </td>
                <? if ($GLOBALS['DEBUG_ALL_DB_QUERIES_WITH_TRACE']) : ?>
                    <td>
                        <ul class="list-unstyled">
                        <? foreach ($query['trace'] as $i => $row): ?>
                            <li>
                                <?= $this->render_partial('debug/trace-row.php', $row) ?>
                            </li>
                        <? endforeach; ?>
                        </ul>
                    </td>
                <? endif ?>
                </tr>
            <? endforeach ?>
            </tbody>
        </table>
    </div>
<? endif ?>
