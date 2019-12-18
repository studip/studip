<?php

namespace JsonApi\Middlewares\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OAuth1Strategy implements Strategy
{
    protected $user;

    protected $request;

    public function __construct(Request $request, $authenticator)
    {
        $this->request = $request;
        $this->authenticator = $authenticator;

        \OAuthStore::instance('PDO', ['conn' => \DBManager::get()]);
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $this->user = $this->detect();

        return $this->user;
    }

    public function addChallenge(Response $response)
    {
        return $response; //->withHeader('WWW-Authenticate', sprintf('Basic realm="%s"', 'Stud.IP JSON-API'));
    }

    private function detect()
    {
        if (!\OAuthRequestVerifier::requestIsSigned()) {
            return null;
        }

        $uri = (string) $this->request->getUri();
        $method = $this->request->getMethod();

        if ('GET' === strtoupper(($method))) {
            $parameters = (array) $this->request->getQueryParams();
        } elseif ('POST' === strtoupper(($method))) {
            $parameters = (array) $this->request->getParsedBody();
        } else {
            $parameters = [];
        }
        $parameters = $this->getParamsFromAuthorizationHeader($this->request, $parameters);

        $req = new \OAuthRequestVerifier($uri, $method, $parameters);

        // Check oauth timestamp and deny access if timestamp is outdated
        if ($req->getParam('oauth_timestamp') < strtotime('-6 hours')) {
            return null;
        }

        $result = $req->verifyExtended('access');

        $query = 'SELECT user_id FROM api_oauth_user_mapping WHERE oauth_id = ?';
        $statement = \DBManager::get()->prepare($query);
        $statement->execute([$result['user_id']]);

        if (!$userId = $statement->fetchColumn()) {
            return null;
        }

        return \User::find($userId);
    }

    private function getParamsFromAuthorizationHeader(Request $request, array $params)
    {
        if ($request->hasHeader('Authorization')) {
            $auth = $request->getHeaderLine('Authorization');
            if (0 == strncasecmp($auth, 'OAuth', 4)) {
                foreach (explode(',', substr($auth, 6)) as $v) {
                    if (!strpos($v, '=')) {
                        continue;
                    }
                    $v = trim($v);
                    list($name, $value) = explode('=', $v, 2);
                    if (!empty($value) && '"' == $value[0] && '"' == substr($value, -1)) {
                        $value = substr(substr($value, 1), 0, -1);
                    }

                    if (0 != strcasecmp($name, 'realm')) {
                        $params[$name] = $value;
                    }
                }
            }
        }

        return $params;
    }
}
