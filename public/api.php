<?php

/** @file
 *
 * Diese Datei stellt den Ausgangspunkt für alle Zugriffe auf die
 * RESTful Web Services von Stud.IP dar.
 * Grob betrachtet läuft das Routings so ab:
 *
 * Ein HTTP-Request geht ein. Falls dort eine inkompatible Version der
 * REST-API verlangt wird, bricht das Skript ab. Die Authentifizierung
 * wird durchgeführt. Bei Erfolg wird die PATH_INFO und die HTTP
 * Methode im Router verwendet, um die passende Funktion zu
 * finden. Der Router liefert in jedem Fall ein Response-Objekt
 * zurück, dass dann anschließende ausgegeben wird, d.h. die Header
 * werden gesendet und dann das Ergebnis ausgegeben oder gestreamt.
 */


namespace {
    require_once '../lib/bootstrap.php';

    page_open(['sess' => 'Seminar_Session',
                    'auth' => 'Seminar_Default_Auth',
                    'perm' => 'Seminar_Perm',
                    'user' => 'Seminar_User']);
}

namespace RESTAPI {
    use User, Seminar_Auth, Seminar_User, Seminar_Perm, Config;

    // A potential api exception will lead to an according response with the
    // exception code and name as the http status.
    try {
        if (!Config::get()->API_ENABLED) {
            throw new RouterException(503, 'REST API is not available');
        }

        require 'lib/bootstrap-api.php';

        $uri = $_SERVER['PATH_INFO'];

        // Check version
        if (defined('RESTAPI\\VERSION') && preg_match('~^/v(\d+)~i', $uri, $match)) {
            $version = $match[1];
            if ($version != VERSION) {
                throw new RouterException(400, 'Version not supported');
            }

            $uri = mb_substr($uri, mb_strlen($match[0]));
            header('X-API-Version: ' . VERSION);
        }

        // Get router instance
        $router = Router::getInstance();

        $user_id = setupAuth($router);

        // Actual dispatch
        $response = $router->dispatch($uri);

        // Tear down
        if ($user_id) {
            restoreLanguage();
        }

        // Send output
        $response->output();

    } catch (RouterException $e) {
        $status = sprintf('%s %u %s',
                          $_SERVER['SERVER_PROTOCOL'] ?: 'HTTP/1.1',
                          $e->getCode(),
                          $e->getMessage());
        $status = trim($status);
        if (!headers_sent()) {
            if ($e->getCode() === 401) {
                header('WWW-Authenticate: Basic realm="' . Config::get()->STUDIP_INSTALLATION_ID . '"');
            }
            header($status, true, $e->getCode());
            echo $status;
        } else {
            echo $status;
        }
    } catch (\Exception $e) {
        header('Content-Type: application/json; charset=UTF-8');
        header("{$_SERVER['SERVER_PROTOCOL']} 500 {$e->getMessage()}");
        echo $GLOBALS['template_factory']->render('json_exception', [
            'exception' => $e,
            'status'    => 500,
        ]);
    }

    function setupAuth($router)
    {
        // Detect consumer
        $consumer = Consumer\Base::detectConsumer();
        if (!$consumer) {
            throw new RouterException(401, 'Unauthorized (no consumer)');
        }

        // Set authentication if present
        if ($user = $consumer->getUser()) {
            // Skip fake authentication if user is already logged in
            if ($GLOBALS['user']->id !== $user->id) {

                $GLOBALS['auth'] = new Seminar_Auth();
                $GLOBALS['auth']->auth = [
                    'uid'   => $user->user_id,
                    'uname' => $user->username,
                    'perm'  => $user->perms,
                ];

                $GLOBALS['user'] = new Seminar_User($user->user_id);

                $GLOBALS['perm'] = new Seminar_Perm();
                $GLOBALS['MAIL_VALIDATE_BOX'] = false;
            }
            setTempLanguage($GLOBALS['user']->id);
        }

        return $consumer->getUser();
    }

}
