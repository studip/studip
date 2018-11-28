<?php
/*
* Session Management for PHP3
*
* Copyright (c) 1998-2000 NetUSE AG
*                    Boris Erdmann, Kristian Koehntopp
*
*
*/

function page_open($feature) {

    # enable sess and all dependent features.
    if (isset($feature["sess"])) {
        $GLOBALS['sess'] = new $feature["sess"];
        $GLOBALS['sess']->start();

        # the auth feature depends on sess
        if (isset($feature["auth"])) {

            if (is_object($_SESSION['auth'])) {
                $_SESSION['auth'] = $_SESSION['auth']->check_feature($feature["auth"]);
            } else {
                $_SESSION['auth'] = new $feature["auth"];
            }
            $_SESSION['auth']->start();

            $GLOBALS['auth'] =& $_SESSION['auth'];

            # the perm feature depends on auth and sess
            if (isset($feature["perm"])) {

                if (!is_object($GLOBALS['perm'])) {
                    $GLOBALS['perm'] = new $feature["perm"];
                }
            }

            # the user feature depends on auth and sess
            if (isset($feature["user"])) {

                if (!is_object($GLOBALS['user'])) {

                    $GLOBALS['user'] = new $feature["user"]($GLOBALS['auth']->auth["uid"]);
                }
            }
        }
    }
}

function page_close() {
    try {
        NotificationCenter::postNotification('PageCloseWillExecute', null);
    } catch (NotificationVetoException $e) {
        return;
    }
    if (is_object($GLOBALS['sess'])) {
        @session_write_close();
    }

    if (is_object($GLOBALS['user'])) {
        $GLOBALS['user']->set_last_action();
    }
    NotificationCenter::postNotification('PageCloseDidExecute', null);
}
