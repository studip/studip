<?php
/*
 * Copyright (c) 2011  Rasmus Fuhse
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Controller called by the main periodical ajax-request. It collects data,
 * converts the textstrings to utf8 and returns it as a json-object to the
 * internal javascript-function "STUDIP.JSUpdater.process(json)".
 */
class JsupdaterController extends AuthenticatedController
{
    // Allow nobody to prevent login screen
    // Refers to http://develop.studip.de/trac/ticket/4771
    protected $allow_nobody = true;

    /**
     * Checks whether we have a valid logged in user,
     * send "Forbidden" otherwise
     *
     * @param String $action The action to perform
     * @param Array  $args   Potential arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Check for a valid logged in user (only when an ajax request occurs)
        if (Request::isXhr() && (!is_object($GLOBALS['user']) || $GLOBALS['user']->id === 'nobody')) {
            $this->response->set_status(403);
            $action = 'nop';
        }
    }

    /**
     * Does and renders absolute nothing.
     */
    public function nop_action()
    {
        $this->render_nothing();
    }

    /**
     * Main action that returns a json-object like
     * {
     *  'js_function.sub_function': data,
     *  'anotherjs_function.sub_function': moredata
     * }
     * This action is called by STUDIP.JSUpdater.poll and the result processed
     * the internal STUDIP.JSUpdater.process method
     */
    public function get_action()
    {
        $data = UpdateInformation::getInformation();
        $data = array_merge($data, $this->coreInformation());

        $this->set_content_type('application/json;charset=utf-8');
        $this->render_text(json_encode($data));
    }

    /**
     * Marks a personal notification as read by the user so it won't be displayed
     * in the list in the header.
     * @param string $id : hash-id of the notification
     */
    public function mark_notification_read_action($id)
    {
        if ($id === 'all') {
            PersonalNotifications::markAllAsRead();
        } else {
            PersonalNotifications::markAsRead($id);
        }

        $url = false;
        if ($id === 'all') {
            $url = Request::get('return_to');
        } elseif (!Request::isXhr() || Request::isDialog()) {
            $notification = new PersonalNotifications($id);
            $url = $notification->url;
        }

        if ($url) {
            $this->redirect(URLHelper::getURL(TransformInternalLinks($url)));
        } else {
            $this->render_nothing();
        }
    }

    /**
     * Sets the background-color of the notification-number to blue, so it does
     * not annoy the user anymore. But he/she is still able to see the notificaion-list.
     * Just sets a unix-timestamp in the user-config NOTIFICATIONS_SEEN_LAST_DATE.
     */
    public function notifications_seen_action()
    {
        UserConfig::get($GLOBALS['user']->id)->store('NOTIFICATIONS_SEEN_LAST_DATE', time());
        $this->render_text(time());
    }

    /**
     * SystemPlugins may call UpdateInformation::setInformation to set information
     * to be sent via ajax to the main request. Core-functionality-data should be
     * collected and set here.
     * @return array: array(array('js_function' => $data), ...)
     */
    protected function coreInformation()
    {
        $data = [];
        if (PersonalNotifications::isActivated()) {
            $notifications = PersonalNotifications::getMyNotifications();
            if ($notifications && count($notifications)) {
                $ret = [];
                foreach ($notifications as $notification) {
                    $info = $notification->toArray();
                    $info['html'] = $notification->getLiElement();
                    $ret[] = $info;
                }
                $data['PersonalNotifications.newNotifications'] = $ret;
            } else {
                $data['PersonalNotifications.newNotifications'] = [];
            }
        }
        $page_info = Request::getArray("page_info");
        if (mb_stripos(Request::get("page"), "dispatch.php/messages") !== false) {
            $messages = Message::findNew(
                $GLOBALS["user"]->id,
                $page_info['Messages']['received'],
                $page_info['Messages']['since'],
                $page_info['Messages']['tag']
            );
            $template_factory = $this->get_template_factory();
            foreach ($messages as $message) {
                $data['Messages.newMessages']['messages'][$message->getId()] = $template_factory
                        ->open("messages/_message_row.php")
                        ->render(compact("message") + ['controller' => $this]);
            }
        }
        if (is_array($page_info['Questionnaire']['questionnaire_ids'])) {
            foreach ($page_info['Questionnaire']['questionnaire_ids'] as $questionnaire_id) {
                $questionnaire = new Questionnaire($questionnaire_id);
                if ($questionnaire->latestAnswerTimestamp() > $page_info['Questionnaire']['last_update']) {
                    $template = $this->get_template_factory()->open("questionnaire/evaluate");
                    $template->set_layout(null);
                    $template->set_attribute("questionnaire", $questionnaire);
                    $data['Questionnaire.updateQuestionnaireResults'][$questionnaire->getId()] = [
                        'html' => $template->render()
                    ];
                }
            }
        }
        return $data;
    }
}
