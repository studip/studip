<?php
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

require_once 'lib/dates.inc.php';

class SemBrowse {

    public $sem_browse_data;
    public $persistent_fields = [
        'level', 'cmd', 'start_item_id', 'show_class', 'group_by',
        'search_result', 'default_sem', 'sem_status', 'show_entries', 'sset'
    ];
    public $search_obj;
    public $sem_tree;
    public $range_tree;
    public $show_result;
    public $sem_number;
    public $group_by_fields = [];
    public $target_url;
    public $target_id;

    function __construct($sem_browse_data_init = [])
    {

        $this->group_by_fields =
                [
                    [
                        'name'        => _('Semester'),
                        'group_field' => 'sem_number'
                    ],
                    [
                        'name'        => _('Bereich'),
                        'group_field' => 'bereich'
                    ],
                    [
                        'name'         => _('Lehrende'),
                        'group_field'  => 'fullname',
                        'unique_field' => 'username'
                    ],
                    [
                        'name'        => _('Typ'),
                        'group_field' => 'status'
                    ],
                    [
                        'name'         => _('Einrichtung'),
                        'group_field'  => 'Institut',
                        'unique_field' => 'Institut_id'
                    ]
                ];

        if (!$_SESSION['sem_browse_data']) {
            $_SESSION['sem_browse_data'] = $sem_browse_data_init;
        }
        $this->sem_browse_data =& $_SESSION['sem_browse_data'];

        $level_change = Request::option('start_item_id') || Request::submitted('search_sem_sem_change');

        for ($i = 0; $i < count($this->persistent_fields); ++$i){
            $persistend_field = $this->persistent_fields[$i];
            if (Request::get($persistend_field) != null) {
                $this->sem_browse_data[$persistend_field] = Request::option($persistend_field);
            }
        }
        $this->search_obj = new StudipSemSearch('search_sem',
                false, !(is_object($GLOBALS['perm'])
                    && $GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'))),
                $this->sem_browse_data['show_class']);


        if (Request::get($this->search_obj->form_name . '_scope_choose')) {
            $this->sem_browse_data['start_item_id'] =
                    Request::option($this->search_obj->form_name . '_scope_choose');
        }
        if (Request::get($this->search_obj->form_name . '_range_choose')) {
            $this->sem_browse_data['start_item_id'] =
                    Request::option($this->search_obj->form_name . '_range_choose');
        }
        if (Request::get($this->search_obj->form_name . '_sem')) {
            $this->sem_browse_data['default_sem'] =
                    Request::option($this->search_obj->form_name . '_sem');
        }

        if (Request::get('keep_result_set')
                || $this->sem_browse_data['sset']
                || (!empty($this->sem_browse_data['search_result'])
                        && $this->sem_browse_data['show_entries'])) {
            $this->show_result = true;
        }

        if ($this->sem_browse_data['cmd'] == 'xts') {
            $this->sem_browse_data['level'] = 'f';
            if ($this->search_obj->new_search_button_clicked) {
                $this->show_result = false;
                $this->sem_browse_data['sset'] = false;
                $this->sem_browse_data['search_result'] = [];
            }
        }

        if($this->sem_browse_data['default_sem'] != 'all') {
            $this->sem_number[0] = intval($this->sem_browse_data['default_sem']);
        } else {
            $this->sem_number = false;
        }

        $sem_status = (is_array($this->sem_browse_data['sem_status'])) ? $this->sem_browse_data['sem_status'] : false;

        if ($this->sem_browse_data['level'] == 'vv') {
            if (!$this->sem_browse_data['start_item_id']){
                $this->sem_browse_data['start_item_id'] = 'root';
            }
            $this->sem_tree = new StudipSemTreeViewSimple(
                    $this->sem_browse_data['start_item_id'],
                    $this->sem_number, $sem_status,
                    !(is_object($GLOBALS['perm'])
                            && $GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'))));
            $this->sem_browse_data['cmd'] = 'qs';
            if (Request::option('cmd') != 'show_sem_range'
                    && $level_change
                    && !$this->search_obj->search_button_clicked ) {
                $this->get_sem_range($this->sem_browse_data['start_item_id'], false);
                $this->show_result = true;
                $this->sem_browse_data['show_entries'] = 'level';
                $this->sem_browse_data['sset'] = false;
            }
            if ($this->search_obj->sem_change_button_clicked) {
                $this->get_sem_range($this->sem_browse_data['start_item_id'],
                        ($this->sem_browse_data['show_entries'] == 'sublevels'));
                $this->show_result = true;
            }
        }

        if ($this->sem_browse_data['level'] == 'ev'){
            if (!$this->sem_browse_data['start_item_id']) {
                $this->sem_browse_data['start_item_id'] = 'root';
            }
            $this->range_tree = new StudipSemRangeTreeViewSimple(
                    $this->sem_browse_data['start_item_id'],
                    $this->sem_number,
                    $sem_status,
                    !(is_object($GLOBALS['perm'])
                            && $GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'))));
            $this->sem_browse_data['cmd'] = 'qs';
            if (Request::option('cmd') != 'show_sem_range_tree'
                    && $level_change
                    && !$this->search_obj->search_button_clicked ) {
                $this->get_sem_range_tree($this->sem_browse_data['start_item_id'], false);
                $this->show_result = true;
                $this->sem_browse_data['show_entries'] = 'level';
                $this->sem_browse_data['sset'] = false;
            }
            if ($this->search_obj->sem_change_button_clicked) {
                $this->get_sem_range_tree($this->sem_browse_data['start_item_id'],
                        ($this->sem_browse_data['show_entries'] == 'sublevels'));
                $this->show_result = true;
            }
        }

        if ($this->search_obj->search_button_clicked
                && !$this->search_obj->new_search_button_clicked) {
            $this->search_obj->override_sem = $this->sem_number;
            $this->search_obj->doSearch();
            if ($this->search_obj->found_rows) {
                $this->sem_browse_data['search_result'] = array_flip($this->search_obj->search_result->getRows('seminar_id'));
            } else {
                $this->sem_browse_data['search_result'] = [];
            }
            $this->show_result = true;
            $this->sem_browse_data['show_entries'] = false;
            $this->sem_browse_data['sset'] = Request::get($this->search_obj->form_name . "_quick_search_parameter");
        }


        if (Request::option('cmd') == 'show_sem_range') {
            $tmp = explode('_', Request::option('item_id'));
            $this->get_sem_range($tmp[0], isset($tmp[1]));
            $this->show_result = true;
            $this->sem_browse_data['show_entries'] = (isset($tmp[1])) ? 'sublevels' : 'level';
            $this->sem_browse_data['sset'] = false;
        }

        if (Request::option('cmd') == 'show_sem_range_tree') {
            $tmp = explode('_', Request::option('item_id'));
            $this->get_sem_range_tree($tmp[0],isset($tmp[1]));
            $this->show_result = true;
            $this->sem_browse_data['show_entries'] = (isset($tmp[1])) ? 'sublevels' : 'level';
            $this->sem_browse_data['sset'] = false;
        }

        if (Request::option('do_show_class')
                && count($this->sem_browse_data['sem_status'])) {
            $this->get_sem_class();
        }

    }

    /**
     * Returns whether the search for modules has to be displayed.
     *
     * @return boolean True if search for modules has to be displayed.
     */
    private function showModules()
    {
        if ($this->sem_browse_data['show_class'] == 'all') {
            return true;
        }
        if (!is_array($this->classes_show_module)) {
            $this->classes_show_class = [];
            foreach ($GLOBALS['SEM_CLASS'] as $sem_class_key => $sem_class){
                if ($sem_class['module']) {
                    $this->classes_show_module[] = $sem_class_key;
                }
            }
        }
        return in_array($this->sem_browse_data['show_class'], $this->classes_show_class);
    }

    public function show_class()
    {
        if ($this->sem_browse_data['show_class'] == 'all') {
            return true;
        }
        if (!is_array($this->classes_show_class)) {
            $this->classes_show_class = [];
            foreach ($GLOBALS['SEM_CLASS'] as $sem_class_key => $sem_class) {
                if ($sem_class['bereiche']) {
                    $this->classes_show_class[] = $sem_class_key;
                }
            }
        }
        return in_array($this->sem_browse_data['show_class'], $this->classes_show_class);
    }

    public function get_sem_class()
    {
        $query = "SELECT `Seminar_id`
                  FROM `seminare`
                  WHERE `status` IN (?)";

        $show_all = is_object($GLOBALS['perm'])
                 && $GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM);
        if (!$show_all) {
            $query .= ' AND visible = 1';
        }

        $sem_ids = DBManager::get()->fetchAll(PDO::FETCH_COLUMN);
        if (is_array($sem_ids)) {
            $this->sem_browse_data['search_result'] = array_flip($sem_ids);
        }
        $this->sem_browse_data['sset'] = true;
        $this->show_result = true;
    }

    public function get_sem_range($item_id, $with_kids)
    {
        if (!is_object($this->sem_tree)) {
            $sem_status = (is_array($this->sem_browse_data['sem_status'])) ? $this->sem_browse_data['sem_status'] : false;
            $this->sem_tree = new StudipSemTreeViewSimple(
                    $this->sem_browse_data['start_item_id'],
                    $this->sem_number,
                    $sem_status,
                    !(is_object($GLOBALS['perm'])
                            && $GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'))));
        }
        $sem_ids = $this->sem_tree->tree->getSemIds($item_id,$with_kids);
        if (is_array($sem_ids)) {
            $this->sem_browse_data['search_result'] = array_flip($sem_ids);
        } else {
            $this->sem_browse_data['search_result'] = [];
        }
    }

    public function get_sem_range_tree($item_id, $with_kids)
    {
        $range_object = RangeTreeObject::GetInstance($item_id);
        if ($with_kids) {
            $inst_ids = $range_object->getAllObjectKids();
        }
        $inst_ids[] = $range_object->item_data['studip_object_id'];
        $db_view = DbView::getView('sem_tree');
        $db_view->params[0] = $inst_ids;
        $db_view->params[1] = (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'))) ? '' : ' AND c.visible=1';
        $db_view->params[1] .= is_array($this->sem_browse_data['sem_status'])
                ? " AND c.status IN('" . join("','", $this->sem_browse_data['sem_status']) ."')"
                : '';
        $db_view->params[2] = is_array($this->sem_number)
                ? ' HAVING sem_number IN ('
                    . join(',', $this->sem_number)
                    . ') OR (sem_number <= '
                    . $this->sem_number[count($this->sem_number) - 1]
                    . '  AND (sem_number_end >= '
                    . $this->sem_number[count($this->sem_number) - 1]
                    . ' OR sem_number_end = -1)) '
                : '';
        $db_snap = new DbSnapshot($db_view->get_query('view:SEM_INST_GET_SEM'));
        if ($db_snap->numRows) {
            $sem_ids = $db_snap->getRows('Seminar_id');
            $this->sem_browse_data['search_result'] = array_flip($sem_ids);
        } else {
            $this->sem_browse_data['search_result'] = [];
        }
    }

    /**
     * Prints the quicksearch form.
     */
    private function printQuickSearch()
    {
        if ($this->sem_browse_data['level'] === 'vv') {
            $this->search_obj->sem_tree =& $this->sem_tree->tree;
            if ($this->sem_tree->start_item_id !== 'root') {
                $this->search_obj->search_scopes[] = $this->sem_tree->start_item_id;
            }
        } elseif ($this->sem_browse_data['level'] === 'ev') {
            $this->search_obj->range_tree =& $this->range_tree->tree;
            if ($this->range_tree->start_item_id !== 'root'){
                $this->search_obj->search_ranges[] = $this->range_tree->start_item_id;
            }
        }

        $template = $GLOBALS['template_factory']->open('sembrowse/quick-search.php');
        $template->search_obj      = $this->search_obj;
        $template->sem_browse_data = $this->sem_browse_data;
        $template->sem_tree        = $this->sem_tree;
        $template->range_tree      = $this->range_tree;
        $template->quicksearch     = $this->getQuicksearch();

        echo $template->render();
    }

    private function getQuicksearch()
    {
        $quicksearch = QuickSearch::get(
            $this->search_obj->form_name . '_quick_search',
            new SeminarSearch('number-name-lecturer')
        );

        $quicksearch->setAttributes([
            'aria-label' => _('Suchbegriff'),
            'autofocus'  => '',
        ]);
        $quicksearch->fireJSFunctionOnSelect('selectSem');
        $quicksearch->noSelectbox();
        $quicksearch->defaultValue(
            $this->sem_browse_data['sset'] ?: '',
            $this->sem_browse_data['sset'] ?: ''
        );

        return $quicksearch;
    }

    private function printExtendedSearch()
    {
        $template = $GLOBALS['template_factory']->open('sembrowse/extended-search.php');
        $template->search_obj      = $this->search_obj;
        $template->sem_browse_data = $this->sem_browse_data;
        $template->show_class      = $this->show_class();
        echo $template->render();
    }

    public function do_output()
    {
        if ($this->sem_browse_data['cmd'] == 'xts') {
            $this->printExtendedSearch();
        } else {
            $this->printQuickSearch();
        }
        $path_id = Request::option('path_id');
        URLHelper::addLinkParam('path_id', $path_id);
        $this->print_level($path_id);
        if ($this->show_result) {
            $this->print_result();
        }
    }

    public function print_level($start_id = null)
    {
        ob_start();

        SkipLinks::addIndex(_('Gefundene Bereiche'), 'sem_search_level', 110);

        echo "\n" . '<table id="sem_search_level" width="99%">' . "\n";
        if ($this->sem_browse_data['level'] == 'f') {

            echo "\n" . '<tr><td align="center" class="table_row_odd" height="40" valign="middle">
                <div style="margin-top:10px;margin-bottom:10px;">';
            if (!($this->show_result && count($this->sem_browse_data['search_result']))) {
                $navigation_options =
                    [
                        'semtree'   =>
                            [
                                'visible' => $this->show_class(),
                                'img'     => Assets::img('directory-search.png',
                                    [
                                        'size' => '260@100',
                                        'alt'  => _('Suche im Vorlesungsverzeichnis')
                                    ])
                            ],
                        'rangetree' =>
                            [
                                'visible' => true,
                                'img'     => Assets::img('institute-search.png',
                                    [
                                        'size' => '260@100',
                                        'alt'  => _('Suche im Einrichtungsverzeichnis')
                                    ])
                            ],
                        'module'    =>
                            [
                                'visible' => $this->showModules(),
                                'img'     => Assets::img('directory-search.png',
                                    [
                                        'size' => '260@100',
                                        'alt'  => _('Suche im Modulverzeichnis')
                                    ])
                            ]
                    ];
                echo '<table class="hidden-medium-down">';
                echo '<tr>';

                foreach (Config::get()->COURSE_SEARCH_NAVIGATION_OPTIONS as $name => $option) {
                    $navigation = self::getSearchOptionNavigation('courses', $name);
                    if ($navigation) {
                        if ($option['url']) {
                            SkipLinks::addLink($navigation->getTitle(),
                                    $navigation->getURL());
                            echo '<td style="whitespace:nowrap; text-align: center; font-size:1.5em; padding:15px; font-weight:bold;">';
                            printf('<a href="%s">%s<br>%s</a></td>',
                                    $navigation->getURL(),
                                    $navigation->getTitle(),
                                    is_array($option['img'])
                                        ? (Assets::img($option['img']['filename'],
                                            array_merge((array) $option['img']['attributes'],
                                            tooltip2($navigation->getTitle()))))
                                        : '');
                        } else {
                            SkipLinks::addLink($navigation->getTitle(),
                                    $navigation->getURL());
                            echo '<td style="whitespace:nowrap; text-align: center; font-size:1.5em; padding:15px; font-weight:bold;">';
                            printf('<a href="%s">%s<br>%s</a></td>',
                                    $navigation->getURL(),
                                    $navigation->getTitle(),
                                    $navigation_options[$name]['img']);
                        }
                    }
                }

                echo '</tr></table>';

                ?>
                <nav class="hidden-large-up button-group">
                    <?= Studip\LinkButton::create(_('Suche in Einrichtungen'),
                            URLHelper::getURL('?level=ev&cmd=qs&sset=0')) ?>
                    <? if ($this->show_class()) : ?>
                        <?= Studip\LinkButton::create(_('Suche im Vorlesungsverzeichnis'),
                                URLHelper::getURL('?level=vv&cmd=qs&sset=0')) ?>
                    <? endif; ?>
                </nav>
                <?
            }
            echo '</div>';
        }
        if ($this->sem_browse_data['level'] == 'vv') {
            echo "\n" . '<tr><td align="center">';
            $this->sem_tree->show_entries = $this->sem_browse_data['show_entries'];
            $this->sem_tree->showSemTree($start_id);
        }
        if ($this->sem_browse_data['level'] == 'ev') {
            echo "\n" . '<tr><td align="center">';
            $this->range_tree->show_entries = $this->sem_browse_data['show_entries'];
            $this->range_tree->showSemRangeTree($start_id);
        }
        echo '</td></tr></table>';
        ob_end_flush();
    }

    public function print_result()
    {
        ob_start();
        global $_fullname_sql, $SEM_TYPE, $SEM_CLASS;

        if (is_array($this->sem_browse_data['search_result'])
                && count($this->sem_browse_data['search_result'])) {
            if (!is_object($this->sem_tree)) {
                $this->sem_tree = new StudipSemTreeViewSimple(
                        $this->sem_browse_data['start_item_id'],
                        $this->sem_number,
                        is_array($this->sem_browse_data['sem_status'])
                            ? $this->sem_browse_data['sem_status'] : false,
                        !(is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm(get_config('SEM_VISIBILITY_PERM'))));
            }
            $the_tree = $this->sem_tree->tree;

            SkipLinks::addIndex(_('Suchergebnis'), 'sem_search_result', 90);

            list($group_by_data, $sem_data) = $this->get_result();

            $visibles = $sem_data;
            if (!$GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)) {
                $visibles = array_filter($visibles, function ($c) {
                    return key($c['visible']) == 1;
                });
            }

            echo '<table class="default" id="sem_search_result">';
            echo '<caption>'
                . sprintf(_(' %s Veranstaltungen gefunden %s, Gruppierung: %s'), count($visibles),
                (($this->sem_browse_data['sset']) ? _('(Suchergebnis)') : ''),
                $this->group_by_fields[$this->sem_browse_data['group_by']]['name'])
                . '</caption>';

            foreach ($group_by_data as $group_field => $sem_ids) {
                if (Config::get()->COURSE_SEARCH_SHOW_ADMISSION_STATE) {
                    echo '<tr><th colspan="6">';
                } else {
                    echo '<tr><th colspan="5">';
                }
                switch ($this->sem_browse_data['group_by']){
                    case 0:
                        echo htmlReady($this->search_obj->sem_dates[$group_field]['name']);
                        break;
                    case 1:
                        if ($the_tree->tree_data[$group_field]) {
                            echo htmlReady($the_tree->getShortPath($group_field));
                            if (is_object($this->sem_tree)){
                                echo $this->sem_tree->getInfoIcon($group_field);
                            }
                        } else {
                            echo _('keine Studienbereiche eingetragen');
                        }
                        break;
                    case 3:
                        echo htmlReady($SEM_TYPE[$group_field]['name']
                                . ' ('
                                . $SEM_CLASS[$SEM_TYPE[$group_field]['class']]['name']
                                . ')');
                        break;
                    default:
                        echo htmlReady($group_field);
                }
                echo '</th></tr>';
                ob_end_flush();
                ob_start();
                if (is_array($sem_ids['Seminar_id'])) {
                    if ($this->sem_browse_data['default_sem'] != 'all') {
                        $current_semester_id = SemesterData::GetSemesterIdByIndex($this->sem_browse_data['default_sem']);
                    }

                    // Get sem classes that can be used for grouping.
                    $grouping = SemType::getGroupingSemTypes();

                    while(list($seminar_id,) = each($sem_ids['Seminar_id'])) {
                        echo $this->printCourseRow($seminar_id, $sem_data);
                    }
                }
            }
            echo '</table>';
        } elseif ($this->search_obj->search_button_clicked
                && !$this->search_obj->new_search_button_clicked) {
            if ($this->search_obj->found_rows === false) {
                $details = [_('Der Suchbegriff fehlt oder ist zu kurz')];
            }
            if ($details) {
                PageLayout::postError(_('Ihre Suche ergab keine Treffer'), $details);
            } else {
                PageLayout::postInfo(_('Ihre Suche ergab keine Treffer'));
            }
            $this->sem_browse_data['sset'] = 0;
        }
        ob_end_flush();
    }

    public function create_result_xls($headline = '')
    {
        require_once "vendor/write_excel/OLEwriter.php";
        require_once "vendor/write_excel/BIFFwriter.php";
        require_once "vendor/write_excel/Worksheet.php";
        require_once "vendor/write_excel/Workbook.php";

        global $_fullname_sql, $SEM_TYPE, $SEM_CLASS, $TMP_PATH;

        if (!$headline) {
            $headline = _('Stud.IP Veranstaltungen') . ' - ' . Config::get()->UNI_NAME_CLEAN;
        }
        if (is_array($this->sem_browse_data['search_result'])
                && count($this->sem_browse_data['search_result'])) {
            if (!is_object($this->sem_tree)) {
                $the_tree = TreeAbstract::GetInstance('StudipSemTree', false);
            } else {
                $the_tree = $this->sem_tree->tree;
            }
            list($group_by_data, $sem_data) = $this->get_result();
            $tmpfile = $TMP_PATH . '/' . md5(uniqid('write_excel', 1));
            // Creating a workbook
            $workbook = new Workbook($tmpfile);
            $head_format = $workbook->addformat();
            $head_format->set_size(12);
            $head_format->set_bold();
            $head_format->set_align('left');
            $head_format->set_align('vcenter');

            $head_format_merged = $workbook->addformat();
            $head_format_merged->set_size(12);
            $head_format_merged->set_bold();
            $head_format_merged->set_align('left');
            $head_format_merged->set_align('vcenter');
            $head_format_merged->set_merge();
            $head_format_merged->set_text_wrap();

            $caption_format = $workbook->addformat();
            $caption_format->set_size(10);
            $caption_format->set_align('left');
            $caption_format->set_align('vcenter');
            $caption_format->set_bold();
            //$caption_format->set_text_wrap();

            $data_format = $workbook->addformat();
            $data_format->set_size(10);
            $data_format->set_align('left');
            $data_format->set_align('vcenter');

            $caption_format_merged = $workbook->addformat();
            $caption_format_merged->set_size(10);
            $caption_format_merged->set_merge();
            $caption_format_merged->set_align('left');
            $caption_format_merged->set_align('vcenter');
            $caption_format_merged->set_bold();


            // Creating the first worksheet
            $worksheet1 = $workbook->addworksheet(_('Veranstaltungen'));
            $worksheet1->set_row(0, 20);
            $worksheet1->write_string(0, 0, mb_convert_encoding($headline, 'WINDOWS-1252') ,$head_format);
            $worksheet1->set_row(1, 20);
            $worksheet1->write_string(1, 0, mb_convert_encoding(sprintf(_(' %s Veranstaltungen gefunden %s, Gruppierung: %s'),count($sem_data),
                (($this->sem_browse_data['sset']) ? _('(Suchergebnis)') : ''),
                $this->group_by_fields[$this->sem_browse_data['group_by']]['name']), 'WINDOWS-1252'), $caption_format);

            $worksheet1->write_blank(0, 1, $head_format);
            $worksheet1->write_blank(0, 2, $head_format);
            $worksheet1->write_blank(0, 3, $head_format);

            $worksheet1->write_blank(1, 1, $head_format);
            $worksheet1->write_blank(1, 2, $head_format);
            $worksheet1->write_blank(1, 3, $head_format);

            $worksheet1->set_column(0, 0, 70);
            $worksheet1->set_column(0, 1, 25);
            $worksheet1->set_column(0, 2, 25);
            $worksheet1->set_column(0, 3, 50);

            $row = 2;

            foreach ($group_by_data as $group_field => $sem_ids) {
                switch ($this->sem_browse_data['group_by']) {
                    case 0:
                        $headline = $this->search_obj->sem_dates[$group_field]['name'];
                        break;
                    case 1:
                        if ($the_tree->tree_data[$group_field]) {
                            $headline = $the_tree->getShortPath($group_field);
                        } else {
                            $headline =  _('keine Studienbereiche eingetragen');
                        }
                        break;
                    case 3:
                        $headline = $SEM_TYPE[$group_field]['name']
                            ." ("
                            . $SEM_CLASS[$SEM_TYPE[$group_field]['class']]['name'] . ')';
                        break;
                    default:
                    $headline = $group_field;
                }
                ++$row;
                $worksheet1->write_string($row, 0 , mb_convert_encoding($headline, 'WINDOWS-1252'), $caption_format);
                $worksheet1->write_blank($row, 1, $caption_format);
                $worksheet1->write_blank($row, 2, $caption_format);
                $worksheet1->write_blank($row, 3, $caption_format);
                ++$row;
                if (is_array($sem_ids['Seminar_id'])) {
                    while(list($seminar_id,) = each($sem_ids['Seminar_id'])) {
                        $sem_name = key($sem_data[$seminar_id]['Name']);
                        $seminar_number = key($sem_data[$seminar_id]['VeranstaltungsNummer']);
                        $sem_number_start = key($sem_data[$seminar_id]['sem_number']);
                        $sem_number_end = key($sem_data[$seminar_id]['sem_number_end']);
                        if ($sem_number_start != $sem_number_end) {
                            $sem_name .= ' (' . $this->search_obj->sem_dates[$sem_number_start]['name'] . ' - ';
                            $sem_name .= ($sem_number_end == -1 ? _('unbegrenzt') : $this->search_obj->sem_dates[$sem_number_end]['name']) . ')';
                        } elseif ($this->sem_browse_data['group_by']) {
                            $sem_name .= ' (' . $this->search_obj->sem_dates[$sem_number_start]['name'] . ')';
                        }
                        //create Turnus field
                        $seminar_obj = new Seminar($seminar_id);
                        // is this sem a studygroup?
                        $studygroup_mode = SeminarCategories::GetByTypeId($seminar_obj->getStatus())->studygroup_mode;
                        if ($studygroup_mode) {
                            $sem_name = $seminar_obj->getName() . ' (' . _('Studiengruppe');
                            if ($seminar_obj->admission_prelim) $sem_name .= ', '. _('Zutritt auf Anfrage');
                            $sem_name .= ')';
                        }
                        $worksheet1->write_string($row, 0, mb_convert_encoding($sem_name, 'WINDOWS-1252'), $data_format);
                        $temp_turnus_string = $seminar_obj->getFormattedTurnus(true);
                        //Shorten, if string too long (add link for details.php)
                        if (mb_strlen($temp_turnus_string) > 245) {
                            $temp_turnus_string = mb_substr($temp_turnus_string,
                                    0, mb_strpos(
                                            mb_substr($temp_turnus_string, 245,
                                                mb_strlen($temp_turnus_string)
                                            ), ','
                                        ) + 246);
                            $temp_turnus_string .= ' ... (' . _('mehr') . ')';
                        }
                        $worksheet1->write_string($row, 1, mb_convert_encoding($seminar_number, 'WINDOWS-1252'), $data_format);
                        $worksheet1->write_string($row, 2, mb_convert_encoding($temp_turnus_string, 'WINDOWS-1252'), $data_format);

                        $doz_name = [];
                        $c = 0;
                        reset($sem_data[$seminar_id]['fullname']);
                        foreach ($sem_data[$seminar_id]['username'] as $anzahl1) {
                            if ($c == 0) {
                                list($d_name, $anzahl2) = each($sem_data[$seminar_id]['fullname']);
                                $c = $anzahl2 / $anzahl1;
                                $doz_name = array_merge($doz_name, array_fill(0, $c, $d_name));
                            }
                            --$c;
                        }
                        $doz_position = array_keys($sem_data[$seminar_id]['position']);
                        if (is_array($doz_name)){
                            if (count($doz_position) != count($doz_name)) {
                                $doz_position = range(1, count($doz_name));
                            }
                            array_multisort($doz_position, $doz_name);
                            $worksheet1->write_string($row, 3, mb_convert_encoding(join(', ', $doz_name), 'WINDOWS-1252'), $data_format);
                        }
                        ++$row;
                    }
                }
            }
            $workbook->close();
        }
        return $tmpfile;
    }

    public function get_result()
    {
        global $_fullname_sql, $SEM_TYPE, $SEM_CLASS;
        if ($this->sem_browse_data['group_by'] == 1) {
            if (!is_object($this->sem_tree)) {
                $the_tree = TreeAbstract::GetInstance('StudipSemTree', false);
            } else {
                $the_tree = $this->sem_tree->tree;
            }
            if ($this->sem_browse_data['start_item_id'] != 'root'
                    && ($this->sem_browse_data['level'] == 'vv'
                    || $this->sem_browse_data['level'] == 'sbb')) {
                $allowed_ranges = $the_tree->getKidsKids($this->sem_browse_data['start_item_id']);
                $allowed_ranges[] = $this->sem_browse_data['start_item_id'];
                $sem_tree_query = " AND sem_tree_id IN('" . join("','", $allowed_ranges) . "') ";
            }
            $add_fields = 'seminar_sem_tree.sem_tree_id AS bereich,';
            $add_query = "LEFT JOIN seminar_sem_tree ON (seminare.Seminar_id = seminar_sem_tree.seminar_id $sem_tree_query)";
        } else if ($this->sem_browse_data['group_by'] == 4){
            $add_fields = 'Institute.Name AS Institut,Institute.Institut_id,';
            $add_query = 'LEFT JOIN seminar_inst
                            ON (seminare.Seminar_id = seminar_inst.Seminar_id)
                          LEFT JOIN Institute
                            ON (Institute.Institut_id = seminar_inst.institut_id)';
        } else {
            $add_fields = '';
            $add_query = '';
        }

        $dbv = DbView::getView('sem_tree');

        $query = "
            SELECT seminare.Seminar_id, VeranstaltungsNummer, seminare.status,
                IF(seminare.visible = 0, CONCAT(seminare.Name, ' " . _('(versteckt)')
                    . "'), seminare.Name) AS Name,"
                . $add_fields
                . $_fullname_sql['full'] . " AS fullname,
                    auth_user_md5.username,"
                . $dbv->sem_number_sql . ' AS sem_number, '
                . $dbv->sem_number_end_sql . ' AS sem_number_end,
                seminar_user.position AS position, seminare.parent_course, seminare.visible
            FROM seminare
                LEFT JOIN seminar_user
                    ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status = ' . "'dozent'" . ')
                LEFT JOIN auth_user_md5
                    USING (user_id)
                LEFT JOIN user_info
                    USING (user_id) '
                . $add_query . "
            WHERE (seminare.Seminar_id IN('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "')
                OR seminare.parent_course IN ('" . join("','", array_keys($this->sem_browse_data['search_result'])) . "'))";

        // don't show Studiengruppen if user not logged in
        if (!$GLOBALS['user'] || $GLOBALS['user']->id == 'nobody') {
            $query .= " AND seminare.status != '99'";
        }

        $db = new DB_Seminar($query);
        $snap = new DbSnapshot($db);
        $group_field = $this->group_by_fields[$this->sem_browse_data['group_by']]['group_field'];
        $data_fields[0] = 'Seminar_id';
        if ($this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field']) {
            $data_fields[1] = $this->group_by_fields[$this->sem_browse_data['group_by']]['unique_field'];
        }
        $group_by_data = $snap->getGroupedResult($group_field, $data_fields);
        $sem_data = $snap->getGroupedResult('Seminar_id');

        if ($this->sem_browse_data['group_by'] == 0) {
            $group_by_duration = $snap->getGroupedResult('sem_number_end', ['sem_number', 'Seminar_id']);
            foreach ($group_by_duration as $sem_number_end => $detail) {
                if ($sem_number_end != -1
                        && ($detail['sem_number'][$sem_number_end]
                                && count($detail['sem_number']) == 1)) {
                    continue;
                }

                $current_semester_index = SemesterData::getSemesterIndexById(Semester::findCurrent()->semester_id);
                foreach (array_keys($detail['Seminar_id']) as $seminar_id) {
                    $start_sem = key($sem_data[$seminar_id]['sem_number']);
                    if ($sem_number_end == -1) {
                        if ($this->sem_number === false) {
                            $sem_number_end = $current_semester_index && isset($this->search_obj->sem_dates[$current_semester_index + 1]) ? $current_semester_index + 1 : count($this->search_obj->sem_dates) -1;
                        } else {
                            $sem_number_end = $this->sem_number[0];
                        }
                    }
                    for ($i = $start_sem; $i <= $sem_number_end; ++$i) {
                        if ($this->sem_number === false
                                || is_array($this->sem_number)
                                && in_array($i,$this->sem_number)) {
                            if ($group_by_data[$i] && !$tmp_group_by_data[$i]) {
                                foreach (array_keys($group_by_data[$i]['Seminar_id']) as $id) {
                                    $tmp_group_by_data[$i]['Seminar_id'][$id] = true;
                                }
                            }
                            $tmp_group_by_data[$i]['Seminar_id'][$seminar_id] = true;
                        }
                    }
                }
            }
            if (is_array($tmp_group_by_data)) {
                if ($this->sem_number !== false) {
                    unset($group_by_data);
                }
                foreach ($tmp_group_by_data as $start_sem => $detail) {
                    $group_by_data[$start_sem] = $detail;
                }
            }
        }

        //release memory
        unset($snap);
        unset($tmp_group_by_data);

        foreach ($group_by_data as $group_field => $sem_ids) {
            foreach ($sem_ids['Seminar_id'] as $seminar_id => $foo) {
                $name = mb_strtolower(key($sem_data[$seminar_id]['Name']));
                $name = str_replace(['ä', 'ö', 'ü'], ['ae', 'oe', 'ue'], $name);
                if (Config::get()->IMPORTANT_SEMNUMBER && key($sem_data[$seminar_id]['VeranstaltungsNummer'])) {
                    $name = key($sem_data[$seminar_id]['VeranstaltungsNummer']) . ' ' . $name;
                }
                $group_by_data[$group_field]['Seminar_id'][$seminar_id] = $name;
            }
            uasort($group_by_data[$group_field]['Seminar_id'], 'strnatcmp');
        }

        switch ($this->sem_browse_data['group_by']) {
            case 0:
                krsort($group_by_data, SORT_NUMERIC);
                break;
            case 1:
                uksort($group_by_data, function($a,$b) {
                    $the_tree = TreeAbstract::GetInstance('StudipSemTree', false);
                    $the_tree->buildIndex();
                    return $the_tree->tree_data[$a]['index'] - $the_tree->tree_data[$b]['index'];
                });
                break;
            case 3:
                uksort($group_by_data, function ($a,$b) {
                    global $SEM_CLASS,$SEM_TYPE;
                    return strnatcasecmp($SEM_TYPE[$a]['name'], $SEM_TYPE[$b]['name'])
                        ?: strnatcasecmp(
                               $SEM_CLASS[$SEM_TYPE[$a]['class']]['name'],
                               $SEM_CLASS[$SEM_TYPE[$b]["class"]]['name']
                           );
                });
                break;
            default:
                uksort($group_by_data, 'strnatcasecmp');
        }

        return [$group_by_data, $sem_data];
    }

    /**
     * Creates HTML code for a single course row. This has been extracted
     * into a separate function as that makes handling and outputting
     * course children easier.
     *
     * @param string $seminar_id a single course id to output
     * @param mixed $sem_data collected data for all found courses
     * @param bool $child call in "child mode" -> force output because here children are listed
     * @return string A HTML table row.
     */
    private function printCourseRow($seminar_id, &$sem_data, $child = false)
    {
        global $_fullname_sql, $SEM_TYPE, $SEM_CLASS;

        $row = '';

        /*
         * As we include child courses now, we need an extra check for visibility.
         * Child courses are not shown extra, but summarized under their parent if
         * the parent is part of the search result.
         */
        if (($GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)
                || key($sem_data[$seminar_id]['visible']) == 1)
                && (!$sem_data[key($sem_data[$seminar_id]['parent_course'])]
                        || $child)) {
            // create instance of seminar-object
            $seminar_obj = new Seminar($seminar_id);
            // is this sem a studygroup?
            $studygroup_mode = SeminarCategories::GetByTypeId($seminar_obj->getStatus())->studygroup_mode;

            $sem_name = $SEM_TYPE[key($sem_data[$seminar_id]['status'])]['name']
                    . ': ' . key($sem_data[$seminar_id]['Name']);
            $seminar_number = key($sem_data[$seminar_id]['VeranstaltungsNummer']);

            $visibleChildren = [];

            $row .= '<tr';
            // Set necessary classes if we are displaying subcourses.
            if ($child) {
                $row .= ' class="hidden-js subcourses subcourses-' . key($sem_data[$seminar_id]['parent_course']) . '"' ;
            }
            $row .= '>';

            if ($studygroup_mode) {
                $sem_name .= ' (' . _('Studiengruppe');
                if ($seminar_obj->admission_prelim) $sem_name .= ', ' . _('Zutritt auf Anfrage');
                $sem_name .= ')';
                $row .= '<td width="1%" class="hidden-tiny-down">';
                $row .= StudygroupAvatar::getAvatar($seminar_id)->getImageTag(Avatar::SMALL, ['title' => htmlReady($seminar_obj->getName())]);
                $row .= '</td>';
            } else {
                $sem_number_start = key($sem_data[$seminar_id]['sem_number']);
                $sem_number_end = key($sem_data[$seminar_id]['sem_number_end']);
                if ($sem_number_start != $sem_number_end) {
                    $sem_name .= ' (' . $this->search_obj->sem_dates[$sem_number_start]['name'] . ' - ';
                    $sem_name .= (($sem_number_end == -1)
                            ? _('unbegrenzt')
                            : $this->search_obj->sem_dates[$sem_number_end]['name']) . ')';
                } elseif ($this->sem_browse_data['group_by']) {
                    $sem_name .= " (" . $this->search_obj->sem_dates[$sem_number_start]['name'] . ')';
                }
                $row .= '<td width="1%" class="hidden-tiny-down">';
                $row .= CourseAvatar::getAvatar($seminar_id)->getImageTag(Avatar::SMALL, ['title' => htmlReady($seminar_obj->getName())]);
                $row .= '</td>';

            }
            $send_from_search = URLHelper::getUrl(basename($_SERVER['PHP_SELF']), ['keep_result_set' => 1, 'cid' => null]);
            $send_from_search_link = UrlHelper::getLink($this->target_url,
                    [
                        $this->target_id => $seminar_id,
                        'cid' => null,
                        'send_from_search' => 1,
                        'send_from_search_page' => $send_from_search
                    ]);
            $row .= '<td width="66%" colspan="2">';

            // Show the "more" icon only if there are visible children.
            if (count($seminar_obj->children) > 0) {

                // If you are not root, perhaps not all available subcourses are visible.
                $visibleChildren = $seminar_obj->children;
                if (!$GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)) {
                    $visibleChildren = $visibleChildren->filter(function($c) {
                        return $c->visible;
                    });
                }
                if (count($visibleChildren) > 0) {
                    $row .= Icon::create('add', 'clickable',[
                            'id' => 'show-subcourses-' . $seminar_id,
                            'title' => sprintf(_('%u Unterveranstaltungen anzeigen'), count($visibleChildren)),
                            'onclick' => "jQuery('tr.subcourses-" . $seminar_id . "').removeClass('hidden-js');jQuery(this).closest('tr').addClass('has-subcourses');jQuery(this).hide();jQuery('#hide-subcourses-" . $seminar_id . "').show();"
                        ])->asImg(12) . ' ';
                    $row .= Icon::create('remove', 'clickable',[
                            'id' => 'hide-subcourses-' . $seminar_id,
                            'style' => 'display:none',
                            'title' => sprintf(_('%u Unterveranstaltungen ausblenden'), count($visibleChildren)),
                            'onclick' => "jQuery('tr.subcourses-" . $seminar_id . "').addClass('hidden-js'); jQuery(this).closest('tr').removeClass('has-subcourses');jQuery(this).hide();jQuery('#show-subcourses-" . $seminar_id . "').show();"
                        ])->asImg(12) . ' ';
                }
            }

            $row .= '<a href="' . $send_from_search_link . '">';
            if (Config::get()->IMPORTANT_SEMNUMBER && $seminar_number) {
                $row .= htmlReady($seminar_number) . " ";
            }
            $row .= htmlReady($sem_name) . '</a><br>';
            //create Turnus field
            if ($studygroup_mode) {
                $row .= '<div style="font-size:smaller">'
                        . htmlReady(mb_substr($seminar_obj->description, 0, 100))
                        . '</div>';
            } else {
                $temp_turnus_string = $seminar_obj->getDatesExport(
                        [
                            'short' => true,
                            'shrink' => true,
                            'semester_id' => $current_semester_id
                        ]);
                //Shorten, if string too long (add link for details.php)
                if (mb_strlen($temp_turnus_string) > 70) {
                    $temp_turnus_string = htmlReady(mb_substr($temp_turnus_string, 0, mb_strpos(mb_substr($temp_turnus_string, 70, mb_strlen($temp_turnus_string)), ',') + 71));
                    $temp_turnus_string .= ' ... <a href="' . $send_from_search_link . '">(' . _('mehr') . ')</a>';
                } else {
                    $temp_turnus_string = htmlReady($temp_turnus_string);
                }
                if (!Config::get()->IMPORTANT_SEMNUMBER) {
                    $row .= '<div style="margin-left:5px;font-size:smaller">' . htmlReady($seminar_number) . '</div>';
                }
                $row .= '<div style="margin-left:5px;font-size:smaller">' . $temp_turnus_string . '</div>';
                if (count($seminar_obj->children) > 0 && count($visibleChildren) > 0) {
                    $row .= '<div style="margin-left:5px;font-size:smaller">';
                    $row .= sprintf(_('%u Unterveranstaltungen'), count($visibleChildren));
                    $row .= '</div>';
                }
            }
            $row .= '</td>';
            $row .= '<td align="right">(';
            $doz_name = [];
            $c = 0;
            reset($sem_data[$seminar_id]['fullname']);
            foreach ($sem_data[$seminar_id]['username'] as $anzahl1) {
                if ($c == 0) {
                    list($d_name, $anzahl2) = each($sem_data[$seminar_id]['fullname']);
                    $c = $anzahl2 / $anzahl1;
                    $doz_name = array_merge($doz_name, array_fill(0, $c, $d_name));
                }
                --$c;
            }
            $doz_uname = array_keys($sem_data[$seminar_id]['username']);
            $doz_position = array_keys($sem_data[$seminar_id]['position']);
            if (count($doz_name)) {
                if (count($doz_position) != count($doz_uname)) {
                    $doz_position = range(1, count($doz_uname));
                }
                array_multisort($doz_position, $doz_name, $doz_uname);
                $i = 0;
                foreach ($doz_name as $index => $value) {
                    if ($value) {  // hide dozenten with empty username
                        if ($i == 4) {
                            $row .= '... <a href="' . $send_from_search_link . '">(' . _('mehr') . ')</a>';
                            break;
                        }
                    $row .= '<a href="' . UrlHelper::getLink('dispatch.php/profile', ['username' => $doz_uname[$index]]) . '">' . htmlReady($value) . '</a>';
                        if ($i != count($doz_name) - 1) {
                            $row .= ', ';
                        }
                    }
                    ++$i;
                }
                $row .= ')</td>';
                if (Config::get()->COURSE_SEARCH_SHOW_ADMISSION_STATE) {
                    $row .= '<td>';
                    switch (self::getStatusCourseAdmission($seminar_id,
                            $seminar_obj->admission_prelim)) {
                        case 1:
                            $row .= Icon::create(
                                'info-circle',
                                Icon::ROLE_STATUS_YELLOW,
                                tooltip2(_('Eingeschränkter Zugang'))
                            );
                            break;
                        case 2:
                            $row .= Icon::create(
                                'decline-circle',
                                Icon::ROLE_STATUS_RED,
                                tooltip2(_('Kein Zugang'))
                            );
                            break;
                        default:
                            $row .= Icon::create(
                                'check-circle',
                                Icon::ROLE_STATUS_GREEN,
                                tooltip2(_('Uneingeschränkter Zugang'))
                            );
                    }
                    $row .= '</td>';
                }
                $row .= '</tr>';
            }

            // Process children.
            foreach ($seminar_obj->children as $child) {
                $row .= $this->printCourseRow($child->id, $sem_data, true);
            }

        }

        return $row;
    }


    /**
     * Returns a new navigation object corresponding to the given target and
     * name of the option. The target has two possibel values "sidebar" and
     * "course" and indicates the place where the navigation is shown.
     * The option name is the key of an entry in the array with the navigation
     * options.
     *
     * The navigation options are configured in the global configuration as an
     * array. For further details see documentation of entry
     * COURSE_SEARCH_NAVIGATION_OPTIONS in global configuration.
     *
     * This is an example with all possible options:
     *
     * {
     *     // "courses", "semtree" and "rangetree" are the "old" search options.
     *     // The link text is fixed.
     *     "courses":{
     *         "visible":true,
     *         // The target indicates where the link to this search option is
     *         // placed. Possible values are "sidebar" for a link in the sidebar
     *         // or "courses" to show a link (maybe with picture) below the
     *         // "course search".
     *         "target":"sidebar"
     *     },
     *     "semtree":{
     *         "visible":true,
     *         "target":"sidebar"
     *     },
     *     "rangetree":{
     *         "visible":false,
     *         "target":"sidebar"
     *     },
     *     // New option to acivate the search for modules and the systematic
     *     // search in studycourses, field of study and degrees.
     *     "module":{
     *         "visible":true,
     *         "target":"sidebar"
     *     },
     *     // This option shows a direct link in the sidebar to an entry (level)
     *     // in the range tree. The link text is the name of the level.
     *     "fb3_hist":{
     *         "visible":true,
     *         "target":"sidebar",
     *         "range_tree_id":"d1a07cf0c8057c664279214cc070b580"
     *     },
     *     // The same for an entry in the sem tree.
     *     "grundstudium":{
     *         "visible":true,
     *         "target":"sidebar",
     *         "sem_tree_id":"e1a07cf0c8057c664279214cc070b580"
     *     },
     *     // This shows a link in the sidebar to the course search. The text is
     *     // availlable in two languages.
     *     "vvz":{
     *         "visible":true,
     *         "target":"sidebar",
     *         "url":"dispatch.php/search/courses?level=f&option=vav",
     *         "title":{
     *             "de_DE":"Veranstaltungsverzeichnis",
     *             "en_GB":"Course Catalogue"
     *         }
     *     },
     *     // This option uses an url with search option and shows a link in the
     *     // sidebar to an entry in the range tree with all courses.
     *     "test":{
     *         "visible":true,
     *         "target":"sidebar",
     *         "url":"dispatch.php/search/courses?start_item_id=d1a07cf0c8057c664279214cc070b580&cmd=show_sem_range_tree&item_id=d1a07cf0c8057c664279214cc070b580_withkids&level=ev",
     *         "title":{
     *             "de_DE":"Historisches Institut",
     *             "en_GB":"Historical Institute"
     *         }
     *     },
     *     // This option shows a link to the sem tree with picture below the
     *     // course search (target: courses).
     *     // This is the behaviour of Stud.IP < 4.2.
     *     "csemtree":{
     *         "visible":true,
     *         "target":"courses",
     *         "url":"dispatch.php/search/courses?level=vv",
     *         "img":{
     *             "filename":"directory-search.png",
     *             "attributes":{
     *                 "size":"260@100"
     *             }
     *         },
     *         "title":{
     *             "de_DE":"Suche im Vorlesungsverzeichnis",
     *             "en_GB":"Search course directory"
     *         }
     *     },
     *     // This option shows a link to the range tree with picture below the
     *     // course search (target: courses).
     *     // This is the behaviour of Stud.IP < 4.2.
     *     "crangetree":{
     *         "visible":true,
     *         "target":"courses",
     *         "url":"dispatch.php/search/courses?level=ev",
     *         "img":{
     *             "filename":"institute-search.png",
     *             "attributes":{
     *                 "size":"260@100"
     *             }
     *         },
     *         "title":{
     *             "de_DE":"Suche in Einrichtungen",
     *             "en_GB":"Search institutes"
     *         }
     *     }
     * }
     *
     *
     * @param string $target
     * @param string $option_name
     * @return \Navigation
     */
    public static function getSearchOptionNavigation($target, $option_name = null)
    {
        // return first visible search option
        if (is_null($option_name)) {
            $options = Config::get()->COURSE_SEARCH_NAVIGATION_OPTIONS;
            foreach ($options as $name => $option) {
                if ($option['visible'] && $option['target'] == $target) {
                    return self::getSearchOptionNavigation($target, $name);
                }
            }
            return null;
        }

        $language = $_SESSION['_language'] ?: reset(array_keys(Config::get()->INSTALLED_LANGUAGES));
        $option = Config::get()->COURSE_SEARCH_NAVIGATION_OPTIONS[$option_name];
        if (!$option['visible'] || $option['target'] != $target) {
            return null;
        }
        if (!$option['url']) {
            switch ($option_name) {
                case 'courses':
                    return new Navigation(_('Veranstaltungssuche'),
                            URLHelper::getURL('dispatch.php/search/courses',
                                    [
                                        'level' => 'f',
                                        'option' => ''
                                    ], true));
                case 'semtree':
                    return new Navigation(_('Suche im Vorlesungsverzeichnis'),
                            URLHelper::getURL('dispatch.php/search/courses',
                                    [
                                        'level' => 'vv',
                                        'cmd'   => 'qs',
                                        'sset'  => '0',
                                        'option' => ''
                                    ], true));
                case 'rangetree':
                    return new Navigation(_('Suche in Einrichtungen'),
                            URLHelper::getURL('dispatch.php/search/courses',
                                    [
                                        'level' => 'ev',
                                        'cmd'   => 'qs',
                                        'sset'  => '0',
                                        'option' => ''
                                    ], true));
                case 'module':
                    return new MVVSearchNavigation(_('Suche im Modulverzeichnis'),
                            URLHelper::getURL('dispatch.php/search/module'),null, true);
            }
        } else {
            return new Navigation($option['title'][$language],
                    URLHelper::getURL($option['url'], ['option' => $option_name], true));
        }
        if ($option['sem_tree_id']) {
            $study_area = StudipStudyArea::find($option['sem_tree_id']);
            return new Navigation($study_area->name,
                    URLHelper::getURL('dispatch.php/search/courses',
                        [
                            'start_item_id' => $option['sem_tree_id'],
                            'path_id'       => $option['sem_tree_id'],
                            'cmd'           => 'show_sem_range',
                            'item_id'       => $option['sem_tree_id'] . '_withkids',
                            'level'         => 'vv',
                            'option'        => $option_name
                        ], true));
        }
        if ($option['range_tree_id']) {
            $item_name = DBManager::get()->fetchColumn('
                    SELECT `name`
                    FROM `range_tree`
                    WHERE item_id = ?',
                    [$option['range_tree_id']]);
            return new Navigation($item_name,
                    URLHelper::getURL('dispatch.php/search/courses',
                        [
                            'start_item_id' => $option['range_tree_id'],
                            'path_id'       => $option['range_tree_id'],
                            'cmd'           => 'show_sem_range_tree',
                            'item_id'       => $option['range_tree_id'] . '_withkids',
                            'level'         => 'ev',
                            'option'        => $option_name
                        ], true));
        }
    }

    /**
     * The class SemBrowse uses a vast number of variables stored in the
     * session. This function sets the default values or transfers some
     * of them to url parameters if a filter in the sidebar has been changed.
     *
     * @see SemBrowse::setClassesSelector()
     * @see SemBrowse::setSemesterSelector()
     */
    public static function transferSessionData()
    {
        if (Request::option('reset_all')) {
            $_SESSION['sem_browse_data'] = null;
        }

        $_SESSION['sem_browse_data']['qs_choose'] = Request::get('search_sem_qs_choose',
                $_SESSION['sem_browse_data']['qs_choose']);

        // simulate button clicked if semester was changed
        if (Request::option('search_sem_sem', $_SESSION['sem_browse_data']['default_sem'])
                != $_SESSION['sem_browse_data']['default_sem']) {
            $_SESSION['sem_browse_data']['default_sem'] = Request::option('search_sem_sem');
            if ($_SESSION['sem_browse_data']['sset']) {
                Request::set('search_sem_quick_search_parameter', $_SESSION['sem_browse_data']['sset']);
                Request::set('search_sem_quick_search', $_SESSION['sem_browse_data']['sset']);
                Request::set('search_sem_qs_choose', $_SESSION['sem_browse_data']['qs_choose']);
                Request::set('search_sem_category', $_SESSION['sem_browse_data']['show_class']);
                Request::set('search_sem_do_search', '1');
                Request::set('search_sem_' . md5('is_sended'), '1');
            } else {
                Request::set('search_sem_category', $_SESSION['sem_browse_data']['show_class']);
                Request::set('search_sem_sem_change', '1');
                Request::set('search_sem_sem_select', '1');
            }
        }

        // simulate button clicked if class was changed
        if (Request::option('show_class', $_SESSION['sem_browse_data']['show_class'])
                != $_SESSION['sem_browse_data']['show_class']) {
            $_SESSION['sem_browse_data']['show_class'] = Request::option('show_class');

            if ($_SESSION['sem_browse_data']['show_class']
                    && $_SESSION['sem_browse_data']['show_class'] != 'all') {
                $class = $GLOBALS['SEM_CLASS'][$_SESSION['sem_browse_data']['show_class']];
                $_SESSION['sem_browse_data']['sem_status'] = array_keys($class->getSemTypes());
            } else {
                $_SESSION['sem_browse_data']['sem_status'] = false;
            }

            if ($_SESSION['sem_browse_data']['sset']) {
                Request::set('search_sem_quick_search_parameter', $_SESSION['sem_browse_data']['sset']);
                Request::set('search_sem_quick_search', $_SESSION['sem_browse_data']['sset']);
                Request::set('search_sem_qs_choose', $_SESSION['sem_browse_data']['qs_choose']);
                Request::set('search_sem_category', $_SESSION['sem_browse_data']['show_class']);
                Request::set('search_sem_do_search', '1');
                Request::set('search_sem_' . md5('is_sended'), '1');
            } else {
                Request::set('search_sem_category', $_SESSION['sem_browse_data']['show_class']);
                Request::set('search_sem_sem_change', '1');
                Request::set('search_sem_sem_select', '1');
            }
        }

        // set default values
        if (!$_SESSION['sem_browse_data']['default_sem']) {
            $_SESSION['sem_browse_data']['default_sem'] =
                    SemesterData::GetSemesterIndexById(self::getDefaultSemester())
                        ?: 'all';
        }
        $_SESSION['sem_browse_data']['show_class'] =
                $_SESSION['sem_browse_data']['show_class'] ?: 'all';
        $_SESSION['sem_browse_data']['group_by'] =
                $_SESSION['sem_browse_data']['group_by'] ?: '0';
    }

    /**
     * Retrieves the default semester from session or calculate it considering
     * the value from SEMESTER_TIME_SWITCH.
     *
     * @return Semester The semester object of the default semester.
     */
    public static function getDefaultSemester()
    {
        $default_sem = $_SESSION['_default_sem'];
        if (!$default_sem) {
            $semester_time_switch = (int) Config::get()->getValue('SEMESTER_TIME_SWITCH');
            $current_sem = Semester::findByTimestamp(time()
                + $semester_time_switch * 7 * 24 * 60 * 60);
            $default_sem = $current_sem->id;
        }

        return $default_sem;
    }

    /**
     * Adds a widget to the sidebar to select a course class. The result set is
     * filtered by this class.
     *
     * @param string $submit_url The submit url.
     */
    public static function setClassesSelector($submit_url)
    {
        $classes_filter = new SelectWidget(_('Veranstaltungsklassen'),
                $submit_url, 'show_class');
        $classes_filter->addElement(new SelectElement('all', _('Alle'),
                ($_SESSION['sem_browse_data']['show_class'] ?: 'all') === 'all'));
        foreach ($GLOBALS['SEM_CLASS'] as $key => $val) {
            $classes_filter->addElement(new SelectElement($key, $val['name'],
                    ($_SESSION['sem_browse_data']['show_class'] == $key)));
        }
        Sidebar::Get()->addWidget($classes_filter);
    }

    /**
     * Adds a widget to the sidebar to select a semester. The result set is
     * filtered by this semester.
     *
     * @param string $submit_url The submit url.
     */
    public static function setSemesterSelector($submit_url)
    {
        $semesters = SemesterData::getSemesterArray();
        unset($semesters[0]);
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Semester'),
                $submit_url, 'search_sem_sem');
        $list->addElement(new SelectElement('all', _('Alle'),
                ($_SESSION['sem_browse_data']['default_sem']) === 'all'));
        foreach(array_reverse($semesters, true) as $i => $semester_data) {
            $list->addElement(new SelectElement(
                $i,
                $semester_data['name'],
                ($_SESSION['sem_browse_data']['default_sem'] !== 'all'
                    && intval($_SESSION['sem_browse_data']['default_sem']) === $i)
            ));
        }
        $sidebar->addWidget($list, 'filter_semester');
    }

    /**
     * Returns the admission status for a course.
     *
     * @param string $seminar_id Id of the course
     * @param bool   $prelim     State of preliminary setting
     * @return int
     */
    public static function getStatusCourseAdmission($seminar_id, $prelim)
    {
        $sql = "SELECT COUNT(`type`) AS `types`,
                       SUM(IF(`type` = 'LockedAdmission', 1, 0)) AS `type_locked`
                FROM `seminar_courseset`
	            INNER JOIN `courseset_rule` USING (`set_id`)
	            WHERE `seminar_id` = ?
                GROUP BY `set_id`";

	    $stmt = DBManager::get()->prepare($sql);
	    $stmt->execute([$seminar_id]);
	    $result = $stmt->fetch();

        if ($result['types']) {
            if ($result['type_locked']) {
                return 2;
            }
            return 1;
        }

        if ($prelim) {
            return 1;
        }
        return 0;
    }
}
