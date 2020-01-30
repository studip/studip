<?php

namespace Helper;

use JsonApi\Middlewares\Authentication;
use JsonApi\Middlewares\JsonApi as JsonApiMiddleware;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Jsonapi extends \Codeception\Module
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function withPHPLib($credentials, $function) {
        // EVIL HACK
        $oldPerm = $GLOBALS['perm'];
        $oldUser = $GLOBALS['user'];
        $GLOBALS['perm'] = new \Seminar_Perm();
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $result = $function($credentials);

        // EVIL HACK
        $GLOBALS['user'] = $oldUser;
        $GLOBALS['perm'] = $oldPerm;

        return $result;
    }

    public function createApp($credentials, $method, $pattern, $callable, $name = null)
    {
        return $this->createApp0(
            $credentials,
            function () use ($method, $pattern, $callable, $name) {
                $route = $this->map([$method], $pattern, $callable);
                if (isset($name)) {
                    $route->setName($name);
                }
            }
        );
    }

    public function createRequestBuilder($credentials = null)
    {
        $env = [];
        if ($credentials) {
            $env = [
                'PHP_AUTH_USER' => $credentials['username'],
                'PHP_AUTH_PW' => $credentials['password'],
            ];
        }

        $requestBuilder = new JsonApiRequestBuilder(
            Request::createFromEnvironment(
                Environment::mock($env)
            )
        );

        $requestBuilder
            ->setProtocolVersion('1.0')
            ->setHeader('Accept-Charset', 'utf-8');

        return $requestBuilder;
    }

    public function sendMockRequest($app, Request $request)
    {
        $container = $app->getContainer();
        $container['request'] = function ($container) use ($request) {
            return $request;
        };
        $response = $app($request, new Response());

        return new JsonApiResponse($response);
    }

    private function createApp0($credentials, $routerFn)
    {
        $app = $this->appFactory();

        $authenticator = function ($username, $password) use ($credentials) {
            // must return a \User
            if ($username === $credentials['username'] && $password === $credentials['password']) {
                return \User::find($credentials['id']);
            }

            return null;
        };

        $group = $app->group('', $routerFn);

        if ($credentials) {
            $group->add(function ($request, $response, $next) {
                $user = $request->getAttribute(Authentication::USER_KEY, null);

                $GLOBALS['auth'] = new \Seminar_Auth();
                $GLOBALS['auth']->auth = array(
                    'uid' => $user->user_id,
                    'uname' => $user->username,
                    'perm' => $user->perms,
                );

                $GLOBALS['user'] = new \Seminar_User($user->user_id);

                $GLOBALS['perm'] = new \Seminar_Perm();
                $GLOBALS['MAIL_VALIDATE_BOX'] = false;

                return $next($request, $response);
            })->add(new Authentication($authenticator));
        }

        $group->add(new JsonApiMiddleware($app));

        return $app;
    }

    private function appFactory()
    {
        $factory = new \JsonApi\AppFactory();

        return $factory->makeApp();
    }

    public function storeJsonMD(
        $filename,
        ResponseInterface $response,
        $limit = null,
        $ellipsis = null
    ) {
        $body = "{$response->getBody()}";
        $body = preg_replace(
            '!plugins.php\\\\/argonautsplugin!',
            'https:\\/\\/example.com',
            $body
        );
        $body = preg_replace('!\\\\/!', '/', $body);
        $body = preg_replace(['!%5B!', '!%5D!'], ['[', ']'], $body);

        $jsonBody = json_decode($body, true);

        if ($limit && isset($jsonBody['data']) && is_array($jsonBody['data'])) {
            $jsonBody['data'] = array_slice($jsonBody['data'], 0, $limit);
            if ($ellipsis) {
                $jsonBody['data'][] = $ellipsis;
            }
        }

        $jsonPretty = new \Camspiers\JsonPretty\JsonPretty();
        $json = $jsonPretty->prettify($jsonBody, JSON_UNESCAPED_SLASHES, '  ');

        $dirname = codecept_output_dir().'json-for-slate/';
        if (!file_exists($dirname)) {
            @mkdir($dirname);
        }

        if (file_exists($dirname)) {
            if (substr($filename, -3) !== '.md') {
                $filename .= '.md';
            }
            if ($filename[0] !== '_') {
                $filename = '_'.$filename;
            }
            file_put_contents($dirname.$filename, "```json\n".$json."\n```\n");
        }

        return $json;
    }
}
