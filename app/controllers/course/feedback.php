<?php
/**
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category Stud.IP
 * @since 4.5
 */

class Course_FeedbackController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $this->course_id = Context::getId();

        $this->admin_perm   = Feedback::hasAdminPerm($this->course_id);
        $this->create_perm  =  Feedback::hasCreatePerm($this->course_id);

        if ($this->admin_perm) {
            Navigation::activateItem('/course/feedback/index');
            PageLayout::setTitle(sprintf(_('%s - Feedback'), Context::getHeaderLine()));
        }
    }

    /* index all feedback for course */
    public function index_action()
    {
        if ($this->admin_perm) {
            Helpbar::get()->addPlainText('', _('Auf dieser Seite werden sämtliche Feedback Elemente der Veranstaltung angezeigt'));

            $this->feedback_elements  = FeedbackElement::findBySQL('course_id = ? ORDER BY chdate DESC', [$this->course_id]);

            $widget = Sidebar::get()->addWidget(new ActionsWidget());

            $widget->addLink(
                    _('Einstellungen'),
                    $this->url_for('course/feedback/config'),
                    Icon::create('admin')
                )->asDialog('size=auto');
            if ($this->create_perm) {
                $widget->addLink(
                    _('Neues Feedback-Element'),
                    $this->url_for('course/feedback/create_form'),
                    Icon::create('star+add')
                )->asDialog();
            }
        }
    }

    public function create_form_action($range_id = null, $range_type = null)
    {
        if (!$this->create_perm) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Neues Feedback-Element anlegen'));
        if ($range_id === null && $range_type === null) {
            $range_id = $this->course_id;
            $range_type = 'Course';
        }
        // default settings for new feedback element
        $this->feedback = FeedbackElement::build([
            'range_id'          => $range_id,
            'range_type'        => $range_type,
            'results_visible'   => 1,
            'commentable'       => 1,
            'mode'              => 1
        ]);
    }

    /* create feedback for specific object */
    public function create_action($range_id, $range_type)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!Feedback::hasRangeAccess($range_id, $range_type)) {
            throw new AccessDeniedException();
        } elseif ($this->create_perm) {
            if(Request::get('comment_only') === 1) {
                $mode = 0;
                $commentable = 1;
            } else {
                $mode           = intval(Request::get('mode'));
                $commentable    = intval(Request::get('commentable'));
            }
            $feedback = FeedbackElement::build([
                'range_id'          => $range_id,
                'range_type'        => $range_type,
                'user_id'           => $GLOBALS['user']->id,
                'course_id'         => $this->course_id,
                'question'          => trim(Request::get('question')),
                'description'       =>  Studip\Markup::purifyHtml(Request::get('description')),
                'results_visible'   => intval(Request::get('results_visible')),
                'commentable'       => $commentable,
                'mode'              => $mode
            ]);
            $feedback->store();
            PageLayout::postSuccess(_('Feedback-Element erfolgreich angelegt'));
        } else {
            PageLayout::postError(_('Sie haben keine Berechtigung, an dieser Stelle ein Feedback-Element anzulegen.'));
        }
        $this->redirect($feedback->getRange()->getRangeUrl());
    }

    /* edit feedback form */
    public function edit_form_action($id)
    {
        if (!$this->admin_perm) {
            throw new AccessDeniedException();
        }
        $this->edit_action  = true;
        $this->feedback     = FeedbackElement::find($id);
    }

    /* edit feedback */
    public function edit_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$this->admin_perm) {
            throw new AccessDeniedException();
        }
        $feedback = FeedbackElement::find($id);
        $feedback->question = trim(Request::get('question'));
        $feedback->description = Studip\Markup::purifyHtml(Request::get('description'));
        $feedback->results_visible = intval(Request::get('results_visible'));
        $feedback->store();

        PageLayout::postSuccess(_('Änderungen gespeichert'));
        $this->redirect($feedback->getRange()->getRangeUrl());
    }

    public function delete_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$this->admin_perm) {
            throw new AccessDeniedException();
        }
        $feedback   = FeedbackElement::find($id);
        $url        = $feedback->getRange()->getRangeUrl();
        if ($feedback->delete()) {
            PageLayout::postSuccess(_('Das Feedback-Element und dazugehörige Einträge wurden gelöscht.'));
        } else {
            PageLayout::postError(_('Das Feedback-Element konnte nicht gelöscht werden.'));
        }
        $this->redirect($url);
    }

    public function config_action()
    {
        $this->create_perm_level    = CourseConfig::get($this->course_id)->FEEDBACK_CREATE_PERM;
        $this->admin_perm_level     = CourseConfig::get($this->course_id)->FEEDBACK_ADMIN_PERM;

        if (Request::submitted('save') && $this->admin_perm) {
            CSRFProtection::verifyUnsafeRequest();

            $create_perm_level = trim(Request::get('create_perm_level'));
            $admin_perm_level = trim(Request::get('admin_perm_level'));

            CourseConfig::get($this->course_id)->store('FEEDBACK_CREATE_PERM', $create_perm_level);
            CourseConfig::get($this->course_id)->store('FEEDBACK_ADMIN_PERM', $admin_perm_level);

            PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
            $this->redirect('course/feedback');
        } elseif (Request::submitted('save') && !$this->admin_perm) {
            PageLayout::postError(_('Sie haben keine Berechtigung, die Einstellungen zu ändern.'));
        }
    }

    /* view feedback element(s) for specific range */
    public function index_for_action($range_id, $range_type)
    {
        $this->range_id     = $range_id;
        $this->range_type   = $range_type;
        if (!Feedback::hasRangeAccess($this->range_id, $this->range_type)) {
            throw new AccessDeniedException();
        }
        if ($this->create_perm) {
            $widget = Sidebar::get()->addWidget(new ActionsWidget());
            $widget->addLink(
                _('Neues Feedback-Element'),
                $this->url_for('course/feedback/create_form/' . $this->range_type . '/' . $this->range_id),
                Icon::create('add')
            )->asDialog('size=auto');
        }
        $this->feedback_elements =  FeedbackElement::findBySQL(
                                        'range_id = ? AND range_type = ?  ORDER BY mkdate DESC',
                                        [$this->range_id,
                                        $this->range_type]
                                    );
    }

    /* view one feedback element */
    public function view_action($id)
    {
        $this->feedback = FeedbackElement::find($id);
        if (!Feedback::hasRangeAccess($this->feedback->range_id, $this->feedback->range_type)) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(sprintf(_('Feedback: %s'), $this->feedback->question));
        if ($this->admin_perm) {
            $widget = Sidebar::get()->addWidget(new ActionsWidget());
            $widget->addLink(
                _('Bearbeiten'),
                $this->url_for('course/feedback/edit_form/' . $id),
                Icon::create('edit')
            )->asDialog('size=auto');
            $widget->addLink(
                _('Löschen'),
                $this->url_for('course/feedback/delete/' . $id),
                Icon::create('trash')
            );
        }
        $this->render_template('course/feedback/_feedback');
    }

    public function entry_add_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $this->feedback = FeedbackElement::find($id);
        if (!Feedback::hasRangeAccess($this->feedback->range_id, $this->feedback->range_type)) {
            throw new AccessDeniedException();
        }
        if ($this->feedback->isFeedbackable()) {
            $rating = intval(Request::get('rating'));
            if ($rating == 0) {
                $rating = 1;
            }
            $entry =  FeedbackEntry::build([
                'feedback_id'   => $this->feedback->id,
                'user_id'       => $GLOBALS['user']->id,
                'rating'        => $rating,
                'comment'       => trim(Request::get('comment'))
            ]);
            $entry->store();
            PageLayout::postSuccess(_('Feedback gespeichert'));
        } else {
            PageLayout::postError(_('Feedback konnte nicht gespeichert werden'));
        }
        $this->redirect($this->feedback->getRange()->getRangeUrl());
    }

    public function entry_edit_form_action($entry_id)
    {
        $this->entry = FeedbackEntry::find($entry_id);
        if (!$this->entry->isEditable()) {
            throw new AccessDeniedException();
        }
        $this->feedback = $this->entry->feedback;
    }

    public function entry_edit_action($entry_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $entry = FeedbackEntry::find($entry_id);
        if (!$entry->isEditable()) {
            throw new AccessDeniedException();
        }
        $rating = intval(Request::get('rating'));
            if ($rating == 0) {
                $rating = 1;
            }
        $entry->comment = trim(Request::get('comment'));
        $entry->rating  = $rating;
        $entry->store();
        PageLayout::postSuccess(_('Änderungen gespeichert'));
        $this->redirect($entry->feedback->getRange()->getRangeUrl());
    }

    public function entry_delete_action($entry_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $entry  = FeedbackEntry::find($entry_id);
        $url    = $entry->feedback->getRange()->getRangeUrl();
        if ($entry->delete()) {
            PageLayout::postSuccess(_('Das Feedback wurde erfolgreich gelöscht.'));
        } else {
            PageLayout::postError(_('Das Feedback konnte nicht gelöscht werden'));
        }
        $this->redirect($url);
    }
}
