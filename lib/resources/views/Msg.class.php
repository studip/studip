<?
# Lifter002: TODO
# Lifter007: TEST
# Lifter003: DONE
# Lifter010: TODO
/**
* Msg.class.php
*
* creates messages
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  resources
* @module       Msg.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Msg.class.php
// erzeugt Fehlermeldungen und andere Ausgaben
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

/*****************************************************************************
Msg, class for all the msg stuff
/*****************************************************************************/

class Msg {
    var $msg;
    var $codes=[];
    var $params;

    /**
     * Constructor for resource-messages. Loads lib/resources/views/msg_resources.inc.php
     */
    function __construct() {
         include "lib/resources/views/msgs_resources.inc.php";
    }

    /**
     * Adds the message defined by the submitted code to a queue
     *
     * @param int $msg_code a number referencing to a message in lib/resources/views/msg_resources.inc.php
     * @param mixed $params an array of msg-paramaters
     *
     */
    function addMsg($msg_code, $params='') {
        $this->codes[]=$msg_code;
        if (is_array($params)) {
            $this->params[] = $params;
        } else
            $this->params[] = [];

    }

    /**
     * Checks if there are any messages in the queue
     *
     * @returns bool true if messages have been queued, false otherwise
     */
    function checkMsgs() {
        if ($this->codes)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Displays all queued messages
     *
     * @param string $view_mode can be "line" for embedded messages, "window" for separate messages
     */
    function displayAllMsg($view_mode = "line") {
        if ( is_array( $this->codes ) ) {
            $messages = [];

            // sort message by type (error, info, success) to show them bundled
            foreach( $this->codes as $key => $message_id ) {
                $messages[ $this->msg[$message_id]['mode'] ][] = vsprintf( $this->msg[$message_id]['msg'], $this->params[$key] );
            }

            // messages alone in the wild
            if ($view_mode == 'window') {
                // template with studip-layout surrounding the message
                $template = $GLOBALS['template_factory']->open('resources/msg_window');
                $template->set_layout('layouts/base');

                // pass messages to template and render it
                $template->set_attribute('messages', $messages);
                $template->set_attribute('title', $this->msg[$this->codes[0]]["titel"]);
                echo $template->render();

            // "normal" messages
            } else if ($view_mode == 'line') {
                foreach ($messages as $type => $msg_array) {
                    echo MessageBox::$type( implode('<br>', $msg_array ));
                }
            }
        }
    }

    /**
     * Display a single message
     *
     * @param int $msg_code a number referencing to a message in lib/resources/views/msg_resources.inc.php
     * @param string $view_mode can be "line" for embedded messages, "window" for separate messages
     * @param mixed $params an array of paramaters to be placed into the message
     *
     */
    function displayMsg($msg_code, $view_mode = "line", $params=[]) {
        // messages alone in the wild
        if ($view_mode == "window") {
            $message[$this->msg[$msg_code]['mode']][] = vsprintf($this->msg[$msg_code]['msg'], $params);

            // template with studip-layout surrounding the message
            $template = $GLOBALS['template_factory']->open('resources/msg_window');
            $template->set_layout('layouts/base');

            // pass messages to template and render it
            $template->set_attribute('messages', $message );
            $template->set_attribute('title', $this->msg[$msg_code]["titel"]);
            echo $template->render();

        }

        // "normal" messages
        else {
            $type = $this->msg[$msg_code]["mode"];
            echo MessageBox::$type( vsprintf($this->msg[$msg_code]["msg"], $params) );
        }
    }
}
