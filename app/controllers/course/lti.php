<?php
/**
 * course/lti.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */

class Course_LtiController extends StudipController
{
    /**
     * Callback function being called before an action is executed.
     */
    public function before_filter(&$action, &$args)
    {
        // these actions do not require session authentication
        if (in_array($action, ['profile', 'outcome'])) {
            return parent::before_filter($action, $args);
        }

        $this->with_session = true;
        $this->allow_nobody = false;

        parent::before_filter($action, $args);

        $this->course_id = Context::getId();
        $this->edit_perm = $GLOBALS['perm']->have_studip_perm('tutor', $this->course_id);

        if (!in_array($action, ['index', 'iframe', 'grades']) && !$this->edit_perm) {
            throw new AccessDeniedException(_('Sie besitzen keine Berechtigung, um LTI-Tools zu konfigurieren.'));
        }

        if ($action !== 'grades') {
            Navigation::activateItem('/course/lti/index');
        }

        $title = CourseConfig::get($this->course_id)->LTI_TOOL_TITLE;
        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . $title);
    }

    /**
     * Display the list of LTI content blocks.
     */
    public function index_action()
    {
        $this->lti_data_array = LtiData::findByCourse_id($this->course_id, 'ORDER BY position');

        if ($this->edit_perm) {
            $widget = Sidebar::get()->addWidget(new ActionsWidget());
            $widget->addLink(
                _('Einstellungen'),
                $this->url_for('course/lti/config'),
                Icon::create('admin')
            )->asDialog('size=auto');
            $widget->addLink(
                _('Abschnitt hinzufügen'),
                $this->url_for('course/lti/edit'),
                Icon::create('add')
            )->asDialog();

            if (LtiTool::findByDeep_linking(1)) {
                $widget->addLink(
                    _('Link aus LTI-Tool einfügen'),
                    $this->url_for('course/lti/add_link'),
                    Icon::create('add')
                )->asDialog('size=auto');
            }
        }

        Helpbar::get()->addPlainText('', _('Auf dieser Seite können Sie externe Anwendungen einbinden, sofern diese den LTI-Standard (Version 1.x) unterstützen.'));
    }

    /**
     * Display the launch form for a tool as an iframe.
     */
    public function iframe_action()
    {
        $this->launch_url  = Request::get('launch_url');
        $this->launch_data = Request::getArray('launch_data');
        $this->signature   = Request::get('signature');

        $this->set_layout(null);
    }

    /**
     * Edit the course settings.
     */
    public function config_action()
    {
        $this->title = CourseConfig::get($this->course_id)->LTI_TOOL_TITLE;
    }

    /**
     * Save the course settings.
     */
    public function save_config_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $title = trim(Request::get('title'));
        CourseConfig::get($this->course_id)->store('LTI_TOOL_TITLE', $title);

        PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
        $this->redirect('course/lti');
    }

    /**
     * Move an LTI content block (either up or down).
     *
     * @param   int $position   block position
     * @param   string $direction 'up' or 'down'
     */
    public function move_action($position, $direction)
    {
        CSRFProtection::verifyUnsafeRequest();

        if ($direction === 'up') {
            $position2 = $position - 1;
        } else {
            $position2 = $position + 1;
        }

        $lti_data = LtiData::findByCourseAndPosition($this->course_id, $position);
        $lti_data2 = LtiData::findByCourseAndPosition($this->course_id, $position2);

        if ($lti_data && $lti_data2) {
            $lti_data->position = $position2;
            $lti_data->store();

            $lti_data2->position = $position;
            $lti_data2->store();
        }

        $this->redirect('course/lti');
    }

    /**
     * Edit an LTI content block (using a dialog window).
     *
     * @param   int $position   block position (blank: create a new block)
     */
    public function edit_action($position = '')
    {
        if ($position !== '') {
            $this->lti_data = LtiData::findByCourseAndPosition($this->course_id, $position);
        }

        $this->tools = LtiTool::findAll();
    }

    /**
     * Save an LTI content block.
     *
     * @param   int $position   block position (blank: create a new block)
     */
    public function save_action($position)
    {
        CSRFProtection::verifyUnsafeRequest();

        if ($position !== '') {
            $lti_data = LtiData::findByCourseAndPosition($this->course_id, $position);
        } else {
            $lti_data = new LtiData();
            $lti_data->course_id = $this->course_id;
            $lti_data->position = LtiData::countBySQL('course_id = ?', [$this->course_id]);
        }

        $lti_data->title = trim(Request::get('title'));
        $lti_data->description = Studip\Markup::purifyHtml(Request::get('description'));
        $lti_data->tool_id = Request::int('tool_id');

        if ($lti_data->tool_id == 0) {
            $lti_data->launch_url = trim(Request::get('launch_url'));
            $options['consumer_key'] = trim(Request::get('consumer_key'));
            $options['consumer_secret'] = trim(Request::get('consumer_secret'));
            $options['send_lis_person'] = Request::int('send_lis_person', 0);
        } else {
            $lti_data->launch_url = trim(Request::get('custom_url'));
        }

        $options['custom_parameters'] = trim(Request::get('custom_parameters'));
        $options['document_target'] = Request::option('document_target', 'window');
        $lti_data->options = $options;
        $lti_data->store();

        PageLayout::postSuccess(_('Der Abschnitt wurde gespeichert.'));
        $this->redirect('course/lti');
    }

    /**
     * Delete an LTI content block.
     *
     * @param   int $position   block position
     */
    public function delete_action($position)
    {
        CSRFProtection::verifyUnsafeRequest();

        $lti_data = LtiData::findByCourseAndPosition($this->course_id, $position);
        $lti_data->delete();

        PageLayout::postSuccess(_('Der Abschnitt wurde gelöscht.'));
        $this->redirect('course/lti');
    }

    /**
     * Select a tool for adding a block via ContentItemSelectionRequest.
     */
    public function add_link_action()
    {
        $this->tools = LtiTool::findByDeep_linking(1);
    }

    /**
     * Dispatch a ContentItemSelectionRequest to a specified LTI tool.
     */
    public function select_link_action()
    {
        $tool_id = Request::int('tool_id');
        $tool = LtiTool::find($tool_id);

        $custom_parameters = explode("\n", $tool->custom_parameters);
        $content_item_return_url = $this->url_for('course/lti/save_link/' . $tool_id);

        // set up ContentItemSelectionRequest
        $lti_link = new LtiLink($tool->launch_url, $tool->consumer_key, $tool->consumer_secret);
        $lti_link->setUser($GLOBALS['user']->id, 'Instructor', $tool->send_lis_person);
        $lti_link->setCourse($this->course_id);
        $lti_link->addLaunchParameters([
            'lti_message_type' => 'ContentItemSelectionRequest',
            'accept_media_types' => 'application/vnd.ims.lti.v1.ltilink',
            'accept_presentation_document_targets' => 'iframe,window',
            'content_item_return_url' => $content_item_return_url,
            'launch_presentation_locale' => str_replace('_', '-', $_SESSION['_language']),
            'launch_presentation_document_target' => 'window'
        ]);

        foreach ($custom_parameters as $param) {
            list($key, $value) = explode('=', $param);
            if (isset($value)) {
                $lti_link->addCustomParameter(trim($key), trim($value));
            }
        }

        $this->launch_url = $lti_link->getLaunchURL();
        $this->launch_data = $lti_link->getBasicLaunchData();
        $this->signature = $lti_link->getLaunchSignature($this->launch_data);

        $this->set_layout(null);
        $this->render_action('iframe');
    }

    /**
     * Create a new LTI content block for the specified tool id.
     *
     * @param   int $tool_id    tool id
     */
    public function save_link_action($tool_id)
    {
        require_once 'vendor/oauth-php/library/OAuthRequestVerifier.php';

        $tool = LtiTool::find($tool_id);
        $lti_msg = Request::get('lti_msg');
        $lti_errormsg = Request::get('lti_errormsg');
        $content_items = Request::get('content_items');
        $content_items = studip_json_decode($content_items);

        OAuthStore::instance('PDO', [
            'dsn' => 'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] . ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'],
            'username' => $GLOBALS['DB_STUDIP_USER'],
            'password' => $GLOBALS['DB_STUDIP_PASSWORD']
        ]);

        $oarv = new OAuthRequestVerifier();
        $oarv->verifySignature($tool->consumer_secret, false, false);

        if (is_array($content_items) && count($content_items['@graph'])) {
            // we only support selecting a single content item
            $item = $content_items['@graph'][0];

            $lti_data = new LtiData();
            $lti_data->course_id = $this->course_id;
            $lti_data->position = LtiData::countBySQL('course_id = ?', [$this->course_id]);
            $lti_data->title = (string) $item['title'];
            $lti_data->description = Studip\Markup::purifyHtml(Studip\Markup::markAsHtml($item['text']));
            $lti_data->tool_id = $tool_id;
            $lti_data->launch_url = (string) $item['url'];

            if (is_array($item['custom'])) {
                foreach ($item['custom'] as $key => $value) {
                    $custom_parameters .= $key . '=' . $value . "\n";
                }

                $options['custom_parameters'] = $custom_parameters;
            }

            if (isset($item['placementAdvice']['presentationDocumentTarget'])) {
                $options['document_target'] = $item['placementAdvice']['presentationDocumentTarget'];
            }

            $lti_data->options = $options;
            $lti_data->store();

            PageLayout::postSuccess($lti_msg ?: _('Der Link wurde als neuer Abschnitt hinzugefügt.'));
        }

        if ($lti_errormsg) {
            PageLayout::postError($lti_errormsg);
        }

        $this->redirect('course/lti');
    }

    /**
     * Return an LtiLink object for the configured LTI content block.
     *
     * @param   LtiData $lti_data data of LTI content block
     *
     * @return  LtiLink  LTI link representation
     */
    public function getLtiLink($lti_data)
    {
        $launch_url = $lti_data->getLaunchURL();
        $consumer_key = $lti_data->getConsumerKey();
        $consumer_secret = $lti_data->getConsumerSecret();

        $roles = $this->edit_perm ? 'Instructor' : 'Learner';
        $custom_parameters = explode("\n", $lti_data->getCustomParameters());
        // posted form data must always use CR LF
        $description = str_replace("\n", "\r\n", kill_format($lti_data->description));
        $lis_outcome_service_url = $this->url_for('course/lti/outcome/' . $lti_data->id, ['cid' => null]);
        $tc_profile_url = $this->url_for('course/lti/profile/' . $lti_data->id, ['cid' => null]);

        // set up launch request
        $lti_link = new LtiLink($launch_url, $consumer_key, $consumer_secret);
        $lti_link->setResource($lti_data->id, $lti_data->title, $description);
        $lti_link->setUser($GLOBALS['user']->id, $roles, $lti_data->getSendLisPerson());
        $lti_link->setCourse($lti_data->course_id);
        $lti_link->addVariable('ToolConsumerProfile.url', $tc_profile_url);
        $lti_link->addLaunchParameters([
            'launch_presentation_locale' => str_replace('_', '-', $_SESSION['_language']),
            'launch_presentation_document_target' => $lti_data->options['document_target'],
            'lis_outcome_service_url' => $lis_outcome_service_url,
            'lis_result_sourcedid' => $GLOBALS['user']->id
        ]);

        foreach ($custom_parameters as $param) {
            list($key, $value) = explode('=', $param);
            if (isset($value)) {
                $lti_link->addCustomParameter(trim($key), trim($value));
            }
        }

        return $lti_link;
    }

    /**
     * Return the LTI consumer profile in standard JSON format.
     *
     * @param   int $id    link id
     */
    public function profile_action($id)
    {
        $profile = [
            '@context' => ['http://purl.imsglobal.org/ctx/lti/v2/ToolConsumerProfile'],
            '@type' => 'ToolConsumerProfile',
            'lti_version' => 'LTI-1p0',
            'guid' => md5(Config::get()->STUDIP_INSTALLATION_ID),
            'product_instance' => [
                'guid' => Config::get()->STUDIP_INSTALLATION_ID,
                'product_info' => [
                    'product_name' => ['default_value' => 'Stud.IP'],
                    'product_version' => $GLOBALS['SOFTWARE_VERSION'],
                    'product_family' => [
                        'code' => 'studip',
                        'vendor' => [
                            'code' => 'studip.de',
                            'vendor_name' => ['default_value' => 'Stud.IP e.V.'],
                            'website' => 'https://www.studip.de/',
                            'timestamp' => date(c)
                        ]
                    ]
                ],
                'service_owner' => [
                    'service_owner_name' => ['default_value' => Config::get()->UNI_NAME_CLEAN],
                    'description' => ['default_value' => $GLOBALS['UNI_INFO']],
                    'support' => ['email' => $GLOBALS['UNI_CONTACT']],
                    'timestamp' => date(c)
                ]
            ],
            'capability_offered' => [
                'basic-lti-launch-request',
                'ContentItemSelectionRequest',
                'Context.id',
                'Context.label',
                'Context.title',
                'Context.type',
                'CourseSection.courseNumber',
                'CourseSection.credits',
                'CourseSection.dept',
                'CourseSection.label',
                'CourseSection.longDescription',
                'CourseSection.maxNumberofStudents',
                'CourseSection.numberofStudents',
                'CourseSection.shortDescription',
                'CourseSection.sourcedId',
                'CourseSection.title',
                'Person.email.primary',
                'Person.name.family',
                'Person.name.full',
                'Person.name.given',
                'Person.name.prefix',
                'Person.name.suffix',
                'Person.sourcedId',
                'Person.webaddress',
                'ResourceLink.description',
                'ResourceLink.id',
                'ResourceLink.title',
                'ToolConsumerProfile.url',
                'User.id',
                'User.image',
                'User.username'
            ],
            'service_offered' => [
                '@type' => 'RestService',
                '@id' => 'tcp:Outcomes.LTI1',
                'endpoint' => $this->url_for('course/lti/outcome/' . $id),
                'format' => ['application/vnd.ims.lti.v1.outcome+xml'],
                'action' => ['POST']
            ]
        ];

        $this->set_content_type('application/vnd.ims.lti.v2.toolconsumerprofile+json');
        $this->render_text(json_encode($profile));
    }

    /**
     * Handle outcome service callback request by the LTI tool.
     *
     * @param   int $id    link id
     */
    public function outcome_action($id)
    {
        require_once 'vendor/oauth-php/library/OAuthRequestVerifier.php';

        $lti_data = LtiData::find($id);

        OAuthStore::instance('PDO', [
            'dsn' => 'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] . ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'],
            'username' => $GLOBALS['DB_STUDIP_USER'],
            'password' => $GLOBALS['DB_STUDIP_PASSWORD']
        ]);

        $oarv = new OAuthRequestVerifier();
        $oarv->verifySignature($lti_data->getConsumerSecret(), false, false);

        // fetch and parse POST data
        $message = file_get_contents('php://input');
        $envelope = new SimpleXMLElement($message);
        $header = current($envelope->imsx_POXHeader->children());
        $body = current($envelope->imsx_POXBody->children());

        $message_id = trim($header->imsx_messageIdentifier);
        $operation = $body->getName();
        $user_id = trim($body->resultRecord->sourcedGUID->sourcedId);
        $grade = new LtiGrade([$id, $user_id]);

        $this->message_id = uniqid();
        $this->message_ref = $message_id;
        $this->status_severity = 'status';
        $this->status_code = 'success';
        $this->operation = $operation;

        if (!CourseMember::exists([$lti_data->course_id, $user_id])) {
            $this->status_severity = 'error';
            $this->status_code = 'failure';
            $this->description = 'incorrect sourcedId: ' . $user_id;
        } else if ($operation === 'readResultRequest') {
            if ($grade->isNew()) {
                $this->status_severity = 'error';
                $this->status_code = 'failure';
                $this->description = 'no score found for: ' . $user_id;
            } else {
                $this->score = $grade->score;
                $this->description = 'score has been read';
            }
        } else if ($operation === 'replaceResultRequest') {
            $grade->score = (float) $body->resultRecord->result->resultScore->textString;
            $grade->store();
            $this->description = 'score has been updated';
        } else if ($operation === 'deleteResultRequest') {
            $grade->delete();
            $this->description = 'score has been deleted';
        } else {
            $this->status_severity = 'error';
            $this->status_code = 'unsupported';
            $this->description = 'operation not supported: ' . $operation;
        }

        $this->set_content_type('text/xml; charset=UTF-8');
        $this->set_layout(null);
    }

    /**
     * Display the (simple) LTI gradebook.
     */
    public function grades_action()
    {
        Navigation::activateItem('/course/lti/grades');

        $this->lti_data_array = LtiData::findByCourse_id($this->course_id, 'ORDER BY position');

        if ($this->edit_perm) {
            $this->desc = Request::int('desc');
            $this->members = CourseMember::findByCourseAndStatus($this->course_id, 'autor');

            if ($this->desc) {
                $this->members = array_reverse($this->members);
            }

            $widget = Sidebar::get()->addWidget(new ExportWidget());
            $widget->addLink(
                _('Ergebnisse exportieren'),
                $this->url_for('course/lti/export_grades'),
                Icon::create('download')
            );
        } else {
            $this->render_action('grades_user');
        }

        Helpbar::get()->addPlainText('', _('Auf dieser Seite können Sie die Ergebnisse sehen, die von LTI-Tools zurückgemeldet wurden.'));
    }

    /**
     * Export grades from the gradebook in CSV format.
     */
    public function export_grades_action()
    {
        $lti_data_array = LtiData::findByCourse_id($this->course_id, 'ORDER BY position');

        $columns = [_('Nachname'), _('Vorname')];

        // add one column for each LTI tool block
        foreach ($lti_data_array as $lti_data) {
            $columns[] = $lti_data->title;
        }

        $data = [$columns];
        setlocale(LC_NUMERIC, $_SESSION['_language'] . '.UTF-8');

        foreach (CourseMember::findByCourseAndStatus($this->course_id, 'autor') as $member) {
            $row = [$member->nachname, $member->vorname];

            foreach ($lti_data_array as $lti_data) {
                if ($grade = $lti_data->grades->findOneBy('user_id', $member->user_id)) {
                    $row[] = (float) $grade->score;
                } else {
                    $row[] = '';
                }
            }

            $data[] = $row;
        }

        $filename = Context::get()->name . ' - ' . _('Ergebnisse') . '.csv';
        $this->render_csv($data, $filename);
    }
}
