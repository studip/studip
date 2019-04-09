<?php

require_once 'lib/bootstrap-api.php';

/**
 *
 **/
class Api_OauthController extends StudipController
{
    /**
     *
     **/
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        # initialize Stud.IP-Session
        page_open(['sess' => 'Seminar_Session',
                        'auth' => 'Seminar_Default_Auth',
                        'perm' => 'Seminar_Perm',
                        'user' => 'Seminar_User']);

        $this->set_layout(null);
    }

    /**
     *
     **/
    public function index_action()
    {
        $this->render_text('TODO');
    }

    /**
     *
     **/
    public function request_token_action()
    {
        try {
            $server = new OAuthServer();
            $token = $server->requestToken();
            $this->render_nothing();
        } catch (Exception $e) {
            $this->render_text($e->getMessage());
        }
    }

    /**
     *
     **/
    public function authorize_action()
    {
        global $user, $auth;

        $auth_plugin = Config::get()->API_OAUTH_AUTH_PLUGIN;
        if ($GLOBALS['user']->id === 'nobody' && $auth_plugin !== 'Standard' && !Request::option('sso')) {
            $params = $_GET;
            $params['sso'] = strtolower($auth_plugin);
            $this->redirect($this->url_for('api/oauth/authorize?' . http_build_query($params)));
            return;
        } else {
            $auth->login_if($user->id === 'nobody');
        }

        $user_id = RESTAPI\Consumer\OAuth::getOAuthId($GLOBALS['user']->id);

        try {
            $consumer = RESTAPI\Consumer\Base::detectConsumer('oauth', 'request');
            if (Request::submitted('allow')) {
                $result = $consumer->grantAccess($GLOBALS['user']->id);

                $redirect_uri = Request::get('oauth_callback', $consumer->callback);

                if ($redirect_uri) {
                    $this->redirect($redirect_uri);
                } else {
                    // No oauth_callback, show the user the result of the authorization
                    // ** your code here **
                    PageLayout::postMessage(MessageBox::success(_('Sie haben der Applikation Zugriff auf Ihre Daten gewährt.')));
                    $this->redirect('api/authorizations#' . $consumer->auth_key);
                }
                return;
           }
        } catch (OAuthException2 $e) {
            // No token to be verified in the request, show a page where the user can enter the token to be verified
            // **your code here**
            die('invalid');
        }

        PageLayout::disableHeader();
        PageLayout::setTitle(sprintf(_('"%s" bittet um Zugriff'), $consumer->title));
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        $this->consumer = $consumer;
        $this->token    = Request::option('oauth_token');
        $this->oauth_callback = Request::get('oauth_callback');
    }

    /**
     *
     **/
    public function access_token_action()
    {
        $server = new OAuthServer();
        $server->accessToken();

        $this->render_nothing();
    }
}
